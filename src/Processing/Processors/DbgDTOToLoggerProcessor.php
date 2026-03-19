<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgBotSetup;

class DbgDTOToLoggerProcessor extends AbsDtoToChannelProcessor
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
        ?ASKSchedulerContract $scheduler = null,
        bool $onlyBasicInfo = true,
    ): static {
        return new static(
            namer: new TgEntityNamer(),
            logger: $botSetup->logger,
            onlyBasicInfo: $onlyBasicInfo,
        );
    }

    public function __construct(
        TgEntityNamer $namer,
        private readonly ASKLogWrapper $logger,
        private readonly string $logLevel = ASKLogWrapper::LEVEL_DEFAULT,
        bool $onlyBasicInfo = true,
    ) {
        parent::__construct($namer, $onlyBasicInfo);
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        $this->logger->log(
            $this->logLevel,
            $this->dump($dto, $botConfig, $action),
        );
    }
}
