<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about the quoted part of a message that is replied to by the given message.')]
#[See('https://core.telegram.org/bots/api#textquote')]
class TextQuoteTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Text of the quoted part of a message that is replied to by the given message')]
        public string $text,
        #[Description('Approximate quote position in the original message in UTF-16 code units as specified by the sender')]
        public int $position,
        #[Description('Special entities that appear in the quote. Currently, only _bold_, _italic_, _underline_, _strikethrough_, _spoiler_, and _custom\_emoji_ entities are kept in quotes.')]
        public ?array $entities = null,
        #[Description('_True_, if the quote was chosen manually by the message sender. Otherwise, the quote was added automatically by the server.')]
        public ?bool $isManual = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::TextQuote;
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
{"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"entities":{"property":"entities","tgPropName":"entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"position":{"property":"position","tgPropName":"position","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_manual":{"property":"isManual","tgPropName":"is_manual","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
