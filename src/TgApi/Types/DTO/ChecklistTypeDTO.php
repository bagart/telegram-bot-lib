<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a checklist.')]
#[See('https://core.telegram.org/bots/api#checklist')]
class ChecklistTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Title of the checklist')]
        public string $title,
        #[Description('List of tasks in the checklist')]
        public array $tasks,
        #[Description('Special entities that appear in the checklist title')]
        public ?array $titleEntities = null,
        #[Description('_True_, if users other than the creator of the list can add tasks to the list')]
        public ?bool $othersCanAddTasks = true,
        #[Description('_True_, if users other than the creator of the list can mark tasks as done or not done')]
        public ?bool $othersCanMarkTasksAsDone = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Checklist;
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
{"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title_entities":{"property":"titleEntities","tgPropName":"title_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"tasks":{"property":"tasks","tgPropName":"tasks","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChecklistTaskTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"ChecklistTask"}}],"nullable":false,"required":true},"others_can_add_tasks":{"property":"othersCanAddTasks","tgPropName":"others_can_add_tasks","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"others_can_mark_tasks_as_done":{"property":"othersCanMarkTasksAsDone","tgPropName":"others_can_mark_tasks_as_done","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
