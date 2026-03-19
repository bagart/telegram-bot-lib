<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents one size of a photo or a [file](https://core.telegram.org/bots/api#document) / [sticker](https://core.telegram.org/bots/api#sticker) thumbnail.')]
#[See('https://core.telegram.org/bots/api#photosize')]
class PhotoSizeTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier for this file, which can be used to download or reuse the file')]
        public string $fileId,
        #[Description('Unique identifier for this file, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $fileUniqueId,
        #[Description('Photo width')]
        public int $width,
        #[Description('Photo height')]
        public int $height,
        #[Description('File size in bytes')]
        public ?int $fileSize = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PhotoSize;
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
{"file_id":{"property":"fileId","tgPropName":"file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"file_unique_id":{"property":"fileUniqueId","tgPropName":"file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"width":{"property":"width","tgPropName":"width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"height":{"property":"height","tgPropName":"height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"file_size":{"property":"fileSize","tgPropName":"file_size","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
