<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

interface TgApiReturnDTOContract extends TgApiDTOContract
{
    /** @return TgApiTypeDTOContract|TgApiTypeDTOContract[]|bool|int|string */
    public function payload(): mixed;

    /** @rerturn string[]|string[][] */
    public static function getReturnTypes(): array;
}
