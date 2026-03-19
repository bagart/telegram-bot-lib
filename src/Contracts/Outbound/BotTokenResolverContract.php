<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

/**
 * Bot token resolver by botId. Framework-independent.
 *
 * Executor does not know about Eloquent / Laravel. TgDbTokenResolver implementation
 * (management-lib) resolves the token via TgBot / TgBotDbRegistry model.
 *
 * Bot tokens are stored in DB (tg_bots), NOT in .env — see project conventions.
 *
 * @see todo.md §1.5.
 */
interface BotTokenResolverContract
{
    /**
     * @param  string  $botId  Bot identifier (TgBotConfig::$botId).
     * @return string Bot token for Telegram API.
     *
     * @throws \Throwable If bot not found or token unavailable.
     */
    public function resolve(string $botId): string;
}
