<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\ActivePeriodEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\StoryTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Reposts a story on behalf of a business account from another business account. Both business accounts must be managed by the same bot, and the story on the source account must have been posted (or reposted) by the bot. Requires the _can\_manage\_stories_ business bot right for both business accounts. Returns [Story](https://core.telegram.org/bots/api#story) on success.')]
#[See('https://core.telegram.org/bots/api#repoststory')]
class RepostStoryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('Unique identifier of the chat which posted the story that should be reposted')]
        public int $fromChatId,
        #[Description('Unique identifier of the story that should be reposted')]
        public int $fromStoryId,
        #[Description('Period after which the story is moved to the archive, in seconds; must be one of `6 * 3600`, `12 * 3600`, `86400`, or `2 * 86400`')]
        public ActivePeriodEnum $activePeriod,
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
        return TgApiMethodsEnum::repostStory;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"from_chat_id":{"property":"fromChatId","tgPropName":"from_chat_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"from_story_id":{"property":"fromStoryId","tgPropName":"from_story_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"active_period":{"property":"activePeriod","tgPropName":"active_period","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ActivePeriodEnum"],"tgTypes":[{"type":"int32","literal":21600},{"type":"int32","literal":43200},{"type":"int32","literal":86400},{"type":"int32","literal":172800}],"nullable":false,"required":true},"post_to_chat_page":{"property":"postToChatPage","tgPropName":"post_to_chat_page","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
