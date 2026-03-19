<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedDataFormatException;

interface TgApiDTOMapperContract
{
    /**
     * @throws TgUnexpectedDataFormatException
     */
    public function fromArray(
        string|TgApiDTOContract|TgApiEntityEnumContract $entity,
        array $data,
    ): TgApiDTOContract;

    //@todo int52 still string
    public function toArray(TgApiDTOContract $dto): array;
}
