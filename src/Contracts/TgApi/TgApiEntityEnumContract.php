<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

use phpDocumentor\Reflection\PseudoTypes\EnumString;

/**
 * @property-read string|TgApiDTOContract $value
 * @property-read string $name
 * @mixin EnumString
 */
interface TgApiEntityEnumContract extends TgApiEnumContract
{
}
