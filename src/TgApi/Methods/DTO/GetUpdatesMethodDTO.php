<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to receive incoming updates using long polling ([wiki](https://en.wikipedia.org/wiki/Push_technology#Long_polling)). Returns an Array of [Update](https://core.telegram.org/bots/api#update) objects.; ; > **Notes**; > ; > **1.** This method will not work if an outgoing webhook is set up.; > ; > **2.** In order to avoid getting duplicate updates, recalculate _offset_ after each server response.')]
#[See('https://core.telegram.org/bots/api#getupdates')]
class GetUpdatesMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the first update to be returned. Must be greater by one than the highest among the identifiers of previously received updates. By default, updates starting with the earliest unconfirmed update are returned. An update is considered confirmed as soon as [getUpdates](https://core.telegram.org/bots/api#getupdates) is called with an _offset_ higher than its _update\_id_. The negative offset can be specified to retrieve updates starting from _\-offset_ update from the end of the updates queue. All previous updates will be forgotten.')]
        public ?int $offset = null,
        #[Description('Limits the number of updates to be retrieved. Values between 1-100 are accepted. Defaults to 100.')]
        public ?int $limit = null,
        #[Description('Timeout in seconds for long polling. Defaults to 0, i.e. usual short polling. Should be positive, short polling should be used for testing purposes only.')]
        public ?int $timeout = null,
        #[Description('An array of the update types you want your bot to receive. For example, specify `["message", "edited_channel_post", "callback_query"]` to only receive updates of these types. See [Update](https://core.telegram.org/bots/api#update) for a complete list of available update types. Specify an empty list to receive all update types except _chat\_member_, _message\_reaction_, and _message\_reaction\_count_ (default). If not specified, the previous setting will be used.; ; Please note that this parameter doesn"t affect updates created before the call to getUpdates, so unwanted updates may be received for a short period of time.')]
        public ?array $allowedUpdates = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            [
                UpdateTypeDTO::class,
            ],
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getUpdates;
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
{"offset":{"property":"offset","tgPropName":"offset","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"limit":{"property":"limit","tgPropName":"limit","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"timeout":{"property":"timeout","tgPropName":"timeout","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"allowed_updates":{"property":"allowedUpdates","tgPropName":"allowed_updates","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false}}
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
