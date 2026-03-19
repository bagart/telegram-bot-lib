<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Pure;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedDataFormatException;
use BAGArt\TelegramBot\Processing\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\ProcessingDispatcherRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

class TgWebhookRequestParser
{
    public function __construct(
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly RegisteredUpdateProcessorSelector $selector,
        private readonly TgBotsSecretServiceContract $secretService,
        private readonly ASKLogWrapper $logger,
        private readonly ?ProcessingDispatcherRegistry $dispatcherRegistry = null,
        private readonly ?ASKSchedulerContract $scheduler = null,
    ) {
    }

    public function makeDTO(array $data): TgApiTypeDTOContract
    {
        $typeDTO = $this->tgApiDTOMapper->fromArray(
            UpdateTypeDTO::class,
            $data,
        );
        assert($typeDTO instanceof TgApiTypeDTOContract);

        return $typeDTO;
    }

    public function parse(
        array $data,
        ?string $secret,
        ?TgServiceConfig $config = null,
        ?TgBotConfig $botConfig = null,
    ): bool {
        try {
            $botId = $this->secretService->botId($secret);

            $config ??= new TgServiceConfig();
            $botConfig ??= new TgBotConfig(token: '', botId: $botId);

            $updateDTO = $this->makeDTO($data);
            $result = $this->process($updateDTO, $config, $botConfig);
            if (! $result) {
                $this->logger->info('TgWebhook: Response is true, but processing error', [
                    'botId' => $botId,
                    'update' => $data,
                ]);
            }

            return true;
        } catch (TgBotInvalidSecretException $e) {
            $this->logger->warning('TgWebhook: Secret is not allowed: '.$e->getMessage());
        } catch (TgUnexpectedDataFormatException $e) {
            $this->logger->critical('Webhook request parse error', [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'update' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logger->critical('Webhook request Unexpected error', [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'update' => $data,
            ]);
        }

        return false;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        ?TgServiceConfig $config = null,
        ?TgBotConfig $botConfig = null,
    ): bool {
        $config ??= new TgServiceConfig();
        $botConfig ??= new TgBotConfig(token: '', botId: '123');

        if ($this->dispatcherRegistry !== null) {
            return $this->processAsync($dto, $config, $botConfig);
        }

        return $this->processSync($dto, $botConfig);
    }

    private function processSync(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
    ): bool {
        assert($dto instanceof UpdateTypeDTO);

        $result = true;

        foreach ($this->selector->selectProcessors($dto, $botConfig) as $property => $processors) {
            $subDto = $dto->{$property};

            foreach ($processors as $processor) {
                try {
                    $processor->process($subDto, $botConfig, $property);
                } catch (\Throwable $e) {
                    $result = false;
                    $this->logger->error('Webhook request process error', [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    private function processAsync(
        TgApiTypeDTOContract $dto,
        TgServiceConfig $config,
        TgBotConfig $botConfig,
    ): bool {
        assert($dto instanceof UpdateTypeDTO);

        $dispatcher = $this->dispatcherRegistry->make(
            dispatcherType: $config->dispatcher,
            scheduler: $this->scheduler,
            logger: $this->logger,
        );

        $result = true;

        foreach ($this->selector->selectProcessors($dto, $botConfig) as $property => $processors) {
            $subDto = $dto->{$property};

            foreach ($processors as $processor) {
                if ($processor::class === UpdateDTOInitProcessor::class) {
                    $processor = new UpdateDTOInitProcessor(
                        serviceConfig: $config,
                        processorSelector: $this->selector,
                        dispatcher: $dispatcher,
                    );
                }

                try {
                    $processor->process($subDto, $botConfig, $property);
                } catch (\Throwable $e) {
                    $result = false;
                    $this->logger->error('Webhook request process error', [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }
}
