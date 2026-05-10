<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class MessageDTOShowToConsoleProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static();
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
        assert($dto instanceof MessageTypeDTO);

        echo "=== SHOW ===\n";
        echo "bot_id: {$botId}\n";
        echo "message_id: {$dto->messageId}\n";
        echo "chat_id: {$dto->chat->id}\n";
        echo "text: {$dto->text}\n";
        echo "from: ".($dto->from?->id ?? 'null')."\n";
        echo "from_username: ".($dto->from?->username ?? 'null')."\n";
        echo "date: ".($dto->date ?? 'null')."\n";
        echo "==============\n";
    }
}
