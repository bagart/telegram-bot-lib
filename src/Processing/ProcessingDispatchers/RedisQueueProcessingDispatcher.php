<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\ASKClient\Queue\JsonCodec;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

class RedisQueueProcessingDispatcher implements ProcessingDispatcherContract
{
    public const string TYPE = 'redis-queue';

    public function __construct(
        private readonly ASKQueueAdapterContract $queue,
        private readonly string $queueName = 'tg-inbox',
        private readonly JsonCodec $codec = new JsonCodec(),
    ) {
    }

    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): int {
        $i = 0;
        foreach ($processors as $processor) {
            $context = new UpdateContext(
                dto: $dto,
                processor: is_string($processor) ? $processor : $processor::class,
                botConfig: $botConfig,
                executionKey: null,
                source: $action,
            );

            $job = new RedisQueueDTOProcessJob(
                serviceConfig: $serviceConfig,
                context: $context,
                updateDto: $updateDto,
            );

            $this->queue->push(
                $this->queueName,
                $this->codec->encode($job),
            );
            ++$i;
        }

        return $i;
    }
}
