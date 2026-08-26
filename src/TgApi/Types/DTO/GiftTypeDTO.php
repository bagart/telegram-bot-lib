<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a gift that can be sent by the bot.')]
#[See('https://core.telegram.org/bots/api#gift')]
class GiftTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the gift')]
        public string $id,
        #[Description('The sticker that represents the gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\StickerTypeDTO $sticker,
        #[Description('The number of Telegram Stars that must be paid to send the sticker')]
        public int $starCount,
        #[Description('The number of Telegram Stars that must be paid to upgrade the gift to a unique one')]
        public ?int $upgradeStarCount = null,
        #[Description('_True_, if the gift can only be purchased by Telegram Premium subscribers')]
        public ?bool $isPremium = true,
        #[Description('_True_, if the gift can be used (after being upgraded) to customize a user"s appearance')]
        public ?bool $hasColors = true,
        #[Description('The total number of gifts of this type that can be sent by all users; for limited gifts only')]
        public ?int $totalCount = null,
        #[Description('The number of remaining gifts of this type that can be sent by all users; for limited gifts only')]
        public ?int $remainingCount = null,
        #[Description('The total number of gifts of this type that can be sent by the bot; for limited gifts only')]
        public ?int $personalTotalCount = null,
        #[Description('The number of remaining gifts of this type that can be sent by the bot; for limited gifts only')]
        public ?int $personalRemainingCount = null,
        #[Description('Background of the gift')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\GiftBackgroundTypeDTO $background = null,
        #[Description('The total number of different unique gifts that can be obtained by upgrading the gift')]
        public ?int $uniqueGiftVariantCount = null,
        #[Description('Information about the chat that published the gift')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $publisherChat = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Gift;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"sticker":{"property":"sticker","tgPropName":"sticker","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StickerTypeDTO"],"tgTypes":[{"type":"api-type","name":"Sticker"}],"nullable":false,"required":true},"star_count":{"property":"starCount","tgPropName":"star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"upgrade_star_count":{"property":"upgradeStarCount","tgPropName":"upgrade_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_premium":{"property":"isPremium","tgPropName":"is_premium","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_colors":{"property":"hasColors","tgPropName":"has_colors","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"total_count":{"property":"totalCount","tgPropName":"total_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"remaining_count":{"property":"remainingCount","tgPropName":"remaining_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"personal_total_count":{"property":"personalTotalCount","tgPropName":"personal_total_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"personal_remaining_count":{"property":"personalRemainingCount","tgPropName":"personal_remaining_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"background":{"property":"background","tgPropName":"background","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiftBackgroundTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiftBackground"}],"nullable":true,"required":false},"unique_gift_variant_count":{"property":"uniqueGiftVariantCount","tgPropName":"unique_gift_variant_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"publisher_chat":{"property":"publisherChat","tgPropName":"publisher_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false}}
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
