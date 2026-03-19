<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents one special entity in a text message. For example, hashtags, usernames, URLs, etc.')]
#[See('https://core.telegram.org/bots/api#messageentity')]
class MessageEntityTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Type of the entity. Currently, can be “mention” (`@username`), “hashtag” (`#hashtag` or `#hashtag@chatusername`), “cashtag” (`$USD` or `$USD@chatusername`), “bot\_command” (`/start@jobs_bot`), “url” (`https://telegram.org`), “email” (`do-not-reply@telegram.org`), “phone\_number” (`+1-212-555-0123`), “bold” (**bold text**), “italic” (_italic text_), “underline” (underlined text), “strikethrough” (strikethrough text), “spoiler” (spoiler message), “blockquote” (block quotation), “expandable\_blockquote” (collapsed-by-default block quotation), “code” (monowidth string), “pre” (monowidth block), “text\_link” (for clickable text URLs), “text\_mention” (for users [without usernames](https://telegram.org/blog/edit#new-mentions)), “custom\_emoji” (for inline custom emoji stickers), or “date\_time” (for formatted date and time)')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\MessageEntityPropTypeEnum $type,
        #[Description('Offset in [UTF-16 code units](https://core.telegram.org/api/entities#entity-length) to the start of the entity')]
        public int $offset,
        #[Description('Length of the entity in [UTF-16 code units](https://core.telegram.org/api/entities#entity-length)')]
        public int $length,
        #[Description('For “text\_link” only, URL that will be opened after user taps on the text')]
        public ?string $url = null,
        #[Description('For “text\_mention” only, the mentioned user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user = null,
        #[Description('For “pre” only, the programming language of the entity text')]
        public ?string $language = null,
        #[Description('For “custom\_emoji” only, unique identifier of the custom emoji. Use [getCustomEmojiStickers](https://core.telegram.org/bots/api#getcustomemojistickers) to get full information about the sticker')]
        public ?string $customEmojiId = null,
        #[Description('For “date\_time” only, the Unix time associated with the entity')]
        public ?int $unixTime = null,
        #[Description('For “date\_time” only, the string that defines the formatting of the date and time. See [date-time entity formatting](https://core.telegram.org/bots/api#date-time-entity-formatting) for more details.')]
        public ?string $dateTimeFormat = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::MessageEntity;
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
{"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\MessageEntityPropTypeEnum"],"tgTypes":[{"type":"str","literal":"mention"},{"type":"str","literal":"hashtag"},{"type":"str","literal":"cashtag"},{"type":"str","literal":"bot_command"},{"type":"str","literal":"url"},{"type":"str","literal":"email"},{"type":"str","literal":"phone_number"},{"type":"str","literal":"bold"},{"type":"str","literal":"italic"},{"type":"str","literal":"underline"},{"type":"str","literal":"strikethrough"},{"type":"str","literal":"spoiler"},{"type":"str","literal":"blockquote"},{"type":"str","literal":"expandable_blockquote"},{"type":"str","literal":"code"},{"type":"str","literal":"pre"},{"type":"str","literal":"text_link"},{"type":"str","literal":"text_mention"},{"type":"str","literal":"custom_emoji"},{"type":"str","literal":"date_time"}],"nullable":false,"required":true},"offset":{"property":"offset","tgPropName":"offset","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"length":{"property":"length","tgPropName":"length","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"language":{"property":"language","tgPropName":"language","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"custom_emoji_id":{"property":"customEmojiId","tgPropName":"custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"unix_time":{"property":"unixTime","tgPropName":"unix_time","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"date_time_format":{"property":"dateTimeFormat","tgPropName":"date_time_format","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
