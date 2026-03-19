<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\StickerPropTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a sticker.')]
#[See('https://core.telegram.org/bots/api#sticker')]
class StickerTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier for this file, which can be used to download or reuse the file')]
        public string $fileId,
        #[Description('Unique identifier for this file, which is supposed to be the same over time and for different bots. Can"t be used to download or reuse the file.')]
        public string $fileUniqueId,
        #[Description('Type of the sticker, currently one of “regular”, “mask”, “custom\_emoji”. The type of the sticker is independent from its format, which is determined by the fields _is\_animated_ and _is\_video_.')]
        public StickerPropTypeEnum $type,
        #[Description('Sticker width')]
        public int $width,
        #[Description('Sticker height')]
        public int $height,
        #[Description('_True_, if the sticker is [animated](https://telegram.org/blog/animated-stickers)')]
        public bool $isAnimated,
        #[Description('_True_, if the sticker is a [video sticker](https://telegram.org/blog/video-stickers-better-reactions)')]
        public bool $isVideo,
        #[Description('Sticker thumbnail in the .WEBP or .JPG format')]
        public ?PhotoSizeTypeDTO $thumbnail = null,
        #[Description('Emoji associated with the sticker')]
        public ?string $emoji = null,
        #[Description('Name of the sticker set to which the sticker belongs')]
        public ?string $setName = null,
        #[Description('For premium regular stickers, premium animation for the sticker')]
        public ?FileTypeDTO $premiumAnimation = null,
        #[Description('For mask stickers, the position where the mask should be placed')]
        public ?MaskPositionTypeDTO $maskPosition = null,
        #[Description('For custom emoji stickers, unique identifier of the custom emoji')]
        public ?string $customEmojiId = null,
        #[Description('_True_, if the sticker must be repainted to a text color in messages, the color of the Telegram Premium badge in emoji status, white color on chat photos, or another appropriate color in other places')]
        public ?bool $needsRepainting = true,
        #[Description('File size in bytes')]
        public ?int $fileSize = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Sticker;
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
{"file_id":{"property":"fileId","tgPropName":"file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"file_unique_id":{"property":"fileUniqueId","tgPropName":"file_unique_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\StickerPropTypeEnum"],"tgTypes":[{"type":"str","literal":"regular"},{"type":"str","literal":"mask"},{"type":"str","literal":"custom_emoji"}],"nullable":false,"required":true},"width":{"property":"width","tgPropName":"width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"height":{"property":"height","tgPropName":"height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_animated":{"property":"isAnimated","tgPropName":"is_animated","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"is_video":{"property":"isVideo","tgPropName":"is_video","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"thumbnail":{"property":"thumbnail","tgPropName":"thumbnail","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"],"tgTypes":[{"type":"api-type","name":"PhotoSize"}],"nullable":true,"required":false},"emoji":{"property":"emoji","tgPropName":"emoji","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"set_name":{"property":"setName","tgPropName":"set_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"premium_animation":{"property":"premiumAnimation","tgPropName":"premium_animation","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\FileTypeDTO"],"tgTypes":[{"type":"api-type","name":"File"}],"nullable":true,"required":false},"mask_position":{"property":"maskPosition","tgPropName":"mask_position","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MaskPositionTypeDTO"],"tgTypes":[{"type":"api-type","name":"MaskPosition"}],"nullable":true,"required":false},"custom_emoji_id":{"property":"customEmojiId","tgPropName":"custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"needs_repainting":{"property":"needsRepainting","tgPropName":"needs_repainting","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"file_size":{"property":"fileSize","tgPropName":"file_size","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
