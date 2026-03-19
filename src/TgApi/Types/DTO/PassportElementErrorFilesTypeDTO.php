<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents an issue with a list of scans. The error is considered resolved when the list of files containing the scans changes.')]
#[See('https://core.telegram.org/bots/api#passportelementerrorfiles')]
class PassportElementErrorFilesTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The section of the user"s Telegram Passport which has the issue, one of “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration”, “temporary\_registration”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\PassportElementErrorFilesPropTypeEnum $type,
        #[Description('List of base64-encoded file hashes')]
        public array $fileHashes,
        #[Description('Error message')]
        public string $message,
        #[Description('Error source, must be _files_')]
        public string $source = 'files',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportElementErrorFiles;
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
{"source":{"property":"source","tgPropName":"source","types":["string"],"tgTypes":[{"type":"str","literal":"files"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PassportElementErrorFilesPropTypeEnum"],"tgTypes":[{"type":"str","literal":"utility_bill"},{"type":"str","literal":"bank_statement"},{"type":"str","literal":"rental_agreement"},{"type":"str","literal":"passport_registration"},{"type":"str","literal":"temporary_registration"}],"nullable":false,"required":true},"file_hashes":{"property":"fileHashes","tgPropName":"file_hashes","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
