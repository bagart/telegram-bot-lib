<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ExampleServices;

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\TgBotSetup;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\BotSecretRegistry;
use PDO;

final class TgPureFactory
{
    private static ?PDO $pdo = null;

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

    public static function botSecretRegistry(?TgBotSetup $setup = null): BotSecretRegistry
    {
        $setup ??= TgBotSetupFactory::build()->create(
            serviceConfig: new TgServiceConfig(),
        );

        return new BotSecretRegistry(
            logger: $setup->logger,
        );
    }
}
