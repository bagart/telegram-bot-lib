<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a service message about the approval of a suggested post.')]
#[See('https://core.telegram.org/bots/api#suggestedpostapproved')]
class SuggestedPostApprovedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Date when the post will be published')]
        public int $sendDate,
        #[Description('Message containing the suggested post. Note that the [Message](https://core.telegram.org/bots/api#message) object in this field will not contain the _reply\_to\_message_ field even if it itself is a reply.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $suggestedPostMessage = null,
        #[Description('Amount paid for the post')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostPriceTypeDTO $price = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SuggestedPostApproved;
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
{"suggested_post_message":{"property":"suggestedPostMessage","tgPropName":"suggested_post_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"price":{"property":"price","tgPropName":"price","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostPriceTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostPrice"}],"nullable":true,"required":false},"send_date":{"property":"sendDate","tgPropName":"send_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
