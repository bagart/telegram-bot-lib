<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to send answers to an inline query. On success, _True_ is returned.; ; No more than **50** results per query are allowed.')]
#[See('https://core.telegram.org/bots/api#answerinlinequery')]
class AnswerInlineQueryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the answered query')]
        public string $inlineQueryId,
        #[Description('An array of results for the inline query')]
        public array $results,
        #[Description('The maximum amount of time in seconds that the result of the inline query may be cached on the server. Defaults to 300.')]
        public ?int $cacheTime = null,
        #[Description('Pass _True_ if results may be cached on the server side only for the user that sent the query. By default, results may be returned to any user who sends the same query.')]
        public ?bool $isPersonal = null,
        #[Description('Pass the offset that a client should send in the next query with the same text to receive more results. Pass an empty string if there are no more results or if you don"t support pagination. Offset length can"t exceed 64 bytes.')]
        public ?string $nextOffset = null,
        #[Description('An object describing a button to be shown above inline query results')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultsButtonTypeDTO $button = null,
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
        return TgApiMethodsEnum::answerInlineQuery;
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
{"inline_query_id":{"property":"inlineQueryId","tgPropName":"inline_query_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"results":{"property":"results","tgPropName":"results","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineQueryResultTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"InlineQueryResult"}}],"nullable":false,"required":true},"cache_time":{"property":"cacheTime","tgPropName":"cache_time","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_personal":{"property":"isPersonal","tgPropName":"is_personal","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"next_offset":{"property":"nextOffset","tgPropName":"next_offset","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"button":{"property":"button","tgPropName":"button","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineQueryResultsButtonTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineQueryResultsButton"}],"nullable":true,"required":false}}
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
