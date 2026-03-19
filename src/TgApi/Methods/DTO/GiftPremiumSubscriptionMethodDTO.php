<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\MonthCountEnum;
use BAGArt\TelegramBot\TgApi\Methods\Enum\StarCountEnum;
use BAGArt\TelegramBot\TgApi\Methods\Enum\TextParseModeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Gifts a Telegram Premium subscription to the given user. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#giftpremiumsubscription')]
class GiftPremiumSubscriptionMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the target user who will receive a Telegram Premium subscription')]
        public int $userId,
        #[Description('Number of months the Telegram Premium subscription will be active for the user; must be one of 3, 6, or 12')]
        public MonthCountEnum $monthCount,
        #[Description('Number of Telegram Stars to pay for the Telegram Premium subscription; must be 1000 for 3 months, 1500 for 6 months, and 2500 for 12 months')]
        public StarCountEnum $starCount,
        #[Description('Text that will be shown along with the service message about the subscription; 0-128 characters')]
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
        return TgApiMethodsEnum::giftPremiumSubscription;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"month_count":{"property":"monthCount","tgPropName":"month_count","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\MonthCountEnum"],"tgTypes":[{"type":"int32","literal":3},{"type":"int32","literal":6},{"type":"int32","literal":12}],"nullable":false,"required":true},"star_count":{"property":"starCount","tgPropName":"star_count","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\StarCountEnum"],"tgTypes":[{"type":"int32","literal":1000},{"type":"int32","literal":1500},{"type":"int32","literal":2500}],"nullable":false,"required":true},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"text_parse_mode":{"property":"textParseMode","tgPropName":"text_parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\TextParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"text_entities":{"property":"textEntities","tgPropName":"text_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false}}
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
