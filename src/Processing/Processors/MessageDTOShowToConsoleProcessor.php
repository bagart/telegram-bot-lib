<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotSetup;

class MessageDTOShowToConsoleProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
    ): self {
        return new static();
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO;
    }

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        \BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        assert($dto instanceof MessageTypeDTO);

        echo "=== SHOW ===\n";
        echo "bot_id: {$botConfig->botId}\n";
        echo "message_id: {$dto->messageId}\n";
        echo "chat_id: {$dto->chat->id}\n";
        echo "text: {$dto->text}\n";
        echo "from: ".($dto->from?->id ?? 'null')."\n";
        echo "from_username: ".($dto->from?->username ?? 'null')."\n";
        echo "date: ".($dto->date ?? 'null')."\n";
        echo "==============\n";
    }
}
