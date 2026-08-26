<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a message about a scheduled giveaway.')]
#[See('https://core.telegram.org/bots/api#giveaway')]
class GiveawayTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The list of chats which the user must join to participate in the giveaway')]
        public array $chats,
        #[Description('Point in time (Unix timestamp) when winners of the giveaway will be selected')]
        public int $winnersSelectionDate,
        #[Description('The number of users which are supposed to be selected as winners of the giveaway')]
        public int $winnerCount,
        #[Description('_True_, if only users who join the chats after the giveaway started should be eligible to win')]
        public ?bool $onlyNewMembers = true,
        #[Description('_True_, if the list of giveaway winners will be visible to everyone')]
        public ?bool $hasPublicWinners = true,
        #[Description('Description of additional giveaway prize')]
        public ?string $prizeDescription = null,
        #[Description('A list of two-letter [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) country codes indicating the countries from which eligible users for the giveaway must come. If empty, then all users can participate in the giveaway. Users with a phone number that was bought on Fragment can always participate in giveaways.')]
        public ?array $countryCodes = null,
        #[Description('The number of Telegram Stars to be split between giveaway winners; for Telegram Star giveaways only')]
        public ?int $prizeStarCount = null,
        #[Description('The number of months the Telegram Premium subscription won from the giveaway will be active for; for Telegram Premium giveaways only')]
        public ?int $premiumSubscriptionMonthCount = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Giveaway;
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
{"chats":{"property":"chats","tgPropName":"chats","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"Chat"}}],"nullable":false,"required":true},"winners_selection_date":{"property":"winnersSelectionDate","tgPropName":"winners_selection_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"winner_count":{"property":"winnerCount","tgPropName":"winner_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"only_new_members":{"property":"onlyNewMembers","tgPropName":"only_new_members","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_public_winners":{"property":"hasPublicWinners","tgPropName":"has_public_winners","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"prize_description":{"property":"prizeDescription","tgPropName":"prize_description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"country_codes":{"property":"countryCodes","tgPropName":"country_codes","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false},"prize_star_count":{"property":"prizeStarCount","tgPropName":"prize_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"premium_subscription_month_count":{"property":"premiumSubscriptionMonthCount","tgPropName":"premium_subscription_month_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
