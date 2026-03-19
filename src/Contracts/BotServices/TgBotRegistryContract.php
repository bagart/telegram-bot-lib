<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\BotServices;

use BAGArt\TelegramBot\TgIntegration\BotSecretDTO;

interface TgBotRegistryContract
{
    public function register(BotSecretDTO $bot): self;

    public function getBot(string $botId): ?BotSecretDTO;

    /**
     * @return \Generator|BotSecretDTO[]
     */
    public function getBotsBySecret(?string $secret): \Generator;

    /**
     * @return \Generator|string[]
     */
    public function getBotIdsBySecret(?string $secret): \Generator;

    public function getBotCount(): int;

    public function has(string $botId): bool;
}
