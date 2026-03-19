<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a text message to be sent as the result of an inline query.')]
#[See('https://core.telegram.org/bots/api#inputtextmessagecontent')]
class InputTextMessageContentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Text of the message to be sent, 1-4096 characters')]
        public string $messageText,
        #[Description('Mode for parsing entities in the message text. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in message text, which can be specified instead of _parse\_mode_')]
        public ?array $entities = null,
        #[Description('Link preview generation options for the message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\LinkPreviewOptionsTypeDTO $linkPreviewOptions = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputTextMessageContent;
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
{"message_text":{"property":"messageText","tgPropName":"message_text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"entities":{"property":"entities","tgPropName":"entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"link_preview_options":{"property":"linkPreviewOptions","tgPropName":"link_preview_options","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LinkPreviewOptionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"LinkPreviewOptions"}],"nullable":true,"required":false}}
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
