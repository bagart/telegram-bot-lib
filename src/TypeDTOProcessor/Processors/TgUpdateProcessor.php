<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgUpdateProcessor implements TgUpdateProcessorContract
{
    private ?TypeDTOProcessorRegistry $registry;

    public function __construct(
        TypeDTOProcessorRegistry $registry,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
        $this->registry = $registry;
    }

    public function __destruct()
    {
        $this->registry = null;
    }

    public function support(TgApiTypeDTOContract $dto): bool
    {
        return $dto instanceof UpdateTypeDTO;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): void
    {
        if ($dto instanceof TgApiTypeDTOContract) {
            $this->processUpdate($dto, $botId);
        }
    }

    private function processUpdate(TgApiTypeDTOContract $update, string $botId): void
    {
        foreach ($update::tgPropertyMetas() as $meta) {
            $property = $meta->property;
            $value = $update->$property;

            if ($value instanceof TgApiTypeDTOContract) {
                $this->processDto($value, $botId);
            }
        }
    }

    private function processDto(TgApiTypeDTOContract $dto, string $botId): void
    {
        if ($this->registry === null) {
            return;
        }

        foreach ($this->registry->get($dto::class) as $processor) {
            if ($processor->support($dto)) {
                try {
                    $processor->process($dto, $botId);
                } catch (\Throwable $e) {
                    $this->logger?->error('[!!!] TgUpdateProcessor processDto error', [
                        'dto' => $dto::class,
                        'processor' => $processor::class,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
