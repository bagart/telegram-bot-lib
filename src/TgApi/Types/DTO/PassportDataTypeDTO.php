<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes Telegram Passport data shared with the bot by the user.')]
#[See('https://core.telegram.org/bots/api#passportdata')]
class PassportDataTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Array with information about documents and other Telegram Passport elements that was shared with the bot')]
        public array $data,
        #[Description('Encrypted credentials required to decrypt the data')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\EncryptedCredentialsTypeDTO $credentials,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportData;
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
{"data":{"property":"data","tgPropName":"data","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\EncryptedPassportElementTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"EncryptedPassportElement"}}],"nullable":false,"required":true},"credentials":{"property":"credentials","tgPropName":"credentials","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\EncryptedCredentialsTypeDTO"],"tgTypes":[{"type":"api-type","name":"EncryptedCredentials"}],"nullable":false,"required":true}}
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
