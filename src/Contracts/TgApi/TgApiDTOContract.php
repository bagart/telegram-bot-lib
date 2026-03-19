<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

/**
 * Base DTO contract for Telegram API objects
 *
 * @see https://core.telegram.org/bots/api#available-types
 * @see https://core.telegram.org/bots/api#available-methods
 */
interface TgApiDTOContract
{
    public static function tgApiEntity(): TgApiEntityEnumContract;

    public static function tgEntityScope(): TgApiEntityScopeEnumContract;

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array;
}
