<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes a unique gift that was upgraded from a regular gift.')]
#[See('https://core.telegram.org/bots/api#uniquegift')]
class UniqueGiftTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the regular gift from which the gift was upgraded')]
        public string $giftId,
        #[Description('Human-readable name of the regular gift from which this unique gift was upgraded')]
        public string $baseName,
        #[Description('Unique name of the gift. This name can be used in `https://t.me/nft/...` links and story areas')]
        public string $name,
        #[Description('Unique number of the upgraded gift among gifts upgraded from the same regular gift')]
        public int $number,
        #[Description('Model of the gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftModelTypeDTO $model,
        #[Description('Symbol of the gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftSymbolTypeDTO $symbol,
        #[Description('Backdrop of the gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftBackdropTypeDTO $backdrop,
        #[Description('_True_, if the original regular gift was exclusively purchaseable by Telegram Premium subscribers')]
        public ?bool $isPremium = true,
        #[Description('_True_, if the gift was used to craft another gift and isn"t available anymore')]
        public ?bool $isBurned = true,
        #[Description('_True_, if the gift is assigned from the TON blockchain and can"t be resold or transferred in Telegram')]
        public ?bool $isFromBlockchain = true,
        #[Description('The color scheme that can be used by the gift"s owner for the chat"s name, replies to messages and link previews; for business account gifts and gifts that are currently on sale only')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftColorsTypeDTO $colors = null,
        #[Description('Information about the chat that published the gift')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $publisherChat = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGift;
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
{"gift_id":{"property":"giftId","tgPropName":"gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"base_name":{"property":"baseName","tgPropName":"base_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"number":{"property":"number","tgPropName":"number","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"model":{"property":"model","tgPropName":"model","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftModelTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftModel"}],"nullable":false,"required":true},"symbol":{"property":"symbol","tgPropName":"symbol","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftSymbolTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftSymbol"}],"nullable":false,"required":true},"backdrop":{"property":"backdrop","tgPropName":"backdrop","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftBackdropTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftBackdrop"}],"nullable":false,"required":true},"is_premium":{"property":"isPremium","tgPropName":"is_premium","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_burned":{"property":"isBurned","tgPropName":"is_burned","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_from_blockchain":{"property":"isFromBlockchain","tgPropName":"is_from_blockchain","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"colors":{"property":"colors","tgPropName":"colors","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftColorsTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftColors"}],"nullable":true,"required":false},"publisher_chat":{"property":"publisherChat","tgPropName":"publisher_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false}}
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
