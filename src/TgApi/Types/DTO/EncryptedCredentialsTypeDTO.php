<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes data required for decrypting and authenticating [EncryptedPassportElement](https://core.telegram.org/bots/api#encryptedpassportelement). See the [Telegram Passport Documentation](https://core.telegram.org/passport#receiving-information) for a complete description of the data decryption and authentication processes.')]
#[See('https://core.telegram.org/bots/api#encryptedcredentials')]
class EncryptedCredentialsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Base64-encoded encrypted JSON-serialized data with unique user"s payload, data hashes and secrets required for [EncryptedPassportElement](https://core.telegram.org/bots/api#encryptedpassportelement) decryption and authentication')]
        public string $data,
        #[Description('Base64-encoded data hash for data authentication')]
        public string $hash,
        #[Description('Base64-encoded secret, encrypted with the bot"s public RSA key, required for data decryption')]
        public string $secret,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::EncryptedCredentials;
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
{"data":{"property":"data","tgPropName":"data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"hash":{"property":"hash","tgPropName":"hash","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"secret":{"property":"secret","tgPropName":"secret","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
