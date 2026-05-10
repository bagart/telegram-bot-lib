<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Chat;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

class ChatSession implements ChatSessionInterface
{
    private const MAX_MESSAGES = 20;

    private array $messages = [];
    private int $messageCount = 0;
    private string $chatId;
    private ?string $userId = null;
    private ?string $username = null;

    public function __construct(string $chatId, ?string $userId = null, ?string $username = null)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->username = $username;
    }

    public function addMessage(MessageTypeDTO $message): void
    {
        $this->messageCount++;

        $this->messages[] = [
            'id' => $this->messageCount,
            'from' => $message->from->username ?? $message->from->firstName ?? 'Unknown',
            'text' => $message->text ?? '[media]',
            'time' => date('H:i:s'),
            'chatType' => $message->chat->type ?? 'private',
            'userId' => $message->from->id ?? null,
        ];

        if (count($this->messages) > self::MAX_MESSAGES) {
            array_shift($this->messages);
        }
    }

    public function addOutgoingMessage(string $text): void
    {
        $this->messageCount++;

        $this->messages[] = [
            'id' => $this->messageCount,
            'from' => 'You',
            'text' => $text,
            'time' => date('H:i:s'),
            'chatType' => 'private',
            'userId' => null,
        ];

        if (count($this->messages) > self::MAX_MESSAGES) {
            array_shift($this->messages);
        }
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function matchesFilter(UpdateTypeDTO $update): bool
    {
        if (!$update->message) {
            return false;
        }

        $message = $update->message;

        if ((string)$message->chat->id !== (string)$this->chatId) {
            return false;
        }

        if ($this->userId !== null && (string)$message->from->id !== (string)$this->userId) {
            return false;
        }

        if ($this->username !== null && $message->from->username !== $this->username) {
            return false;
        }

        return true;
    }

    public function setChatId(string $chatId): void
    {
        $this->chatId = $chatId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getChatId(): string
    {
        return $this->chatId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
