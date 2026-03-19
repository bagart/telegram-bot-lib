<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\CodecEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a video file of a specific quality.')]
#[See('https://core.telegram.org/bots/api#videoquality')]
class VideoQualityTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier for this file, which can be used to download or reuse the file')]
        public string $fileId,
        #[Description('Unique identifier for this file, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $fileUniqueId,
        #[Description('Video width')]
        public int $width,
        #[Description('Video height')]
        public int $height,
        #[Description('Codec that was used to encode the video, for example, “h264”, “h265”, or “av01”')]
        public CodecEnum $codec,
        #[Description('File size in bytes.')]
        public ?string $fileSize = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::VideoQuality;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"file_id":{"property":"fileId","tgPropName":"file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"file_unique_id":{"property":"fileUniqueId","tgPropName":"file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"width":{"property":"width","tgPropName":"width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"height":{"property":"height","tgPropName":"height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"codec":{"property":"codec","tgPropName":"codec","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\CodecEnum"],"tgTypes":[{"type":"str","literal":"h264"},{"type":"str","literal":"h265"},{"type":"str","literal":"av01"}],"nullable":false,"required":true},"file_size":{"property":"fileSize","tgPropName":"file_size","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false}}
XJSON,
            true,
            20,
            JSON_THROW_ON_ERROR
        );

        $result = [];
        foreach ($metaByProp as $tgPropName => $propertyMeta) {
            $result[$tgPropName] = new TgApiProperty(...$propertyMeta);
        }

        return $result;
    }
}
