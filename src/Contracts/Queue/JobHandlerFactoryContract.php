<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Queue;

interface JobHandlerFactoryContract
{
    /**
     * Build a job handler instance.
     *
     * @param  class-string<JobHandlerContract>  $class
     * @param  array<int, mixed>  $params  Positional constructor arguments
     */
    public function build(string $class, array $params = []): JobHandlerContract;
}
