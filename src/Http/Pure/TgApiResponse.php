<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Pure;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

class TgApiResponse
{
    /**
     * @param  TgApiTypeDTOContract|TgApiTypeDTOContract[]|bool|string|int|null  $result
     */
    public function __construct(
        public readonly bool $ok,
        public readonly array $possibleResultTypes,
        public readonly mixed $result,
        public readonly ?int $errorCode = null,
        public readonly ?int $retryAfter = null,
    ) {
    }
}
