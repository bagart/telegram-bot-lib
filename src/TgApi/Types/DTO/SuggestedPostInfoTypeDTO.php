<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\StateEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Contains information about a suggested post.')]
#[See('https://core.telegram.org/bots/api#suggestedpostinfo')]
class SuggestedPostInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('State of the suggested post. Currently, it can be one of “pending”, “approved”, “declined”.')]
        public StateEnum $state,
        #[Description('Proposed price of the post. If the field is omitted, then the post is unpaid.')]
        public ?SuggestedPostPriceTypeDTO $price = null,
        #[Description('Proposed send date of the post. If the field is omitted, then the post can be published at any time within 30 days at the sole discretion of the user or administrator who approves it.')]
        public ?int $sendDate = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SuggestedPostInfo;
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
{"state":{"property":"state","tgPropName":"state","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\StateEnum"],"tgTypes":[{"type":"str","literal":"pending"},{"type":"str","literal":"approved"},{"type":"str","literal":"declined"}],"nullable":false,"required":true},"price":{"property":"price","tgPropName":"price","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostPriceTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostPrice"}],"nullable":true,"required":false},"send_date":{"property":"sendDate","tgPropName":"send_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
