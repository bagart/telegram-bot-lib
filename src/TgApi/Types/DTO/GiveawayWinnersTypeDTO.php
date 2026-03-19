<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a message about the completion of a giveaway with public winners.')]
#[See('https://core.telegram.org/bots/api#giveawaywinners')]
class GiveawayWinnersTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The chat that created the giveaway')]
        public ChatTypeDTO $chat,
        #[Description('Identifier of the message with the giveaway in the chat')]
        public int $giveawayMessageId,
        #[Description('Point in time (Unix timestamp) when winners of the giveaway were selected')]
        public int $winnersSelectionDate,
        #[Description('Total number of winners in the giveaway')]
        public int $winnerCount,
        #[Description('List of up to 100 winners of the giveaway')]
        public array $winners,
        #[Description('The number of other chats the user had to join in order to be eligible for the giveaway')]
        public ?int $additionalChatCount = null,
        #[Description('The number of Telegram Stars that were split between giveaway winners; for Telegram Star giveaways only')]
        public ?int $prizeStarCount = null,
        #[Description('The number of months the Telegram Premium subscription won from the giveaway will be active for; for Telegram Premium giveaways only')]
        public ?int $premiumSubscriptionMonthCount = null,
        #[Description('Number of undistributed prizes')]
        public ?int $unclaimedPrizeCount = null,
        #[Description('_True_, if only users who had joined the chats after the giveaway started were eligible to win')]
        public ?bool $onlyNewMembers = true,
        #[Description('_True_, if the giveaway was canceled because the payment for it was refunded')]
        public ?bool $wasRefunded = true,
        #[Description('Description of additional giveaway prize')]
        public ?string $prizeDescription = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::GiveawayWinners;
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
{"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"giveaway_message_id":{"property":"giveawayMessageId","tgPropName":"giveaway_message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"winners_selection_date":{"property":"winnersSelectionDate","tgPropName":"winners_selection_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"winner_count":{"property":"winnerCount","tgPropName":"winner_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"winners":{"property":"winners","tgPropName":"winners","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"User"}}],"nullable":false,"required":true},"additional_chat_count":{"property":"additionalChatCount","tgPropName":"additional_chat_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"prize_star_count":{"property":"prizeStarCount","tgPropName":"prize_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"premium_subscription_month_count":{"property":"premiumSubscriptionMonthCount","tgPropName":"premium_subscription_month_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"unclaimed_prize_count":{"property":"unclaimedPrizeCount","tgPropName":"unclaimed_prize_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"only_new_members":{"property":"onlyNewMembers","tgPropName":"only_new_members","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"was_refunded":{"property":"wasRefunded","tgPropName":"was_refunded","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"prize_description":{"property":"prizeDescription","tgPropName":"prize_description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
