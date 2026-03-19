<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\SentWebAppMessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to set the result of an interaction with a [Web App](https://core.telegram.org/bots/webapps) and send a corresponding message on behalf of the user to the chat from which the query originated. On success, a [SentWebAppMessage](https://core.telegram.org/bots/api#sentwebappmessage) object is returned.')]
#[See('https://core.telegram.org/bots/api#answerwebappquery')]
class AnswerWebAppQueryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the query to be answered')]
        public string $webAppQueryId,
        #[Description('An object describing the message to be sent')]
        public InlineQueryResultTypeDTO $result,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            SentWebAppMessageTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::answerWebAppQuery;
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
{"web_app_query_id":{"property":"webAppQueryId","tgPropName":"web_app_query_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"result":{"property":"result","tgPropName":"result","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineQueryResultTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineQueryResult"}],"nullable":false,"required":true}}
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
