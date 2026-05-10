<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgUpdateConfig;

class AnyDTOToStdProcessor extends AbsDtoToChannelProcessor
{
    public function __construct(
        protected readonly TgEntityNamer $namer,
        protected readonly mixed $output = STDOUT,
    ) {
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
        fwrite(
            $this->output,
            'Incoming '.$dto::class.': '
            .json_encode(
                $this->data($dto, $botId, $action),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )."\n"
        );
    }
}
