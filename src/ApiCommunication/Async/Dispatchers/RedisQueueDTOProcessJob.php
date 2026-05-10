<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;

final class RedisQueueDTOProcessJob
{
    /**
     * @param  class-string<TgTypeDTOProcessorContract>  $processor
     */
    public function __construct(
        public readonly TgUpdateConfig $config,
        public readonly string $processor,
        public readonly string $botId,
        public readonly TgApiTypeDTOContract $dto,
        public readonly ?string $action = null,
    ) {
    }

    public function handle(): void
    {
        $this->processor::build($this->config)
            ->process(
                $this->dto,
                $this->botId,
                $this->config,
                $this->action,
            );
    }
}
