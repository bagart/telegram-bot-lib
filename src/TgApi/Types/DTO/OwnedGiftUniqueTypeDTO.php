<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a unique gift received and owned by a user or a chat.')]
#[See('https://core.telegram.org/bots/api#ownedgiftunique')]
class OwnedGiftUniqueTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Information about the unique gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftTypeDTO $gift,
        #[Description('Date the gift was sent in Unix time')]
        public int $sendDate,
        #[Description('Type of the gift, always “unique”')]
        public string $type = 'unique',
        #[Description('Unique identifier of the received gift for the bot; for gifts received on behalf of business accounts only')]
        public ?string $ownedGiftId = null,
        #[Description('Sender of the gift if it is a known user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $senderUser = null,
        #[Description('_True_, if the gift is displayed on the account"s profile page; for gifts received on behalf of business accounts only')]
        public ?bool $isSaved = true,
        #[Description('_True_, if the gift can be transferred to another owner; for gifts received on behalf of business accounts only')]
        public ?bool $canBeTransferred = true,
        #[Description('Number of Telegram Stars that must be paid to transfer the gift; omitted if the bot cannot transfer the gift')]
        public ?int $transferStarCount = null,
        #[Description('Point in time (Unix timestamp) when the gift can be transferred. If it is in the past, then the gift can be transferred now')]
        public ?int $nextTransferDate = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::OwnedGiftUnique;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"unique"}],"nullable":false,"required":true},"gift":{"property":"gift","tgPropName":"gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGift"}],"nullable":false,"required":true},"owned_gift_id":{"property":"ownedGiftId","tgPropName":"owned_gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"sender_user":{"property":"senderUser","tgPropName":"sender_user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"send_date":{"property":"sendDate","tgPropName":"send_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_saved":{"property":"isSaved","tgPropName":"is_saved","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_be_transferred":{"property":"canBeTransferred","tgPropName":"can_be_transferred","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"transfer_star_count":{"property":"transferStarCount","tgPropName":"transfer_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"next_transfer_date":{"property":"nextTransferDate","tgPropName":"next_transfer_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
