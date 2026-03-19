<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents an answer of a user in a non-anonymous poll.')]
#[See('https://core.telegram.org/bots/api#pollanswer')]
class PollAnswerTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique poll identifier')]
        public string $pollId,
        #[Description('0-based identifiers of chosen answer options. May be empty if the vote was retracted.')]
        public array $optionIds,
        #[Description('The chat that changed the answer to the poll, if the voter is anonymous')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $voterChat = null,
        #[Description('The user that changed the answer to the poll, if the voter isn"t anonymous')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PollAnswer;
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
{"poll_id":{"property":"pollId","tgPropName":"poll_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"voter_chat":{"property":"voterChat","tgPropName":"voter_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"option_ids":{"property":"optionIds","tgPropName":"option_ids","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":false,"required":true}}
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
