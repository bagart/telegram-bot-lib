<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Processors;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;

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
        BotProcessorContext $context,
    ): self;

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): void;

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool;

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool;
}
