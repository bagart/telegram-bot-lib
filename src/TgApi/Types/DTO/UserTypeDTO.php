<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a Telegram user or bot.')]
#[See('https://core.telegram.org/bots/api#user')]
class UserTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this user or bot.')]
        public string $id,
        #[Description('_True_, if this user is a bot')]
        public bool $isBot,
        #[Description('User"s or bot"s first name')]
        public string $firstName,
        #[Description('User"s or bot"s last name')]
        public ?string $lastName = null,
        #[Description('User"s or bot"s username')]
        public ?string $username = null,
        #[Description('[IETF language tag](https://en.wikipedia.org/wiki/IETF_language_tag) of the user"s language')]
        public ?string $languageCode = null,
        #[Description('_True_, if this user is a Telegram Premium user')]
        public ?bool $isPremium = true,
        #[Description('_True_, if this user added the bot to the attachment menu')]
        public ?bool $addedToAttachmentMenu = true,
        #[Description('_True_, if the bot can be invited to groups. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $canJoinGroups = null,
        #[Description('_True_, if [privacy mode](https://core.telegram.org/bots/features#privacy-mode) is disabled for the bot. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $canReadAllGroupMessages = null,
        #[Description('_True_, if the bot supports inline queries. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $supportsInlineQueries = null,
        #[Description('_True_, if the bot can be connected to a Telegram Business account to receive its messages. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $canConnectToBusiness = null,
        #[Description('_True_, if the bot has a main Web App. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $hasMainWebApp = null,
        #[Description('_True_, if the bot has forum topic mode enabled in private chats. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $hasTopicsEnabled = null,
        #[Description('_True_, if the bot allows users to create and delete topics in private chats. Returned only in [getMe](https://core.telegram.org/bots/api#getme).')]
        public ?bool $allowsUsersToCreateTopics = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::User;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"is_bot":{"property":"isBot","tgPropName":"is_bot","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"username":{"property":"username","tgPropName":"username","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"language_code":{"property":"languageCode","tgPropName":"language_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"is_premium":{"property":"isPremium","tgPropName":"is_premium","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"added_to_attachment_menu":{"property":"addedToAttachmentMenu","tgPropName":"added_to_attachment_menu","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_join_groups":{"property":"canJoinGroups","tgPropName":"can_join_groups","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_read_all_group_messages":{"property":"canReadAllGroupMessages","tgPropName":"can_read_all_group_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"supports_inline_queries":{"property":"supportsInlineQueries","tgPropName":"supports_inline_queries","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_connect_to_business":{"property":"canConnectToBusiness","tgPropName":"can_connect_to_business","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"has_main_web_app":{"property":"hasMainWebApp","tgPropName":"has_main_web_app","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"has_topics_enabled":{"property":"hasTopicsEnabled","tgPropName":"has_topics_enabled","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allows_users_to_create_topics":{"property":"allowsUsersToCreateTopics","tgPropName":"allows_users_to_create_topics","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
