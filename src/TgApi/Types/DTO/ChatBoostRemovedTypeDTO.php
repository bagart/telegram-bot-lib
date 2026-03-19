<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a boost removed from a chat.')]
#[See('https://core.telegram.org/bots/api#chatboostremoved')]
class ChatBoostRemovedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Chat which was boosted')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $chat,
        #[Description('Unique identifier of the boost')]
        public string $boostId,
        #[Description('Point in time (Unix timestamp) when the boost was removed')]
        public int $removeDate,
        #[Description('Source of the removed boost')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourceTypeDTO $source,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatBoostRemoved;
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
{"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"boost_id":{"property":"boostId","tgPropName":"boost_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"remove_date":{"property":"removeDate","tgPropName":"remove_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"source":{"property":"source","tgPropName":"source","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBoostSourceTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBoostSource"}],"nullable":false,"required":true}}
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
