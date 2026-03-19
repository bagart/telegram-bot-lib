<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\InputProfilePhotoTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Changes the profile photo of a managed business account. Requires the _can\_edit\_profile\_photo_ business bot right. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setbusinessaccountprofilephoto')]
class SetBusinessAccountProfilePhotoMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('The new profile photo to set')]
        public InputProfilePhotoTypeDTO $photo,
        #[Description('Pass _True_ to set the public photo, which will be visible even if the main photo is hidden by the business account"s privacy settings. An account can have only one public photo.')]
        public ?bool $isPublic = null,
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
        return TgApiMethodsEnum::setBusinessAccountProfilePhoto;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"photo":{"property":"photo","tgPropName":"photo","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputProfilePhotoTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputProfilePhoto"}],"nullable":false,"required":true},"is_public":{"property":"isPublic","tgPropName":"is_public","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
