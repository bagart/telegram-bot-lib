<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a regular gift owned by a user or a chat.')]
#[See('https://core.telegram.org/bots/api#ownedgiftregular')]
class OwnedGiftRegularTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Information about the regular gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\GiftTypeDTO $gift,
        #[Description('Date the gift was sent in Unix time')]
        public int $sendDate,
        #[Description('Type of the gift, always “regular”')]
        public string $type = 'regular',
        #[Description('Unique identifier of the gift for the bot; for gifts received on behalf of business accounts only')]
        public ?string $ownedGiftId = null,
        #[Description('Sender of the gift if it is a known user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $senderUser = null,
        #[Description('Text of the message that was added to the gift')]
        public ?string $text = null,
        #[Description('Special entities that appear in the text')]
        public ?array $entities = null,
        #[Description('_True_, if the sender and gift text are shown only to the gift receiver; otherwise, everyone will be able to see them')]
        public ?bool $isPrivate = true,
        #[Description('_True_, if the gift is displayed on the account"s profile page; for gifts received on behalf of business accounts only')]
        public ?bool $isSaved = true,
        #[Description('_True_, if the gift can be upgraded to a unique gift; for gifts received on behalf of business accounts only')]
        public ?bool $canBeUpgraded = true,
        #[Description('_True_, if the gift was refunded and isn"t available anymore')]
        public ?bool $wasRefunded = true,
        #[Description('Number of Telegram Stars that can be claimed by the receiver instead of the gift; omitted if the gift cannot be converted to Telegram Stars; for gifts received on behalf of business accounts only')]
        public ?int $convertStarCount = null,
        #[Description('Number of Telegram Stars that were paid for the ability to upgrade the gift')]
        public ?int $prepaidUpgradeStarCount = null,
        #[Description('_True_, if the gift"s upgrade was purchased after the gift was sent; for gifts received on behalf of business accounts only')]
        public ?bool $isUpgradeSeparate = true,
        #[Description('Unique number reserved for this gift when upgraded. See the _number_ field in [UniqueGift](https://core.telegram.org/bots/api#uniquegift)')]
        public ?int $uniqueGiftNumber = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::OwnedGiftRegular;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"regular"}],"nullable":false,"required":true},"gift":{"property":"gift","tgPropName":"gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiftTypeDTO"],"tgTypes":[{"type":"api-type","name":"Gift"}],"nullable":false,"required":true},"owned_gift_id":{"property":"ownedGiftId","tgPropName":"owned_gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"sender_user":{"property":"senderUser","tgPropName":"sender_user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"send_date":{"property":"sendDate","tgPropName":"send_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"entities":{"property":"entities","tgPropName":"entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"is_private":{"property":"isPrivate","tgPropName":"is_private","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_saved":{"property":"isSaved","tgPropName":"is_saved","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_be_upgraded":{"property":"canBeUpgraded","tgPropName":"can_be_upgraded","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"was_refunded":{"property":"wasRefunded","tgPropName":"was_refunded","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"convert_star_count":{"property":"convertStarCount","tgPropName":"convert_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"prepaid_upgrade_star_count":{"property":"prepaidUpgradeStarCount","tgPropName":"prepaid_upgrade_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_upgrade_separate":{"property":"isUpgradeSeparate","tgPropName":"is_upgrade_separate","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"unique_gift_number":{"property":"uniqueGiftNumber","tgPropName":"unique_gift_number","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
