<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a task to add to a checklist.')]
#[See('https://core.telegram.org/bots/api#inputchecklisttask')]
class InputChecklistTaskTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the task; must be positive and unique among all task identifiers currently present in the checklist')]
        public int $id,
        #[Description('Text of the task; 1-100 characters after entities parsing')]
        public string $text,
        #[Description('Mode for parsing entities in the text. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the text, which can be specified instead of parse\_mode. Currently, only _bold_, _italic_, _underline_, _strikethrough_, _spoiler_, and _custom\_emoji_ entities are allowed.')]
        public ?array $textEntities = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputChecklistTask;
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
{"id":{"property":"id","tgPropName":"id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"text_entities":{"property":"textEntities","tgPropName":"text_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false}}
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
