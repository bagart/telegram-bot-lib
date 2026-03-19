<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes a sticker to be added to a sticker set.')]
#[See('https://core.telegram.org/bots/api#inputsticker')]
class InputStickerTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The added sticker. Pass a _file\_id_ as a String to send a file that already exists on the Telegram servers, pass an HTTP URL as a String for Telegram to get a file from the Internet, or pass “attach://<file\_attach\_name>” to upload a new file using multipart/form-data under <file\_attach\_name> name. Animated and video stickers can"t be uploaded via HTTP URL. [More information on Sending Files »](https://core.telegram.org/bots/api#sending-files)')]
        public string $sticker,
        #[Description('Format of the added sticker, must be one of “static” for a **.WEBP** or **.PNG** image, “animated” for a **.TGS** animation, “video” for a **.WEBM** video')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\FormatEnum $format,
        #[Description('List of 1-20 emoji associated with the sticker')]
        public array $emojiList,
        #[Description('Position where the mask should be placed on faces. For “mask” stickers only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MaskPositionTypeDTO $maskPosition = null,
        #[Description('List of 0-20 search keywords for the sticker with total length of up to 64 characters. For “regular” and “custom\_emoji” stickers only.')]
        public ?array $keywords = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputSticker;
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
{"sticker":{"property":"sticker","tgPropName":"sticker","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"format":{"property":"format","tgPropName":"format","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\FormatEnum"],"tgTypes":[{"type":"str","literal":"static"},{"type":"str","literal":"animated"},{"type":"str","literal":"video"}],"nullable":false,"required":true},"emoji_list":{"property":"emojiList","tgPropName":"emoji_list","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":false,"required":true},"mask_position":{"property":"maskPosition","tgPropName":"mask_position","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MaskPositionTypeDTO"],"tgTypes":[{"type":"api-type","name":"MaskPosition"}],"nullable":true,"required":false},"keywords":{"property":"keywords","tgPropName":"keywords","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false}}
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
