<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\PassportElementErrorDataFieldPropTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents an issue in one of the data fields that was provided by the user. The error is considered resolved when the field"s value changes.')]
#[See('https://core.telegram.org/bots/api#passportelementerrordatafield')]
class PassportElementErrorDataFieldTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The section of the user"s Telegram Passport which has the error, one of “personal\_details”, “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “address”')]
        public PassportElementErrorDataFieldPropTypeEnum $type,
        #[Description('Name of the data field which has the error')]
        public string $fieldName,
        #[Description('Base64-encoded data hash')]
        public string $dataHash,
        #[Description('Error message')]
        public string $message,
        #[Description('Error source, must be _data_')]
        public string $source = 'data',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportElementErrorDataField;
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
{"source":{"property":"source","tgPropName":"source","types":["string"],"tgTypes":[{"type":"str","literal":"data"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PassportElementErrorDataFieldPropTypeEnum"],"tgTypes":[{"type":"str","literal":"personal_details"},{"type":"str","literal":"passport"},{"type":"str","literal":"driver_license"},{"type":"str","literal":"identity_card"},{"type":"str","literal":"internal_passport"},{"type":"str","literal":"address"}],"nullable":false,"required":true},"field_name":{"property":"fieldName","tgPropName":"field_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"data_hash":{"property":"dataHash","tgPropName":"data_hash","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
