<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Queue;

interface JobHandlerContract
{
    public function handle(): void;
}
