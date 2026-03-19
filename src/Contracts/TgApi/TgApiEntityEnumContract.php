<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

use phpDocumentor\Reflection\PseudoTypes\EnumString;

/**
 * @property-read string|TgApiDTOContract $value
 * @mixin EnumString
 */
interface TgApiEntityEnumContract extends TgApiEnumContract
{
}
