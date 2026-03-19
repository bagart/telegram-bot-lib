<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a contact with a phone number. By default, this contact will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the contact.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultcontact')]
class InlineQueryResultContactTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 Bytes')]
        public string $id,
        #[Description('Contact"s phone number')]
        public string $phoneNumber,
        #[Description('Contact"s first name')]
        public string $firstName,
        #[Description('Type of the result, must be _contact_')]
        public string $type = 'contact',
        #[Description('Contact"s last name')]
        public ?string $lastName = null,
        #[Description('Additional data about the contact in the form of a [vCard](https://en.wikipedia.org/wiki/VCard), 0-2048 bytes')]
        public ?string $vcard = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the contact')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InputMessageContentTypeDTO $inputMessageContent = null,
        #[Description('Url of the thumbnail for the result')]
        public ?string $thumbnailUrl = null,
        #[Description('Thumbnail width')]
        public ?int $thumbnailWidth = null,
        #[Description('Thumbnail height')]
        public ?int $thumbnailHeight = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultContact;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"contact"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"phone_number":{"property":"phoneNumber","tgPropName":"phone_number","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"vcard":{"property":"vcard","tgPropName":"vcard","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_width":{"property":"thumbnailWidth","tgPropName":"thumbnail_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_height":{"property":"thumbnailHeight","tgPropName":"thumbnail_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
