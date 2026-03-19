<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Processors;

/**
 * Critical contract rules:
 *
 * 1. All processors must work inside one shared scheduler
 * 2. No private schedulers inside processors
 * 3. Async chains must use the same scheduler instance
 * 4. Strict ordered processors must respect execution coordinator
 */
interface TgTypeDTOInitProcessorContract extends TgTypeDTOProcessorContract
{
}
