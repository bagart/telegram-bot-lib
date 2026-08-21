<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\TgDbLoggerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;

class MessageDTOToDbProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        BotProcessorContext $context,
    ): self {
        return new static(
            new TgEntityNamer(),
            $context->logger,
            $context->dbLogger,
        );
    }

    public function __construct(
        protected readonly TgEntityNamer $namer,
        private ASKLogWrapper $logger,
        private readonly ?TgDbLoggerContract $dbLogger = null,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO;
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
        if ($this->dbLogger) {
            $this->dbLogger->log($dto, [
                'tg_bot_id' => $botConfig->botId,
                'action' => $action,
            ]);
        } else {
            $this->logger->warning('[MessageDTOToDbProcessor] dbLogger not initialized');
        }
    }

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }
}
