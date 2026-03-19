<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Changes the privacy settings pertaining to incoming gifts in a managed business account. Requires the _can\_change\_gift\_settings_ business bot right. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setbusinessaccountgiftsettings')]
class SetBusinessAccountGiftSettingsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('Pass _True_, if a button for sending a gift to the user or by the business account must always be shown in the input field')]
        public bool $showGiftButton,
        #[Description('Types of gifts accepted by the business account')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\AcceptedGiftTypesTypeDTO $acceptedGiftTypes,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::setBusinessAccountGiftSettings;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"show_gift_button":{"property":"showGiftButton","tgPropName":"show_gift_button","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"accepted_gift_types":{"property":"acceptedGiftTypes","tgPropName":"accepted_gift_types","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AcceptedGiftTypesTypeDTO"],"tgTypes":[{"type":"api-type","name":"AcceptedGiftTypes"}],"nullable":false,"required":true}}
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
