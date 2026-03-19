<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a chat photo.')]
#[See('https://core.telegram.org/bots/api#chatphoto')]
class ChatPhotoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('File identifier of small (160x160) chat photo. This file\_id can be used only for photo download and only for as long as the photo is not changed.')]
        public string $smallFileId,
        #[Description('Unique file identifier of small (160x160) chat photo, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $smallFileUniqueId,
        #[Description('File identifier of big (640x640) chat photo. This file\_id can be used only for photo download and only for as long as the photo is not changed.')]
        public string $bigFileId,
        #[Description('Unique file identifier of big (640x640) chat photo, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $bigFileUniqueId,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatPhoto;
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
{"small_file_id":{"property":"smallFileId","tgPropName":"small_file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"small_file_unique_id":{"property":"smallFileUniqueId","tgPropName":"small_file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"big_file_id":{"property":"bigFileId","tgPropName":"big_file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"big_file_unique_id":{"property":"bigFileUniqueId","tgPropName":"big_file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
