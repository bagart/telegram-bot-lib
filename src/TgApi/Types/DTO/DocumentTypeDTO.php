<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a general file (as opposed to [photos](https://core.telegram.org/bots/api#photosize), [voice messages](https://core.telegram.org/bots/api#voice) and [audio files](https://core.telegram.org/bots/api#audio)).')]
#[See('https://core.telegram.org/bots/api#document')]
class DocumentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier for this file, which can be used to download or reuse the file')]
        public string $fileId,
        #[Description('Unique identifier for this file, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $fileUniqueId,
        #[Description('Document thumbnail as defined by the sender')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PhotoSizeTypeDTO $thumbnail = null,
        #[Description('Original filename as defined by the sender')]
        public ?string $fileName = null,
        #[Description('MIME type of the file as defined by the sender')]
        public ?string $mimeType = null,
        #[Description('File size in bytes.')]
        public ?string $fileSize = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Document;
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
{"file_id":{"property":"fileId","tgPropName":"file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"file_unique_id":{"property":"fileUniqueId","tgPropName":"file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"thumbnail":{"property":"thumbnail","tgPropName":"thumbnail","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"],"tgTypes":[{"type":"api-type","name":"PhotoSize"}],"nullable":true,"required":false},"file_name":{"property":"fileName","tgPropName":"file_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"mime_type":{"property":"mimeType","tgPropName":"mime_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"file_size":{"property":"fileSize","tgPropName":"file_size","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false}}
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
