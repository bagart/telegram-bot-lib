<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes documents or other Telegram Passport elements shared with the bot by the user.')]
#[See('https://core.telegram.org/bots/api#encryptedpassportelement')]
class EncryptedPassportElementTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Element type. One of “personal\_details”, “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “address”, “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration”, “temporary\_registration”, “phone\_number”, “email”.')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\EncryptedPassportElementPropTypeEnum $type,
        #[Description('Base64-encoded element hash for using in [PassportElementErrorUnspecified](https://core.telegram.org/bots/api#passportelementerrorunspecified)')]
        public string $hash,
        #[Description('Base64-encoded encrypted Telegram Passport element data provided by the user; available only for “personal\_details”, “passport”, “driver\_license”, “identity\_card”, “internal\_passport” and “address” types. Can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?string $data = null,
        #[Description('User"s verified phone number; available only for “phone\_number” type')]
        public ?string $phoneNumber = null,
        #[Description('User"s verified email address; available only for “email” type')]
        public ?string $email = null,
        #[Description('Array of encrypted files with documents provided by the user; available only for “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration” and “temporary\_registration” types. Files can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?array $files = null,
        #[Description('Encrypted file with the front side of the document, provided by the user; available only for “passport”, “driver\_license”, “identity\_card” and “internal\_passport”. The file can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PassportFileTypeDTO $frontSide = null,
        #[Description('Encrypted file with the reverse side of the document, provided by the user; available only for “driver\_license” and “identity\_card”. The file can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PassportFileTypeDTO $reverseSide = null,
        #[Description('Encrypted file with the selfie of the user holding a document, provided by the user; available if requested for “passport”, “driver\_license”, “identity\_card” and “internal\_passport”. The file can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PassportFileTypeDTO $selfie = null,
        #[Description('Array of encrypted files with translated versions of documents provided by the user; available if requested for “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration” and “temporary\_registration” types. Files can be decrypted and verified using the accompanying [EncryptedCredentials](https://core.telegram.org/bots/api#encryptedcredentials).')]
        public ?array $translation = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::EncryptedPassportElement;
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
{"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\EncryptedPassportElementPropTypeEnum"],"tgTypes":[{"type":"str","literal":"personal_details"},{"type":"str","literal":"passport"},{"type":"str","literal":"driver_license"},{"type":"str","literal":"identity_card"},{"type":"str","literal":"internal_passport"},{"type":"str","literal":"address"},{"type":"str","literal":"utility_bill"},{"type":"str","literal":"bank_statement"},{"type":"str","literal":"rental_agreement"},{"type":"str","literal":"passport_registration"},{"type":"str","literal":"temporary_registration"},{"type":"str","literal":"phone_number"},{"type":"str","literal":"email"}],"nullable":false,"required":true},"data":{"property":"data","tgPropName":"data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"phone_number":{"property":"phoneNumber","tgPropName":"phone_number","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"email":{"property":"email","tgPropName":"email","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"files":{"property":"files","tgPropName":"files","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportFileTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PassportFile"}}],"nullable":true,"required":false},"front_side":{"property":"frontSide","tgPropName":"front_side","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportFileTypeDTO"],"tgTypes":[{"type":"api-type","name":"PassportFile"}],"nullable":true,"required":false},"reverse_side":{"property":"reverseSide","tgPropName":"reverse_side","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportFileTypeDTO"],"tgTypes":[{"type":"api-type","name":"PassportFile"}],"nullable":true,"required":false},"selfie":{"property":"selfie","tgPropName":"selfie","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportFileTypeDTO"],"tgTypes":[{"type":"api-type","name":"PassportFile"}],"nullable":true,"required":false},"translation":{"property":"translation","tgPropName":"translation","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportFileTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PassportFile"}}],"nullable":true,"required":false},"hash":{"property":"hash","tgPropName":"hash","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
