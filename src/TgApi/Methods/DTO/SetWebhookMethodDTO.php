<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to specify a URL and receive incoming updates via an outgoing webhook. Whenever there is an update for the bot, we will send an HTTPS POST request to the specified URL, containing a JSON-serialized [Update](https://core.telegram.org/bots/api#update). In case of an unsuccessful request (a request with response [HTTP status code](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes) different from `2XY`), we will repeat the request and give up after a reasonable amount of attempts. Returns _True_ on success.; ; If you"d like to make sure that the webhook was set by you, you can specify secret data in the parameter _secret\_token_. If specified, the request will contain a header “X-Telegram-Bot-Api-Secret-Token” with the secret token as content.; ; > **Notes**; > ; > **1.** You will not be able to receive updates using [getUpdates](https://core.telegram.org/bots/api#getupdates) for as long as an outgoing webhook is set up.; > ; > **2.** To use a self-signed certificate, you need to upload your [public key certificate](https://core.telegram.org/bots/self-signed) using _certificate_ parameter. Please upload as InputFile, sending a String will not work.; > ; > **3.** Ports currently supported _for webhooks_: **443, 80, 88, 8443**.; > ; > If you"re having any trouble setting up webhooks, please check out this [amazing guide to webhooks](https://core.telegram.org/bots/webhooks).')]
#[See('https://core.telegram.org/bots/api#setwebhook')]
class SetWebhookMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('HTTPS URL to send updates to. Use an empty string to remove webhook integration')]
        public string $url,
        #[Description('Upload your public key certificate so that the root certificate in use can be checked. See our [self-signed guide](https://core.telegram.org/bots/self-signed) for details.')]
        public ?string $certificate = null,
        #[Description('The fixed IP address which will be used to send webhook requests instead of the IP address resolved through DNS')]
        public ?string $ipAddress = null,
        #[Description('The maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to _40_. Use lower values to limit the load on your bot"s server, and higher values to increase your bot"s throughput.')]
        public ?int $maxConnections = null,
        #[Description('An array of the update types you want your bot to receive. For example, specify `["message", "edited_channel_post", "callback_query"]` to only receive updates of these types. See [Update](https://core.telegram.org/bots/api#update) for a complete list of available update types. Specify an empty list to receive all update types except _chat\_member_, _message\_reaction_, and _message\_reaction\_count_ (default). If not specified, the previous setting will be used.; ; Please note that this parameter doesn"t affect updates created before the call to the setWebhook, so unwanted updates may be received for a short period of time.')]
        public ?array $allowedUpdates = null,
        #[Description('Pass _True_ to drop all pending updates')]
        public ?bool $dropPendingUpdates = null,
        #[Description('A secret token to be sent in a header “X-Telegram-Bot-Api-Secret-Token” in every webhook request, 1-256 characters. Only characters `A-Z`, `a-z`, `0-9`, `_` and `-` are allowed. The header is useful to ensure that the request comes from a webhook set by you.')]
        public ?string $secretToken = null,
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
        return TgApiMethodsEnum::setWebhook;
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
{"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"certificate":{"property":"certificate","tgPropName":"certificate","types":["string"],"tgTypes":[{"type":"input-file"}],"nullable":true,"required":false},"ip_address":{"property":"ipAddress","tgPropName":"ip_address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"max_connections":{"property":"maxConnections","tgPropName":"max_connections","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"allowed_updates":{"property":"allowedUpdates","tgPropName":"allowed_updates","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false},"drop_pending_updates":{"property":"dropPendingUpdates","tgPropName":"drop_pending_updates","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"secret_token":{"property":"secretToken","tgPropName":"secret_token","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
