<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents an issue with the reverse side of a document. The error is considered resolved when the file with reverse side of the document changes.')]
#[See('https://core.telegram.org/bots/api#passportelementerrorreverseside')]
class PassportElementErrorReverseSideTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The section of the user"s Telegram Passport which has the issue, one of “driver\_license”, “identity\_card”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\PassportElementErrorReverseSidePropTypeEnum $type,
        #[Description('Base64-encoded hash of the file with the reverse side of the document')]
        public string $fileHash,
        #[Description('Error message')]
        public string $message,
        #[Description('Error source, must be _reverse\_side_')]
        public string $source = 'reverse_side',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportElementErrorReverseSide;
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
{"source":{"property":"source","tgPropName":"source","types":["string"],"tgTypes":[{"type":"str","literal":"reverse_side"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PassportElementErrorReverseSidePropTypeEnum"],"tgTypes":[{"type":"str","literal":"driver_license"},{"type":"str","literal":"identity_card"}],"nullable":false,"required":true},"file_hash":{"property":"fileHash","tgPropName":"file_hash","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
