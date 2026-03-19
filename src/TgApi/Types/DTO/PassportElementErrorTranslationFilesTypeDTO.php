<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents an issue with the translated version of a document. The error is considered resolved when a file with the document translation change.')]
#[See('https://core.telegram.org/bots/api#passportelementerrortranslationfiles')]
class PassportElementErrorTranslationFilesTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Type of element of the user"s Telegram Passport which has the issue, one of “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration”, “temporary\_registration”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\PassportElementErrorTranslationFilesPropTypeEnum $type,
        #[Description('List of base64-encoded file hashes')]
        public array $fileHashes,
        #[Description('Error message')]
        public string $message,
        #[Description('Error source, must be _translation\_files_')]
        public string $source = 'translation_files',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportElementErrorTranslationFiles;
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
{"source":{"property":"source","tgPropName":"source","types":["string"],"tgTypes":[{"type":"str","literal":"translation_files"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PassportElementErrorTranslationFilesPropTypeEnum"],"tgTypes":[{"type":"str","literal":"passport"},{"type":"str","literal":"driver_license"},{"type":"str","literal":"identity_card"},{"type":"str","literal":"internal_passport"},{"type":"str","literal":"utility_bill"},{"type":"str","literal":"bank_statement"},{"type":"str","literal":"rental_agreement"},{"type":"str","literal":"passport_registration"},{"type":"str","literal":"temporary_registration"}],"nullable":false,"required":true},"file_hashes":{"property":"fileHashes","tgPropName":"file_hashes","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
