<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgUpdateProcessor;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

/**
 * Critical contract rules:
 *
 * 1. All processors must work inside one shared scheduler
 * 2. No private schedulers inside processors
 * 3. Async chains must use the same scheduler instance
 * 4. Strict ordered processors must respect execution coordinator
 */
interface TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self;

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool;

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void;
}
