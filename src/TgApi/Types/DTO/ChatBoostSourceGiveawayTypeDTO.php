<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The boost was obtained by the creation of a Telegram Premium or a Telegram Star giveaway. This boosts the chat 4 times for the duration of the corresponding Telegram Premium subscription for Telegram Premium giveaways and _prize\_star\_count_ / 500 times for one year for Telegram Star giveaways.')]
#[See('https://core.telegram.org/bots/api#chatboostsourcegiveaway')]
class ChatBoostSourceGiveawayTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of a message in the chat with the giveaway; the message could have been deleted already. May be 0 if the message isn"t sent yet.')]
        public int $giveawayMessageId,
        #[Description('Source of the boost, always “giveaway”')]
        public string $source = 'giveaway',
        #[Description('User that won the prize in the giveaway if any; for Telegram Premium giveaways only')]
        public ?UserTypeDTO $user = null,
        #[Description('The number of Telegram Stars to be split between giveaway winners; for Telegram Star giveaways only')]
        public ?int $prizeStarCount = null,
        #[Description('_True_, if the giveaway was completed, but there was no user to win the prize')]
        public ?bool $isUnclaimed = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatBoostSourceGiveaway;
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
{"source":{"property":"source","tgPropName":"source","types":["string"],"tgTypes":[{"type":"str","literal":"giveaway"}],"nullable":false,"required":true},"giveaway_message_id":{"property":"giveawayMessageId","tgPropName":"giveaway_message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"prize_star_count":{"property":"prizeStarCount","tgPropName":"prize_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_unclaimed":{"property":"isUnclaimed","tgPropName":"is_unclaimed","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
