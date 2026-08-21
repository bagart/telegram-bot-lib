<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Processing\TgDbLoggerContract;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\TgApiCaller;
use BAGArt\TelegramBot\TgBotSetup;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRuleRegistry;

/**
 * Processor-facing context passed to TgTypeDTOProcessorContract::build().
 *
 * Carries the services processors need, plus the originating TgBotSetup
 * for processors that require the full setup.
 */
final readonly class BotProcessorContext
{
    public function __construct(
        public ASKLogWrapper $logger,
        public TgSenderContract $tgSender,
        public TgApiCaller $tgApiCaller,
        public TypeDTOProcessorRegistry $processorRegistry,
        public TgServiceConfig $serviceConfig,
        public TgBotSetup $botSetup,
        public ?TgDbLoggerContract $dbLogger = null,
        public ?MessageValidationRuleRegistry $messageRules = null,
        public ?TgCommandRegistry $commandRegistry = null,
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
            botSetup: $botSetup,
            dbLogger: $botSetup->dbLogger,
            messageRules: $botSetup->messageRules,
            commandRegistry: $botSetup->commandRegistry,
        );
    }
}
