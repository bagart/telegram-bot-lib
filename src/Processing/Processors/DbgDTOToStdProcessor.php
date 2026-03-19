<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgBotSetup;

class DbgDTOToStdProcessor extends AbsDtoToChannelProcessor
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
        ?ASKSchedulerContract $scheduler = null,
        bool $onlyBasicInfo = true,
    ): static {
        return new static(
            namer: new TgEntityNamer(),
            onlyBasicInfo: $onlyBasicInfo,
        );
    }

    public function __construct(
        TgEntityNamer $namer,
        protected readonly mixed $output = STDOUT,
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
        fwrite(
            $this->output,
            $this->dump($dto, $botConfig, $action)."\n",
        );
    }
}
