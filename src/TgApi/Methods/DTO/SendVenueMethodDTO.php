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
#[Description('Use this method to send information about a venue. On success, the sent [Message](https://core.telegram.org/bots/api#message) is returned.')]
#[See('https://core.telegram.org/bots/api#sendvenue')]
class SendVenueMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('Latitude of the venue')]
        public string $latitude,
        #[Description('Longitude of the venue')]
        public string $longitude,
        #[Description('Name of the venue')]
        public string $title,
        #[Description('Address of the venue')]
        public string $address,
        #[Description('Unique identifier of the business connection on behalf of which the message will be sent')]
        public ?string $businessConnectionId = null,
        #[Description('Unique identifier for the target message thread (topic) of a forum; for forum supergroups and private chats of bots with forum topic mode enabled only')]
        public ?int $messageThreadId = null,
        #[Description('Identifier of the direct messages topic to which the message will be sent; required if the message is sent to a direct messages chat')]
        public ?int $directMessagesTopicId = null,
        #[Description('Foursquare identifier of the venue')]
        public ?string $foursquareId = null,
        #[Description('Foursquare type of the venue, if known. (For example, “arts\_entertainment/default”, “arts\_entertainment/aquarium” or “food/icecream”.)')]
        public ?string $foursquareType = null,
        #[Description('Google Places identifier of the venue')]
        public ?string $googlePlaceId = null,
        #[Description('Google Places type of the venue. (See [supported types](https://developers.google.com/places/web-service/supported_types).)')]
        public ?string $googlePlaceType = null,
        #[Description('Sends the message [silently](https://telegram.org/blog/channels-2-0#silent-messages). Users will receive a notification with no sound.')]
        public ?bool $disableNotification = null,
        #[Description('Protects the contents of the sent message from forwarding and saving')]
        public ?bool $protectContent = null,
        #[Description('Pass _True_ to allow up to 1000 messages per second, ignoring [broadcasting limits](https://core.telegram.org/bots/faq#how-can-i-message-all-of-my-bot-39s-subscribers-at-once) for a fee of 0.1 Telegram Stars per message. The relevant Stars will be withdrawn from the bot"s balance')]
        public ?bool $allowPaidBroadcast = null,
        #[Description('Unique identifier of the message effect to be added to the message; for private chats only')]
        public ?string $messageEffectId = null,
        #[Description('An object containing the parameters of the suggested post to send; for direct messages chats only. If the message is sent as a reply to another suggested post, then that suggested post is automatically declined.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostParametersTypeDTO $suggestedPostParameters = null,
        #[Description('Description of the message to reply to')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyParametersTypeDTO $replyParameters = null,
        #[Description('Additional interface options. An object for an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards), [custom reply keyboard](https://core.telegram.org/bots/features#keyboards), instructions to remove a reply keyboard or to force a reply from the user')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ForceReplyTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardMarkupTypeDTO|\BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardRemoveTypeDTO|null $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::sendVenue;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"direct_messages_topic_id":{"property":"directMessagesTopicId","tgPropName":"direct_messages_topic_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"address":{"property":"address","tgPropName":"address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"foursquare_id":{"property":"foursquareId","tgPropName":"foursquare_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"foursquare_type":{"property":"foursquareType","tgPropName":"foursquare_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_id":{"property":"googlePlaceId","tgPropName":"google_place_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_type":{"property":"googlePlaceType","tgPropName":"google_place_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"disable_notification":{"property":"disableNotification","tgPropName":"disable_notification","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_paid_broadcast":{"property":"allowPaidBroadcast","tgPropName":"allow_paid_broadcast","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"message_effect_id":{"property":"messageEffectId","tgPropName":"message_effect_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"suggested_post_parameters":{"property":"suggestedPostParameters","tgPropName":"suggested_post_parameters","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostParametersTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostParameters"}],"nullable":true,"required":false},"reply_parameters":{"property":"replyParameters","tgPropName":"reply_parameters","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyParametersTypeDTO"],"tgTypes":[{"type":"api-type","name":"ReplyParameters"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForceReplyTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyKeyboardMarkupTypeDTO","\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyKeyboardRemoveTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"},{"type":"api-type","name":"ReplyKeyboardMarkup"},{"type":"api-type","name":"ReplyKeyboardRemove"},{"type":"api-type","name":"ForceReply"}],"nullable":true,"required":false}}
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
