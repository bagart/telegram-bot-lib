<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

class TgApiResponse
{
    /**
     * @param TgApiTypeDTOContract|TgApiTypeDTOContract[]|bool|string|int|null $result
     */
    public function __construct(
        public readonly bool $ok,
        public readonly array $possibleResultTypes,
        public readonly mixed $result,
    ) {
    }
}
