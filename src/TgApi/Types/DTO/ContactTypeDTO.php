<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a phone contact.')]
#[See('https://core.telegram.org/bots/api#contact')]
class ContactTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Contact"s phone number')]
        public string $phoneNumber,
        #[Description('Contact"s first name')]
        public string $firstName,
        #[Description('Contact"s last name')]
        public ?string $lastName = null,
        #[Description('Contact"s user identifier in Telegram.')]
        public ?string $userId = null,
        #[Description('Additional data about the contact in the form of a [vCard](https://en.wikipedia.org/wiki/VCard)')]
        public ?string $vcard = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Contact;
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
{"phone_number":{"property":"phoneNumber","tgPropName":"phone_number","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"user_id":{"property":"userId","tgPropName":"user_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false},"vcard":{"property":"vcard","tgPropName":"vcard","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
