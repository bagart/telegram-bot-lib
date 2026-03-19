<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserProfilePhotosTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to get a list of profile pictures for a user. Returns a [UserProfilePhotos](https://core.telegram.org/bots/api#userprofilephotos) object.')]
#[See('https://core.telegram.org/bots/api#getuserprofilephotos')]
class GetUserProfilePhotosMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the target user')]
        public int $userId,
        #[Description('Sequential number of the first photo to be returned. By default, all photos are returned.')]
        public ?int $offset = null,
        #[Description('Limits the number of photos to be retrieved. Values between 1-100 are accepted. Defaults to 100.')]
        public ?int $limit = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            UserProfilePhotosTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getUserProfilePhotos;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"offset":{"property":"offset","tgPropName":"offset","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"limit":{"property":"limit","tgPropName":"limit","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
