<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

class MessageValidationVerdict
{
    public function __construct(
        public readonly MessageVerdictActionEnum $action,
        public readonly string $reason,
        public readonly string $matchedRule,
        public readonly int $priority,
        public readonly ?int $restrictDuration = null,
        public readonly bool $deleteMessage = true,
        public readonly bool $notifyChat = true,
        public readonly ?string $warningMessage = null,
    ) {
    }

    public static function reject(
        MessageVerdictActionEnum $action,
        string $reason,
        string $matchedRule,
        int $priority = 100,
        ?int $restrictDuration = null,
        bool $deleteMessage = true,
        bool $notifyChat = true,
        ?string $warningMessage = null,
    ): static {
        return new static(
            action: $action,
            reason: $reason,
            matchedRule: $matchedRule,
            priority: $priority,
            restrictDuration: $restrictDuration,
            deleteMessage: $deleteMessage,
            notifyChat: $notifyChat,
            warningMessage: $warningMessage,
        );
    }
}
