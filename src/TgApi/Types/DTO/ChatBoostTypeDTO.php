<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about a chat boost.')]
#[See('https://core.telegram.org/bots/api#chatboost')]
class ChatBoostTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the boost')]
        public string $boostId,
        #[Description('Point in time (Unix timestamp) when the chat was boosted')]
        public int $addDate,
        #[Description('Point in time (Unix timestamp) when the boost will automatically expire, unless the booster"s Telegram Premium subscription is prolonged')]
        public int $expirationDate,
        #[Description('Source of the added boost')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourceTypeDTO $source,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatBoost;
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
{"boost_id":{"property":"boostId","tgPropName":"boost_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"add_date":{"property":"addDate","tgPropName":"add_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"expiration_date":{"property":"expirationDate","tgPropName":"expiration_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"source":{"property":"source","tgPropName":"source","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBoostSourceTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBoostSource"}],"nullable":false,"required":true}}
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
