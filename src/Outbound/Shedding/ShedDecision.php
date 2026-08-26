<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Shedding;

/**
 * Push-side shedding verdict (06 §44).
 *
 * Accept — enqueue normally. Defer — hold in the delayed set and re-promote
 * after delaySec (zero loss). Drop — route to DLQ with the given reason
 * (visible and replayable, never silent).
 */
final class ShedDecision
{
    private function __construct(
        public readonly string $action,
        public readonly int $delaySec = 0,
        public readonly string $reason = '',
    ) {
    }

    public static function Accept(): self
    {
        return new self('accept');
    }

    public static function Defer(int $delaySec): self
    {
        return new self('defer', max(1, $delaySec));
    }

    public static function Drop(string $reason): self
    {
        return new self('drop', reason: $reason);
    }

    public function isAccept(): bool
    {
        return $this->action === 'accept';
    }

    public function isDefer(): bool
    {
        return $this->action === 'defer';
    }

    public function isDrop(): bool
    {
        return $this->action === 'drop';
    }
}
