<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about a message that is being replied to, which may come from another chat or forum topic.')]
#[See('https://core.telegram.org/bots/api#externalreplyinfo')]
class ExternalReplyInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Origin of the message replied to by the given message')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginTypeDTO $origin,
        #[Description('Chat the original message belongs to. Available only if the chat is a supergroup or a channel.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $chat = null,
        #[Description('Unique message identifier inside the original chat. Available only if the original chat is a supergroup or a channel.')]
        public ?int $messageId = null,
        #[Description('Options used for link preview generation for the original message, if it is a text message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\LinkPreviewOptionsTypeDTO $linkPreviewOptions = null,
        #[Description('Message is an animation, information about the animation')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\AnimationTypeDTO $animation = null,
        #[Description('Message is an audio file, information about the file')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\AudioTypeDTO $audio = null,
        #[Description('Message is a general file, information about the file')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\DocumentTypeDTO $document = null,
        #[Description('Message contains paid media; information about the paid media')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaInfoTypeDTO $paidMedia = null,
        #[Description('Message is a photo, available sizes of the photo')]
        public ?array $photo = null,
        #[Description('Message is a sticker, information about the sticker')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\StickerTypeDTO $sticker = null,
        #[Description('Message is a forwarded story')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\StoryTypeDTO $story = null,
        #[Description('Message is a video, information about the video')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\VideoTypeDTO $video = null,
        #[Description('Message is a [video note](https://telegram.org/blog/video-messages-and-telescope), information about the video message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\VideoNoteTypeDTO $videoNote = null,
        #[Description('Message is a voice message, information about the file')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\VoiceTypeDTO $voice = null,
        #[Description('_True_, if the message media is covered by a spoiler animation')]
        public ?bool $hasMediaSpoiler = true,
        #[Description('Message is a checklist')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChecklistTypeDTO $checklist = null,
        #[Description('Message is a shared contact, information about the contact')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ContactTypeDTO $contact = null,
        #[Description('Message is a dice with random value')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\DiceTypeDTO $dice = null,
        #[Description('Message is a game, information about the game. [More about games »](https://core.telegram.org/bots/api#games)')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\GameTypeDTO $game = null,
        #[Description('Message is a scheduled giveaway, information about the giveaway')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayTypeDTO $giveaway = null,
        #[Description('A giveaway with public winners was completed')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayWinnersTypeDTO $giveawayWinners = null,
        #[Description('Message is an invoice for a [payment](https://core.telegram.org/bots/api#payments), information about the invoice. [More about payments »](https://core.telegram.org/bots/api#payments)')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InvoiceTypeDTO $invoice = null,
        #[Description('Message is a shared location, information about the location')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\LocationTypeDTO $location = null,
        #[Description('Message is a native poll, information about the poll')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PollTypeDTO $poll = null,
        #[Description('Message is a venue, information about the venue')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\VenueTypeDTO $venue = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ExternalReplyInfo;
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
{"origin":{"property":"origin","tgPropName":"origin","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageOriginTypeDTO"],"tgTypes":[{"type":"api-type","name":"MessageOrigin"}],"nullable":false,"required":true},"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"link_preview_options":{"property":"linkPreviewOptions","tgPropName":"link_preview_options","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LinkPreviewOptionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"LinkPreviewOptions"}],"nullable":true,"required":false},"animation":{"property":"animation","tgPropName":"animation","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AnimationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Animation"}],"nullable":true,"required":false},"audio":{"property":"audio","tgPropName":"audio","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AudioTypeDTO"],"tgTypes":[{"type":"api-type","name":"Audio"}],"nullable":true,"required":false},"document":{"property":"document","tgPropName":"document","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DocumentTypeDTO"],"tgTypes":[{"type":"api-type","name":"Document"}],"nullable":true,"required":false},"paid_media":{"property":"paidMedia","tgPropName":"paid_media","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PaidMediaInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"PaidMediaInfo"}],"nullable":true,"required":false},"photo":{"property":"photo","tgPropName":"photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":true,"required":false},"sticker":{"property":"sticker","tgPropName":"sticker","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StickerTypeDTO"],"tgTypes":[{"type":"api-type","name":"Sticker"}],"nullable":true,"required":false},"story":{"property":"story","tgPropName":"story","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryTypeDTO"],"tgTypes":[{"type":"api-type","name":"Story"}],"nullable":true,"required":false},"video":{"property":"video","tgPropName":"video","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoTypeDTO"],"tgTypes":[{"type":"api-type","name":"Video"}],"nullable":true,"required":false},"video_note":{"property":"videoNote","tgPropName":"video_note","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoNoteTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoNote"}],"nullable":true,"required":false},"voice":{"property":"voice","tgPropName":"voice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VoiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Voice"}],"nullable":true,"required":false},"has_media_spoiler":{"property":"hasMediaSpoiler","tgPropName":"has_media_spoiler","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"checklist":{"property":"checklist","tgPropName":"checklist","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChecklistTypeDTO"],"tgTypes":[{"type":"api-type","name":"Checklist"}],"nullable":true,"required":false},"contact":{"property":"contact","tgPropName":"contact","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ContactTypeDTO"],"tgTypes":[{"type":"api-type","name":"Contact"}],"nullable":true,"required":false},"dice":{"property":"dice","tgPropName":"dice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Dice"}],"nullable":true,"required":false},"game":{"property":"game","tgPropName":"game","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GameTypeDTO"],"tgTypes":[{"type":"api-type","name":"Game"}],"nullable":true,"required":false},"giveaway":{"property":"giveaway","tgPropName":"giveaway","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayTypeDTO"],"tgTypes":[{"type":"api-type","name":"Giveaway"}],"nullable":true,"required":false},"giveaway_winners":{"property":"giveawayWinners","tgPropName":"giveaway_winners","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayWinnersTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiveawayWinners"}],"nullable":true,"required":false},"invoice":{"property":"invoice","tgPropName":"invoice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InvoiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Invoice"}],"nullable":true,"required":false},"location":{"property":"location","tgPropName":"location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Location"}],"nullable":true,"required":false},"poll":{"property":"poll","tgPropName":"poll","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PollTypeDTO"],"tgTypes":[{"type":"api-type","name":"Poll"}],"nullable":true,"required":false},"venue":{"property":"venue","tgPropName":"venue","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VenueTypeDTO"],"tgTypes":[{"type":"api-type","name":"Venue"}],"nullable":true,"required":false}}
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
