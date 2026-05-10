<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

abstract class AbsDtoToChannelProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static(
            namer: new TgEntityNamer(),
        );
    }

    public function __construct(
        protected readonly TgEntityNamer $namer,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool {
        return true;
    }

    public function data(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
    ): array {
        if ($dto instanceof MessageTypeDTO) {
            return array_filter([
                'tg_bot_id' => $botId,
                'action' => $action,
                'message_id' => $dto->messageId,
                'chat_id' => $dto->chat->id,
                'chat' => $this->namer->name($dto->chat),
                'from_id' => $dto->from?->id,
                'from' => $dto->from ? $this->namer->name($dto->from) : null,
                'text' => $dto->text,
                'edit_date' => $dto->editDate,
                'reply_to_message_id' => $dto->replyToMessage?->messageId,
            ]);
        }

        return [
            'tg_bot_id' => $botId,
            'action' => $action,
            'dto' => $dto,
        ];
    }
}
