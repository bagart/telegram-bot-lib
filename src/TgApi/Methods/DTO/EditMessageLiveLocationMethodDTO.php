<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to edit live location messages. A location can be edited until its _live\_period_ expires or editing is explicitly disabled by a call to [stopMessageLiveLocation](https://core.telegram.org/bots/api#stopmessagelivelocation). On success, if the edited message is not an inline message, the edited [Message](https://core.telegram.org/bots/api#message) is returned, otherwise _True_ is returned.')]
#[See('https://core.telegram.org/bots/api#editmessagelivelocation')]
class EditMessageLiveLocationMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Latitude of new location')]
        public string $latitude,
        #[Description('Longitude of new location')]
        public string $longitude,
        #[Description('Unique identifier of the business connection on behalf of which the message to be edited was sent')]
        public ?string $businessConnectionId = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public ?string $chatId = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Identifier of the message to edit')]
        public ?int $messageId = null,
        #[Description('Required if _chat\_id_ and _message\_id_ are not specified. Identifier of the inline message')]
        public ?string $inlineMessageId = null,
        #[Description('New period in seconds during which the location can be updated, starting from the message send date. If 0x7FFFFFFF is specified, then the location can be updated forever. Otherwise, the new value must not exceed the current _live\_period_ by more than a day, and the live location expiration date must remain within the next 90 days. If not specified, then _live\_period_ remains unchanged')]
        public ?int $livePeriod = null,
        #[Description('The radius of uncertainty for the location, measured in meters; 0-1500')]
        public ?string $horizontalAccuracy = null,
        #[Description('Direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.')]
        public ?int $heading = null,
        #[Description('The maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.')]
        public ?int $proximityAlertRadius = null,
        #[Description('An object for a new [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards).')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::editMessageLiveLocation;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":true,"required":false},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"inline_message_id":{"property":"inlineMessageId","tgPropName":"inline_message_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"live_period":{"property":"livePeriod","tgPropName":"live_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"horizontal_accuracy":{"property":"horizontalAccuracy","tgPropName":"horizontal_accuracy","types":["string"],"tgTypes":[{"type":"float"}],"nullable":true,"required":false},"heading":{"property":"heading","tgPropName":"heading","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"proximity_alert_radius":{"property":"proximityAlertRadius","tgPropName":"proximity_alert_radius","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false}}
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
