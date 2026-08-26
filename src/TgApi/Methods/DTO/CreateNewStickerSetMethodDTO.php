<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to create a new sticker set owned by a user. The bot will be able to edit the sticker set thus created. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#createnewstickerset')]
class CreateNewStickerSetMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('User identifier of created sticker set owner')]
        public int $userId,
        #[Description('Short name of sticker set, to be used in `t.me/addstickers/` URLs (e.g., _animals_). Can contain only English letters, digits and underscores. Must begin with a letter, can"t contain consecutive underscores and must end in `"_by_<bot_username>"`. `<bot_username>` is case insensitive. 1-64 characters.')]
        public string $name,
        #[Description('Sticker set title, 1-64 characters')]
        public string $title,
        #[Description('An array of 1-50 initial stickers to be added to the sticker set')]
        public array $stickers,
        #[Description('Type of stickers in the set, pass “regular”, “mask”, or “custom\_emoji”. By default, a regular sticker set is created.')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\StickerTypeEnum $stickerType = null,
        #[Description('Pass _True_ if stickers in the sticker set must be repainted to the color of text when used in messages, the accent color if used as emoji status, white on chat photos, or another appropriate color based on context; for custom emoji sticker sets only')]
        public ?bool $needsRepainting = null,
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
        return TgApiMethodsEnum::createNewStickerSet;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"stickers":{"property":"stickers","tgPropName":"stickers","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputStickerTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"InputSticker"}}],"nullable":false,"required":true},"sticker_type":{"property":"stickerType","tgPropName":"sticker_type","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\StickerTypeEnum"],"tgTypes":[{"type":"str","literal":"regular"},{"type":"str","literal":"mask"},{"type":"str","literal":"custom_emoji"}],"nullable":true,"required":false},"needs_repainting":{"property":"needsRepainting","tgPropName":"needs_repainting","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
