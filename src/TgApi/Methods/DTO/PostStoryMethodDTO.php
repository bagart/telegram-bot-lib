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
#[Description('Posts a story on behalf of a managed business account. Requires the _can\_manage\_stories_ business bot right. Returns [Story](https://core.telegram.org/bots/api#story) on success.')]
#[See('https://core.telegram.org/bots/api#poststory')]
class PostStoryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('Content of the story')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\InputStoryContentTypeDTO $content,
        #[Description('Period after which the story is moved to the archive, in seconds; must be one of `6 * 3600`, `12 * 3600`, `86400`, or `2 * 86400`')]
        public \BAGArt\TelegramBot\TgApi\Methods\Enum\ActivePeriodEnum $activePeriod,
        #[Description('Caption of the story, 0-2048 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the story caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Methods\Enum\ParseModeEnum $parseMode = null,
        #[Description('An array of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('An array of clickable areas to be shown on the story')]
        public ?array $areas = null,
        #[Description('Pass _True_ to keep the story accessible after it expires')]
        public ?bool $postToChatPage = null,
        #[Description('Pass _True_ if the content of the story must be protected from forwarding and screenshotting')]
        public ?bool $protectContent = null,
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
        return TgApiMethodsEnum::postStory;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"content":{"property":"content","tgPropName":"content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputStoryContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputStoryContent"}],"nullable":false,"required":true},"active_period":{"property":"activePeriod","tgPropName":"active_period","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ActivePeriodEnum"],"tgTypes":[{"type":"int32","literal":21600},{"type":"int32","literal":43200},{"type":"int32","literal":86400},{"type":"int32","literal":172800}],"nullable":false,"required":true},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"areas":{"property":"areas","tgPropName":"areas","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryAreaTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"StoryArea"}}],"nullable":true,"required":false},"post_to_chat_page":{"property":"postToChatPage","tgPropName":"post_to_chat_page","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
