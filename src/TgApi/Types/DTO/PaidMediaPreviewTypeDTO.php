<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The paid media isn"t available before the payment.')]
#[See('https://core.telegram.org/bots/api#paidmediapreview')]
class PaidMediaPreviewTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Type of the paid media, always “preview”')]
        public string $type = 'preview',
        #[Description('Media width as defined by the sender')]
        public ?int $width = null,
        #[Description('Media height as defined by the sender')]
        public ?int $height = null,
        #[Description('Duration of the media in seconds as defined by the sender')]
        public ?int $duration = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PaidMediaPreview;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"preview"}],"nullable":false,"required":true},"width":{"property":"width","tgPropName":"width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"height":{"property":"height","tgPropName":"height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"duration":{"property":"duration","tgPropName":"duration","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
