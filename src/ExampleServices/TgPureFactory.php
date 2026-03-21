<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ExampleServices;

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiClient;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\BotServices\BotSecretRegistry;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistryFactory;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class TgPureFactory
{
    private static TgBotLogWrapper $logger;
    private static TgBotCacheWrapper $cache;
    private static ?\PDO $pdo = null;

    public static function logger(): TgBotLogWrapper
    {
        return static::$logger ??= new TgBotLogWrapper(
            logger: new Logger(
                name: 'TelegramBot',
                handlers: [
                    new StreamHandler(
                        stream: 'php://stderr',
                        level: Level::Debug,
                    ),
                ],
            ),
        );
    }

    public static function cache(): TgBotCacheWrapper
    {
        return static::$cache ??= new TgBotCacheWrapper(
            cache: new TinyFileCache($cacheDir ?? 'storage/lib/cache')
        );
    }

    public static function dtoMapper(): TgApiDTOMapper
    {
        $logger = self::logger();

        return new TgApiDTOMapper(
            logger: $logger,
            tgApiDTORegistry: (new TgEntityToDTORegistryFactory($logger))
                ->default(TgApiEntityScopeEnum::class),
        );
    }

    public static function dtoClient(): TgBotApiDTOClientContract
    {
        $dtoMapper = self::dtoMapper();

        return new TgBotApiDTOClient(
            tgClient: self::rawClient(),
            tgApiDTOMapper: $dtoMapper,
            returnParser: new TgResponseParser(
                tgApiDTOMapper: $dtoMapper,
                logger: self::logger(),
            ),
        );
    }

    public static function rawClient(): TgBotApiClientContract
    {
        return new TgBotApiClient(
            rateLimiter: new TgRateLimiter(self::cache()),
            circuitBreaker: new TgCircuitBreaker(self::cache()),
            retryPolicy: new TgRetryPolicy(),
        );
    }


    public static function pdo(string $dsn = 'sqlite:storage/lib/tg-bot.db'): \PDO
    {
        $pdo = static::$pdo ??= new \PDO($dsn, options: [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        $pdo->exec('
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
        ');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_tg_messages_lookup ON tg_messages (tg_bot_id, chat_id, message_id, created_at)');

        return $pdo;
    }

    public static function webhook(
        TypeDTOProcessorRegistry $processorRegistry,
    ): TgWebhookRequestParser {
        return new TgWebhookRequestParser(
            tgApiDTOMapper: self::dtoMapper(),
            processorRegistry: $processorRegistry,
            secretService: new AutoSecretByTokenService(),
            logger: self::logger(),
        );
    }

    public static function botSecretRegistry(): BotSecretRegistry
    {
        return new BotSecretRegistry(
            logger: self::logger(),
        );
    }
}
