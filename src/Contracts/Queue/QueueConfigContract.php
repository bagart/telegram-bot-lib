<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Queue;

interface QueueConfigContract
{
    public function host(): string;

    public function port(): int;

    public function timeout(): float;

    public function prefix(): string;

    public function outboundQueue(): string;

    public function processorQueue(): string;

    public function blockTimeout(): int;
}
