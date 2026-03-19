<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\FormatEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to set the thumbnail of a regular or mask sticker set. The format of the thumbnail file must match the format of the stickers in the set. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setstickersetthumbnail')]
class SetStickerSetThumbnailMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Sticker set name')]
        public string $name,
        #[Description('User identifier of the sticker set owner')]
        public int $userId,
        #[Description('Format of the thumbnail, must be one of “static” for a **.WEBP** or **.PNG** image, “animated” for a **.TGS** animation, or “video” for a **.WEBM** video')]
        public FormatEnum $format,
        #[Description('A **.WEBP** or **.PNG** image with the thumbnail, must be up to 128 kilobytes in size and have a width and height of exactly 100px, or a **.TGS** animation with a thumbnail up to 32 kilobytes in size (see [](https://core.telegram.org/stickers#animation-requirements)[https://core.telegram.org/stickers#animation-requirements](https://core.telegram.org/stickers#animation-requirements) for animated sticker technical requirements), or a **.WEBM** video with the thumbnail up to 32 kilobytes in size; see [](https://core.telegram.org/stickers#video-requirements)[https://core.telegram.org/stickers#video-requirements](https://core.telegram.org/stickers#video-requirements) for video sticker technical requirements. Pass a _file\_id_ as a String to send a file that already exists on the Telegram servers, pass an HTTP URL as a String for Telegram to get a file from the Internet, or upload a new one using multipart/form-data. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files). Animated and video sticker set thumbnails can"t be uploaded via HTTP URL. If omitted, then the thumbnail is dropped and the first sticker is used as the thumbnail.')]
        public ?string $thumbnail = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::setStickerSetThumbnail;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"thumbnail":{"property":"thumbnail","tgPropName":"thumbnail","types":["string"],"tgTypes":[{"type":"input-file"},{"type":"str"}],"nullable":true,"required":false},"format":{"property":"format","tgPropName":"format","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\FormatEnum"],"tgTypes":[{"type":"str","literal":"static"},{"type":"str","literal":"animated"},{"type":"str","literal":"video"}],"nullable":false,"required":true}}
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
