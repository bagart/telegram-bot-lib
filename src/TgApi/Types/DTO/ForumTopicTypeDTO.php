<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a forum topic.')]
#[See('https://core.telegram.org/bots/api#forumtopic')]
class ForumTopicTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the forum topic')]
        public int $messageThreadId,
        #[Description('Name of the topic')]
        public string $name,
        #[Description('Color of the topic icon in RGB format')]
        public int $iconColor,
        #[Description('Unique identifier of the custom emoji shown as the topic icon')]
        public ?string $iconCustomEmojiId = null,
        #[Description('_True_, if the name of the topic wasn"t specified explicitly by its creator and likely needs to be changed by the bot')]
        public ?bool $isNameImplicit = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ForumTopic;
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
{"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"icon_color":{"property":"iconColor","tgPropName":"icon_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"icon_custom_emoji_id":{"property":"iconCustomEmojiId","tgPropName":"icon_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"is_name_implicit":{"property":"isNameImplicit","tgPropName":"is_name_implicit","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
