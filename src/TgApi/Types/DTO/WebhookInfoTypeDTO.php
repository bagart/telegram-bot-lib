<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the current status of a webhook.')]
#[See('https://core.telegram.org/bots/api#webhookinfo')]
class WebhookInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Webhook URL, may be empty if webhook is not set up')]
        public string $url,
        #[Description('_True_, if a custom certificate was provided for webhook certificate checks')]
        public bool $hasCustomCertificate,
        #[Description('Number of updates awaiting delivery')]
        public int $pendingUpdateCount,
        #[Description('Currently used webhook IP address')]
        public ?string $ipAddress = null,
        #[Description('Unix time for the most recent error that happened when trying to deliver an update via webhook')]
        public ?int $lastErrorDate = null,
        #[Description('Error message in human-readable format for the most recent error that happened when trying to deliver an update via webhook')]
        public ?string $lastErrorMessage = null,
        #[Description('Unix time of the most recent error that happened when trying to synchronize available updates with Telegram datacenters')]
        public ?int $lastSynchronizationErrorDate = null,
        #[Description('The maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery')]
        public ?int $maxConnections = null,
        #[Description('A list of update types the bot is subscribed to. Defaults to all update types except _chat\_member_')]
        public ?array $allowedUpdates = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::WebhookInfo;
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
{"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"has_custom_certificate":{"property":"hasCustomCertificate","tgPropName":"has_custom_certificate","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"pending_update_count":{"property":"pendingUpdateCount","tgPropName":"pending_update_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"ip_address":{"property":"ipAddress","tgPropName":"ip_address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"last_error_date":{"property":"lastErrorDate","tgPropName":"last_error_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"last_error_message":{"property":"lastErrorMessage","tgPropName":"last_error_message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"last_synchronization_error_date":{"property":"lastSynchronizationErrorDate","tgPropName":"last_synchronization_error_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"max_connections":{"property":"maxConnections","tgPropName":"max_connections","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"allowed_updates":{"property":"allowedUpdates","tgPropName":"allowed_updates","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false}}
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
