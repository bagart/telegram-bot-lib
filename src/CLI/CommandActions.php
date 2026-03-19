<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI;

use BAGArt\ASKClient\Exceptions\ASKNetworkException;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiCommunicationException;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher\LaravelProcessingDispatcher;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;

final class CommandActions
{
    /**
     * Validate parsed CLI options — reject unknown --options.
     *
     * @param  array<string, string|bool>  $options  result of getopt('', $definedOptions)
     * @param  string[]  $definedOptions  getopt definitions, e.g. ['token::', 'echo', 'help']
     * @return array<string, string|bool> validated options
     *
     * Exits with code 1 if any unknown --option is found in $_SERVER['argv'].
     */
    public static function parseOptions(array $options, array $definedOptions): array
    {
        $knownNames = array_map(
            fn (string $opt) => preg_replace('/[:]+$/', '', $opt),
            $definedOptions,
        );

        foreach ($_SERVER['argv'] as $arg) {
            if (!str_starts_with($arg, '--')) {
                continue;
            }
            $name = explode('=', substr($arg, 2), 2)[0];
            $nameWithoutValue = rtrim($name, ':');
            if (!in_array($nameWithoutValue, $knownNames, true)) {
                echo "Error: Unknown option --{$nameWithoutValue}\n";

                exit(1);
            }
        }

        return $options;
    }

    /**
     * Resolve Telegram bot token from --token option or TELEGRAM_BOT_TOKEN env.
     *
     * @param  array<string, string|bool>  $options  parsed options from parseOptions()
     * @return string                              Telegram Bot API token
     *
     * Exits with code 1 if no token is found.
     */
    public static function resolveToken(array $options): string
    {
        $token = $options['token'] ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
        if (!$token) {
            TgBotSetupFactory::createLogger()->error(
                'Token not set. Use --token=xxx:xxx or "export TELEGRAM_BOT_TOKEN=xxx:xxx"'
            );
            exit(1);
        }

        if (!preg_match('/^\d{5,20}:[A-Za-z0-9_-]+$/', $token)) {
            TgBotSetupFactory::createLogger()->error("Invalid token format. Expected: {numeric_id}:{secret}");
            exit(1);
        }
        assert(is_numeric(explode(':', $token)[0]));

        return $token;
    }

    /**
     * Verify bot token by calling getMe and printing bot info.
     *
     * @param  TgBotApiDTOClientContract  $dtoClient  Telegram API DTO client
     * @param  string  $token  Telegram Bot API token
     * @return UserTypeDTO                           verified bot user
     *
     * Exits with code 1 on failure.
     */
    public static function verifyBot(
        TgBotApiDTOClientContract $dtoClient,
        string $token,
        ?ASKLogWrapper $logger = null,
        int $maxRetries = 10,
    ): UserTypeDTO {
        $logger ??= TgBotSetupFactory::createLogger();

        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries + 1; $attempt++) {
            try {
                $response = $dtoClient->request(
                    botConfig: new TgBotConfig(token: $token),
                    dto: new TgApi\Methods\DTO\GetMeMethodDTO(),
                    timeout: 3,
                );
                $user = $response->result;
                assert($user instanceof UserTypeDTO);

                $logger->info(
                    "Bot verified: @{$user->username} "
                    .'('.trim($user->firstName.' '.$user->lastName).');'
                );

                return $user;
            } catch (TgApiCommunicationException|ASKNetworkException $e) {
                $lastException = $e;

                if ($attempt <= $maxRetries) {
                    $logger->error(
                        "Network error connecting to Telegram (attempt {$attempt}/{$maxRetries}): "
                        .$e->getMessage()
                    );
                }
            }
        }

        $logger->error(
            'Failed to connect to Telegram after '.($maxRetries + 1).' attempts: '
            .$lastException::class."{$lastException->getMessage()};\n{$lastException->getTraceAsString()}\n"
        );

        exit(1);
    }

    /**
     * Apply CLI options to poller/service configuration.
     */
    public static function makePollerConfig(
        array $options,
        ?TgServiceConfig $serviceConfig = null,
    ): TgServiceConfig {
        $serviceConfig ??= new TgServiceConfig();

        if (isset($options['dispatcher'])) {
            $serviceConfig->dispatcher = $options['dispatcher'];
        } elseif (isset($options['queue'])) {
            $serviceConfig->dispatcher = LaravelProcessingDispatcher::TYPE;
        }

        $serviceConfig->logLevel = $options['log-level'] ?? ASKLogWrapper::LEVEL_INFO;

        if (isset($options['transport'])) {
            $serviceConfig->transport = $options['transport'];
        }

        return $serviceConfig;
    }

    /**
     * Apply CLI options to outbound/service configuration.
     */
    public static function makeOutboundConfig(
        array $options,
        ?TgServiceConfig $serviceConfig = null,
    ): TgServiceConfig {
        $serviceConfig ??= new TgServiceConfig();

        $mode = $options['mode'] ?? 'single';

        if ($mode === 'multi') {
            $serviceConfig->outboundQueueStore = 'redis';
            $serviceConfig->redisDsn = TgBotSetupFactory::buildRedisDsn(
                host: (string)($options['redis-host'] ?? '127.0.0.1'),
                port: (int)($options['redis-port'] ?? 6379),
                timeout: (float)($options['redis-timeout'] ?? 2.0),
            );
        }

        $serviceConfig->logLevel = $options['log-level'] ?? ASKLogWrapper::LEVEL_INFO;

        return $serviceConfig;
    }

    /**
     * Print service/poller configuration info to stdout.
     */
    public static function configInfo(
        TgServiceConfig $tgConfig,
        ?ProcessorConfig $initProcConfig = null,
    ): void {
        echo "=== Poller Mode: Async => {$tgConfig->dispatcher} ===\n";
        echo "=== Transport: {$tgConfig->transport} ===\n";
        echo "=== Cache Driver: {$tgConfig->cacheDriver} ===\n";

        if ($initProcConfig) {
            $flags = implode(' ', array_filter([
                $initProcConfig->antispam ? '[ANTISPAM]' : null,
                $initProcConfig->echo ? '[ECHO]' : null,
                $initProcConfig->show ? '[SHOW]' : null,
                $initProcConfig->store ? '[STORE]' : null,
                $initProcConfig->log ? '[LOG]' : null,
                $tgConfig->logLevel === ASKLogWrapper::LEVEL_DEBUG ? '[DBG]' : null,
            ]));

            echo "PROCESSORS: {$flags}\n";
        }
    }

    /**
     * Initialize PHP runtime for CLI commands: memory_limit, pcntl signals.
     *
     * @param  array<string, string|bool>  $options  parsed options from parseOptions()
     */
    public static function initRuntime(array $options): void
    {
        ini_set('memory_limit', (string)($options['memory-limit'] ?? '512M'));

        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }
    }
}
