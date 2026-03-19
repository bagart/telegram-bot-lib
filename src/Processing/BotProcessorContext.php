<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Processing\TgDbLoggerContract;
use BAGArt\TelegramBot\TgApiCaller;
use BAGArt\TelegramBot\TgBotSetup;

/**
 * Processor-facing context passed to TgTypeDTOProcessorContract::build().
 *
 * Carries only the services processors actually need — not the full TgBotSetup.
 */
final readonly class BotProcessorContext
{
    public function __construct(
        public ASKLogWrapper $logger,
        public TgSenderContract $tgSender,
        public TgApiCaller $tgApiCaller,
        public TypeDTOProcessorRegistry $processorRegistry,
        public TgServiceConfig $serviceConfig,
        public ?TgDbLoggerContract $dbLogger = null,
    ) {
    }

    public static function fromBotSetup(TgBotSetup $botSetup): self
    {
        return new self(
            logger: $botSetup->logger,
            tgSender: $botSetup->tgSender,
            tgApiCaller: $botSetup->tgApiCaller,
            processorRegistry: $botSetup->processorRegistry,
            serviceConfig: $botSetup->serviceConfig,
            dbLogger: $botSetup->dbLogger,
        );
    }
}
