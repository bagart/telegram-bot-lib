<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\InputStickerTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to replace an existing sticker in a sticker set with a new one. The method is equivalent to calling [deleteStickerFromSet](https://core.telegram.org/bots/api#deletestickerfromset), then [addStickerToSet](https://core.telegram.org/bots/api#addstickertoset), then [setStickerPositionInSet](https://core.telegram.org/bots/api#setstickerpositioninset). Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#replacestickerinset')]
class ReplaceStickerInSetMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('User identifier of the sticker set owner')]
        public int $userId,
        #[Description('Sticker set name')]
        public string $name,
        #[Description('File identifier of the replaced sticker')]
        public string $oldSticker,
        #[Description('An object with information about the added sticker. If exactly the same sticker had already been added to the set, then the set remains unchanged.')]
        public InputStickerTypeDTO $sticker,
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
        return TgApiMethodsEnum::replaceStickerInSet;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"old_sticker":{"property":"oldSticker","tgPropName":"old_sticker","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"sticker":{"property":"sticker","tgPropName":"sticker","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputStickerTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputSticker"}],"nullable":false,"required":true}}
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
