<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgUpdateProcessor;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TypeDTOProcessor\DtoProcessorConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

interface TgTypeDTOProcessorContract
{
    public static function build(
        DtoProcessorConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self;

    public function support(
        TgApiTypeDTOContract $dto,
        DtoProcessorConfig $config,
        ?string $action = null,
    ): bool;

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        DtoProcessorConfig $config,
        ?string $action = null,
    ): void;
}
