<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Processors;

/**
 * Processor that belongs to a module and is subject to module enablement
 * filtering in the update selector. Processors NOT implementing this
 * contract are global and never filtered.
 */
interface TgModuleProcessorContract extends TgTypeDTOProcessorContract
{
    /** Logical module id this processor belongs to. */
    public static function moduleId(): string;
}
