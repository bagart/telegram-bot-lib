<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the rating of a user based on their Telegram Star spendings.')]
#[See('https://core.telegram.org/bots/api#userrating')]
class UserRatingTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Current level of the user, indicating their reliability when purchasing digital goods and services. A higher level suggests a more trustworthy customer; a negative level is likely reason for concern.')]
        public int $level,
        #[Description('Numerical value of the user"s rating; the higher the rating, the better')]
        public int $rating,
        #[Description('The rating value required to get the current level')]
        public int $currentLevelRating,
        #[Description('The rating value required to get to the next level; omitted if the maximum level was reached')]
        public ?int $nextLevelRating = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UserRating;
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
{"level":{"property":"level","tgPropName":"level","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"rating":{"property":"rating","tgPropName":"rating","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"current_level_rating":{"property":"currentLevelRating","tgPropName":"current_level_rating","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"next_level_rating":{"property":"nextLevelRating","tgPropName":"next_level_rating","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
