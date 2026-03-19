<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use Throwable;

class SyncProcessingDispatcher implements ProcessingDispatcherContract
{
    public const string TYPE = 'sync';

    public function __construct(
        private readonly ?ASKLogWrapper $logger = null,
        private readonly ?BotProcessorContext $processorContext = null,
    ) {
    }

    /**
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): int {
        $i = 0;
        /** @var TgTypeDTOProcessorContract $processor */
        foreach ($processors as $processor) {
            ++$i;
            $instance = is_string($processor)
                ? $processor::build($this->processorContext)
                : $processor;

            try {
                $instance->process($dto, $botConfig, $action, $updateDto);
            } catch (Throwable $e) {
                $this->logger?->warning(
                    sprintf(
                        '[SyncProcessingDispatcher]: processor %s failed: %s',
                        $processor::class,
                        $e->getMessage(),
                    ),
                    ['exception' => $e::class],
                );
            }
        }

        return $i;
    }
}
