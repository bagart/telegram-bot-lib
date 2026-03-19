<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a service message about the completion of a giveaway without public winners.')]
#[See('https://core.telegram.org/bots/api#giveawaycompleted')]
class GiveawayCompletedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Number of winners in the giveaway')]
        public int $winnerCount,
        #[Description('Number of undistributed prizes')]
        public ?int $unclaimedPrizeCount = null,
        #[Description('Message with the giveaway that was completed, if it wasn"t deleted')]
        public ?MessageTypeDTO $giveawayMessage = null,
        #[Description('_True_, if the giveaway is a Telegram Star giveaway. Otherwise, currently, the giveaway is a Telegram Premium giveaway.')]
        public ?bool $isStarGiveaway = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::GiveawayCompleted;
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
{"winner_count":{"property":"winnerCount","tgPropName":"winner_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"unclaimed_prize_count":{"property":"unclaimedPrizeCount","tgPropName":"unclaimed_prize_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"giveaway_message":{"property":"giveawayMessage","tgPropName":"giveaway_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"is_star_giveaway":{"property":"isStarGiveaway","tgPropName":"is_star_giveaway","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
