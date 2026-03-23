<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ExampleServices;

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\SyncDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiClient;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Transport\GuzzleTransport;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiTransport;
use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\BotServices\BotSecretRegistry;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistryFactory;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\AnyDTOToLoggerProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageDTOEchoToUserProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageDTOShowToConsoleProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;

final class TgPureFactory
{
    private static ?TgBotLogWrapper $logger = null;
    private static ?TgBotCacheWrapper $cache = null;
    private static ?PDO $pdo = null;

    public static function logger(?TgUpdateConfig $config = null): TgBotLogWrapper
    {
        if (static::$logger === null) {
            TgBotLogWrapper::init(
                logger: new Logger(
                    name: 'TelegramBot',
                    handlers: [
                        new StreamHandler(
                            stream: 'php://stderr',
                            level: Level::Debug,
                        ),
                    ],
                ),
                debugEnabled: $config ? $config->dbg : false,
            );
            static::$logger = TgBotLogWrapper::build();
        }

        return static::$logger;
    }

    public static function cache(): TgBotCacheWrapper
    {
        if (static::$cache === null) {
            TgBotCacheWrapper::init(
                new TinyFileCache('storage/lib/cache')
            );
            static::$cache = TgBotCacheWrapper::build();
        }

        return static::$cache;
    }

    public static function dtoMapper(): TgApiDTOMapper
    {
        $logger = static::logger();

        return new TgApiDTOMapper(
            tgApiDTORegistry: new TgEntityToDTORegistryFactory($logger)
                ->build(TgApiEntityScopeEnum::class),
            logger: $logger,
        );
    }

    public static function transportAsync(): TgBotApiTransportContract
    {
        return new TgCurlMultiTransport(
            logger: static::logger(),
        );
    }

    public static function transport(): TgBotApiTransportContract
    {
        return new GuzzleTransport();
    }

    public static function dtoClient(
        ?TgUpdateConfig $config = null,
    ): TgBotApiDTOClientContract {
        $dtoMapper = static::dtoMapper();
        return new TgBotApiDTOClient(
            tgClient: TgBotApiClient::build(
                cache: static::cache(),
                transport: $config?->poller === 'async'
                    ? TgPureFactory::transportAsync()
                    : TgPureFactory::transport()
            ),
            tgApiDTOMapper: $dtoMapper,
            returnParser: new TgResponseParser(
                tgApiDTOMapper: $dtoMapper,
                logger: static::logger(),
            ),
        );
    }

    public static function pdo(string $dsn = 'sqlite:storage/lib/tg-bot.db'): PDO
    {
        $pdo = static::$pdo ??= new PDO($dsn, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        $pdo->exec(
            '
            CREATE TABLE IF NOT EXISTS tg_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tg_bot_id TEXT NOT NULL,
                message_id BIGINT NOT NULL,
                chat_id BIGINT NOT NULL,
                from_id BIGINT,
                from_username TEXT,
                text TEXT,
                edit_date TEXT,
                reply_to_message_id BIGINT,
                created_at TEXT NOT NULL
            )
        '
        );

        $pdo->exec(
            'CREATE INDEX IF NOT EXISTS idx_tg_messages_lookup ON tg_messages (tg_bot_id, chat_id, message_id, created_at)'
        );

        return $pdo;
    }

    public static function webhook(
        TypeDTOProcessorRegistry $processorRegistry,
    ): TgWebhookRequestParser {
        return new TgWebhookRequestParser(
            tgApiDTOMapper: static::dtoMapper(),
            processorRegistry: $processorRegistry,
            secretService: new AutoSecretByTokenService(),
            logger: static::logger(),
        );
    }

    public static function botSecretRegistry(): BotSecretRegistry
    {
        return new BotSecretRegistry(
            logger: static::logger(),
        );
    }

    public static function syncDispatcherType(): string
    {
        return SyncDtoPipelineDispatcher::TYPE;
    }

    /**
     * Build a TypeDTOProcessorRegistry from CLI flags.
     *
     * Supported flags:
     *   'echo'  => MessageDTOEchoToUserProcessor (requires dtoClient + token)
     *   'log'   => AnyDTOToLoggerProcessor
     *   'store' => MessageDTOPdoStoreProcessor
     *   'show'  => MessageDTOShowToConsoleProcessor
     *
     * @param  array<string, bool>  $processors  e.g. ['echo' => true, 'log' => false]
     * @param  TgUpdateConfig  $config  contains token, scheduler, and flags
     * @return TypeDTOProcessorRegistry
     */
    public static function processorRegistry(
        array $processors,
        TgUpdateConfig $config,
    ): TypeDTOProcessorRegistry {
        $registry = new TypeDTOProcessorRegistry();

        if (!empty($processors['echo'])) {
            $registry->register(
                MessageTypeDTO::class,
                MessageDTOEchoToUserProcessor::class,
            );
        }

        if (!empty($processors['log'])) {
            $registry->register(
                MessageTypeDTO::class,
                AnyDTOToLoggerProcessor::class,
            );
        }

        if (!empty($processors['store'])) {
            $registry->register(
                MessageTypeDTO::class,
                MessageDTOPdoStoreProcessor::class,
            );
        }

        if (!empty($processors['show'])) {
            $registry->register(
                MessageTypeDTO::class,
                MessageDTOShowToConsoleProcessor::class,
            );
        }

        return $registry;
    }
}
