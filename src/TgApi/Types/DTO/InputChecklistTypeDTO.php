<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a checklist to create.')]
#[See('https://core.telegram.org/bots/api#inputchecklist')]
class InputChecklistTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Title of the checklist; 1-255 characters after entities parsing')]
        public string $title,
        #[Description('List of 1-30 tasks in the checklist')]
        public array $tasks,
        #[Description('Mode for parsing entities in the title. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the title, which can be specified instead of parse\_mode. Currently, only _bold_, _italic_, _underline_, _strikethrough_, _spoiler_, and _custom\_emoji_ entities are allowed.')]
        public ?array $titleEntities = null,
        #[Description('Pass _True_ if other users can add tasks to the checklist')]
        public ?bool $othersCanAddTasks = null,
        #[Description('Pass _True_ if other users can mark tasks as done or not done in the checklist')]
        public ?bool $othersCanMarkTasksAsDone = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputChecklist;
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
{"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"title_entities":{"property":"titleEntities","tgPropName":"title_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"tasks":{"property":"tasks","tgPropName":"tasks","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputChecklistTaskTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"InputChecklistTask"}}],"nullable":false,"required":true},"others_can_add_tasks":{"property":"othersCanAddTasks","tgPropName":"others_can_add_tasks","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"others_can_mark_tasks_as_done":{"property":"othersCanMarkTasksAsDone","tgPropName":"others_can_mark_tasks_as_done","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
