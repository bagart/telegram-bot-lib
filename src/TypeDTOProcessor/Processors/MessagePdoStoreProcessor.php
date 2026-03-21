<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

class MessagePdoStoreProcessor implements TgUpdateProcessorContract
{
    public function __construct(
        private readonly \PDO $pdo,
    ) {
    }

    public function support(TgApiTypeDTOContract $dto): bool
    {
        return $dto instanceof MessageTypeDTO;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): void
    {
        assert($dto instanceof MessageTypeDTO);

        $stmt = $this->pdo->prepare(
            'INSERT INTO tg_messages (tg_bot_id, message_id, chat_id, from_id, from_username, text, edit_date, reply_to_message_id, created_at)
             VALUES (:tg_bot_id, :message_id, :chat_id, :from_id, :from_username, :text, :edit_date, :reply_to_message_id, :created_at)'
        );

        $stmt->execute([
            'tg_bot_id' => $botId,
            'message_id' => $dto->messageId,
            'chat_id' => $dto->chat->id,
            'from_id' => $dto->from?->id,
            'from_username' => $dto->from?->username,
            'text' => $dto->text,
            'edit_date' => $dto->editDate ? gmdate('Y-m-d\TH:i:s\Z', $dto->editDate) : null,
            'reply_to_message_id' => $dto->replyToMessage?->messageId,
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
