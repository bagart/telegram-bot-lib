<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;

class DbgDTOToStdProcessor extends AbsDtoToChannelProcessor
{
    public static function build(
        BotProcessorContext $context,
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
