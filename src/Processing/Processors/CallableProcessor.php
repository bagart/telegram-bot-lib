<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgBotSetup;
use Throwable;

class CallableProcessor implements TgTypeDTOProcessorContract
{
    public function __construct(
        private readonly mixed $fn,
        private readonly ?ASKLogWrapper $logger = null,
    ) {
    }

    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
    ): self {
        return new self(
            fn: static fn () => null,
            logger: $botSetup->logger,
        );
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return true;
    }

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        \BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        try {
            ($this->fn)($dto, new TgServiceConfig(), $action);
        } catch (Throwable $e) {
            $this->logger?->error(
                '[CallableProcessor] execution failed',
                [
                    'dto' => $dto::class,
                    'action' => $action,
                    'exception' => [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ],
                ]
            );
        }
    }
}
