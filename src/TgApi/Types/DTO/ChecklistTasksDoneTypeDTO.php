<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a service message about checklist tasks marked as done or not done.')]
#[See('https://core.telegram.org/bots/api#checklisttasksdone')]
class ChecklistTasksDoneTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Message containing the checklist whose tasks were marked as done or not done. Note that the [Message](https://core.telegram.org/bots/api#message) object in this field will not contain the _reply\_to\_message_ field even if it itself is a reply.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $checklistMessage = null,
        #[Description('Identifiers of the tasks that were marked as done')]
        public ?array $markedAsDoneTaskIds = null,
        #[Description('Identifiers of the tasks that were marked as not done')]
        public ?array $markedAsNotDoneTaskIds = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChecklistTasksDone;
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
{"checklist_message":{"property":"checklistMessage","tgPropName":"checklist_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"marked_as_done_task_ids":{"property":"markedAsDoneTaskIds","tgPropName":"marked_as_done_task_ids","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":true,"required":false},"marked_as_not_done_task_ids":{"property":"markedAsNotDoneTaskIds","tgPropName":"marked_as_not_done_task_ids","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":true,"required":false}}
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
