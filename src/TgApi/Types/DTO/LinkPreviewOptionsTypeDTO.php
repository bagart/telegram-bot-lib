<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the options used for link preview generation.')]
#[See('https://core.telegram.org/bots/api#linkpreviewoptions')]
class LinkPreviewOptionsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('_True_, if the link preview is disabled')]
        public ?bool $isDisabled = null,
        #[Description('URL to use for the link preview. If empty, then the first URL found in the message text will be used')]
        public ?string $url = null,
        #[Description('_True_, if the media in the link preview is supposed to be shrunk; ignored if the URL isn"t explicitly specified or media size change isn"t supported for the preview')]
        public ?bool $preferSmallMedia = null,
        #[Description('_True_, if the media in the link preview is supposed to be enlarged; ignored if the URL isn"t explicitly specified or media size change isn"t supported for the preview')]
        public ?bool $preferLargeMedia = null,
        #[Description('_True_, if the link preview must be shown above the message text; otherwise, the link preview will be shown below the message text')]
        public ?bool $showAboveText = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::LinkPreviewOptions;
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
{"is_disabled":{"property":"isDisabled","tgPropName":"is_disabled","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"prefer_small_media":{"property":"preferSmallMedia","tgPropName":"prefer_small_media","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"prefer_large_media":{"property":"preferLargeMedia","tgPropName":"prefer_large_media","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"show_above_text":{"property":"showAboveText","tgPropName":"show_above_text","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
