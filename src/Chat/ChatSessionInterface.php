<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Chat;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

interface ChatSessionInterface
{
    public function addMessage(MessageTypeDTO $message): void;

    public function addOutgoingMessage(string $text): void;

    public function getMessages(): array;

    public function matchesFilter(UpdateTypeDTO $update): bool;

    public function setChatId(string $chatId): void;

    public function setUserId(?string $userId): void;

    public function setUsername(?string $username): void;
}
