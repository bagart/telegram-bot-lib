<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\ActionEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method when you need to tell the user that something is happening on the bot"s side. The status is set for 5 seconds or less (when a message arrives from your bot, Telegram clients clear its typing status). Returns _True_ on success.; ; > Example: The [ImageBot](https://t.me/imagebot) needs some time to process a request and upload the image. Instead of sending a text message along the lines of “Retrieving image, please wait…”, the bot may use [sendChatAction](https://core.telegram.org/bots/api#sendchataction) with _action_ = _upload\_photo_. The user will see a “sending photo” status for the bot.; ; We only recommend using this method when a response from the bot will take a **noticeable** amount of time to arrive.')]
#[See('https://core.telegram.org/bots/api#sendchataction')]
class SendChatActionMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target supergroup (in the format `@supergroupusername`). Channel chats and channel direct messages chats aren"t supported.')]
        public string $chatId,
        #[Description('Type of action to broadcast. Choose one, depending on what the user is about to receive: _typing_ for [text messages](https://core.telegram.org/bots/api#sendmessage), _upload\_photo_ for [photos](https://core.telegram.org/bots/api#sendphoto), _record\_video_ or _upload\_video_ for [videos](https://core.telegram.org/bots/api#sendvideo), _record\_voice_ or _upload\_voice_ for [voice notes](https://core.telegram.org/bots/api#sendvoice), _upload\_document_ for [general files](https://core.telegram.org/bots/api#senddocument), _choose\_sticker_ for [stickers](https://core.telegram.org/bots/api#sendsticker), _find\_location_ for [location data](https://core.telegram.org/bots/api#sendlocation), _record\_video\_note_ or _upload\_video\_note_ for [video notes](https://core.telegram.org/bots/api#sendvideonote).')]
        public ActionEnum $action,
        #[Description('Unique identifier of the business connection on behalf of which the action will be sent')]
        public ?string $businessConnectionId = null,
        #[Description('Unique identifier for the target message thread or topic of a forum; for supergroups and private chats of bots with forum topic mode enabled only')]
        public ?int $messageThreadId = null,
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
        return TgApiMethodsEnum::sendChatAction;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"action":{"property":"action","tgPropName":"action","types":["\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ActionEnum"],"tgTypes":[{"type":"str","literal":"typing"},{"type":"str","literal":"upload_photo"},{"type":"str","literal":"record_video"},{"type":"str","literal":"upload_video"},{"type":"str","literal":"record_voice"},{"type":"str","literal":"upload_voice"},{"type":"str","literal":"upload_document"},{"type":"str","literal":"choose_sticker"},{"type":"str","literal":"find_location"},{"type":"str","literal":"record_video_note"},{"type":"str","literal":"upload_video_note"}],"nullable":false,"required":true}}
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
