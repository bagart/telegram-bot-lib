<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class UpdateLoggerProcessor implements TgUpdateProcessorContract
{
    public function __construct(
        private readonly TgBotLogWrapper $logger,
        private readonly TgEntityNamer $namer,
    ) {
    }

    public function support(TgApiTypeDTOContract $dto): bool
    {
        return true;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): void
    {
        if ($dto instanceof MessageTypeDTO) {
            $this->logger->info('Incoming message', array_filter([
                'tg_bot_id' => $botId,
                'message_id' => $dto->messageId,
                'chat_id' => $dto->chat->id,
                'chat' => $this->namer->name($dto->chat),
                'from_id' => $dto->from?->id,
                'from' => $this->namer->name($dto->from),
                'text' => $dto->text,
                'edit_date' => $dto->editDate,
                'reply_to_message_id' => $dto->replyToMessage?->messageId,
            ]));
        } else {
            $this->logger->info('Incoming update:'.$dto::class, [
                'dto' => $dto,
            ]);
        }
    }
}
