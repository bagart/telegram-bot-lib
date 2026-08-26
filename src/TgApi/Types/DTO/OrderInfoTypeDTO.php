<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents information about an order.')]
#[See('https://core.telegram.org/bots/api#orderinfo')]
class OrderInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('User name')]
        public ?string $name = null,
        #[Description('User"s phone number')]
        public ?string $phoneNumber = null,
        #[Description('User email')]
        public ?string $email = null,
        #[Description('User shipping address')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ShippingAddressTypeDTO $shippingAddress = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::OrderInfo;
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
{"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"phone_number":{"property":"phoneNumber","tgPropName":"phone_number","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"email":{"property":"email","tgPropName":"email","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"shipping_address":{"property":"shippingAddress","tgPropName":"shipping_address","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ShippingAddressTypeDTO"],"tgTypes":[{"type":"api-type","name":"ShippingAddress"}],"nullable":true,"required":false}}
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
