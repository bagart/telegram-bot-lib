<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;

class RedisQueueDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'redis-queue';

    public function __construct(
        private readonly TgBotRedisQueueWrapper $wrapper,
    ) {
    }

    /**
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int {
        $this->wrapper->connect();

        $i = 0;
        foreach ($processors as $processor) {
            $job = new RedisQueueDTOProcessJob(
                config: $config,
                processor: is_string($processor) ? $processor : $processor::class,
                botId: $botId,
                dto: $dto,
                action: $action,
            );

            $payload = serialize($job);
            $this->wrapper->publishRaw($payload);
            ++$i;
        }

        return $i;
    }
}
