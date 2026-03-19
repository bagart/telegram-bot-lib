<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\StoryTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Edits a story previously posted by the bot on behalf of a managed business account. Requires the _can\_manage\_stories_ business bot right. Returns [Story](https://core.telegram.org/bots/api#story) on success.')]
#[See('https://core.telegram.org/bots/api#editstory')]
class EditStoryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('Unique identifier of the story to edit')]
        public int $storyId,
        #[Description('Content of the story')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\InputStoryContentTypeDTO $content,
        #[Description('Caption of the story, 0-2048 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the story caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\ParseModeEnum $parseMode = null,
        #[Description('An array of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('An array of clickable areas to be shown on the story')]
        public ?array $areas = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            StoryTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::editStory;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"story_id":{"property":"storyId","tgPropName":"story_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"content":{"property":"content","tgPropName":"content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputStoryContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputStoryContent"}],"nullable":false,"required":true},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"areas":{"property":"areas","tgPropName":"areas","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryAreaTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"StoryArea"}}],"nullable":true,"required":false}}
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
