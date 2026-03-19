<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\TgBotSetup;

class ApiCallProcessor implements TgTypeDTOProcessorContract
{
    public function __construct(
        private readonly TgSenderContract $sender,
        private readonly TgApiMethodDTOContract $methodDto,
        private readonly ASKLogWrapper $logger,
    ) {
    }

    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
    ): self {
        throw new \RuntimeException('ApiCallProcessor cannot be built from registry');
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
        return true;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        $this->logger->info('ApiCallProcessor: executing API call', [
            'method' => $this->methodDto::tgApiEntity()->name,
            'botId' => $botConfig->botId,
            'action' => $action,
        ]);

        $this->sender->send($botConfig, $this->methodDto);
    }

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }
}
