<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\BotServices;

interface TgBotsSecretServiceContract
{
    /**
     * @throws \BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException
     */
    public function secret(string $token): string;

    /**
     * @throws \BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException
     */
    public function botId(?string $secret): string;
}
