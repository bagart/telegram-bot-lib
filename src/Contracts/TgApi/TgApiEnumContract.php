<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

use phpDocumentor\Reflection\PseudoTypes\EnumString;

/**
 * @property-read string $name ENUM Name
 * @property-read string|int $value ENUM Name
 * @method static static cases() ENUM cases
 * @method static static|null tryFrom(string|int $propValue) ENUM cases
 * @mixin EnumString
 */
interface TgApiEnumContract
{
}
