<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
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

    // @todo int52 still string
    public function toArray(TgApiDTOContract $dto): array;

    /**
     * Splits a method DTO into JSON parameters and multipart upload files.
     *
     * Fields whose tgPropertyMetas declare the `input-file` tgType AND whose
     * string value uses the `file://` scheme are moved into `files`
     * (tgPropName → absolute local path); everything else — including
     * file_ids and http(s) URLs on the same fields — stays verbatim in
     * `parameters`. The split happens only at send time; serialization for
     * the outbound queue must use toArray(), which keeps `file://` values so
     * a queued task round-trips through Redis without losing them.
     *
     * Scope: top-level method fields only; `file://` values inside nested
     * DTOs are not extracted.
     *
     * @return array{parameters: array<string, mixed>, files: array<string, string>}
     */
    public function splitRequest(TgApiMethodDTOContract $dto): array;
}
