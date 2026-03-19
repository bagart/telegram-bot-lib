<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\TextParseModeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Sends a gift to the given user or channel chat. The gift can"t be converted to Telegram Stars by the receiver. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#sendgift')]
class SendGiftMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the gift; limited gifts can"t be sent to channel chats')]
        public string $giftId,
        #[Description('Required if _chat\_id_ is not specified. Unique identifier of the target user who will receive the gift.')]
        public ?int $userId = null,
        #[Description('Required if _user\_id_ is not specified. Unique identifier for the chat or username of the channel (in the format `@channelusername`) that will receive the gift.')]
        public ?string $chatId = null,
        #[Description('Pass _True_ to pay for the gift upgrade from the bot"s balance, thereby making the upgrade free for the receiver')]
        public ?bool $payForUpgrade = null,
        #[Description('Text that will be shown along with the gift; 0-128 characters')]
        public ?string $text = null,
        #[Description('Mode for parsing entities in the text. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details. Entities other than “bold”, “italic”, “underline”, “strikethrough”, “spoiler”, and “custom\_emoji” are ignored.')]
        public ?TextParseModeEnum $textParseMode = null,
        #[Description('An array of special entities that appear in the gift text. It can be specified instead of _text\_parse\_mode_. Entities other than “bold”, “italic”, “underline”, “strikethrough”, “spoiler”, and “custom\_emoji” are ignored.')]
        public ?array $textEntities = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::sendGift;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":true,"required":false},"gift_id":{"property":"giftId","tgPropName":"gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"pay_for_upgrade":{"property":"payForUpgrade","tgPropName":"pay_for_upgrade","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"text_parse_mode":{"property":"textParseMode","tgPropName":"text_parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\TextParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"text_entities":{"property":"textEntities","tgPropName":"text_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false}}
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
