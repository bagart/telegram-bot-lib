<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

interface TgApiMethodDTOContract extends TgApiDTOContract
{
    /**
     * @return string[]|TgApiTypeDTOContract[]|TgApiTypeDTOContract[][]
     */
    public static function getReturnTypes(): array;
}
