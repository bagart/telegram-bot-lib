<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\OwnedGiftsTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Returns the gifts owned and hosted by a user. Returns [OwnedGifts](https://core.telegram.org/bots/api#ownedgifts) on success.')]
#[See('https://core.telegram.org/bots/api#getusergifts')]
class GetUserGiftsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the user')]
        public int $userId,
        #[Description('Pass _True_ to exclude gifts that can be purchased an unlimited number of times')]
        public ?bool $excludeUnlimited = null,
        #[Description('Pass _True_ to exclude gifts that can be purchased a limited number of times and can be upgraded to unique')]
        public ?bool $excludeLimitedUpgradable = null,
        #[Description('Pass _True_ to exclude gifts that can be purchased a limited number of times and can"t be upgraded to unique')]
        public ?bool $excludeLimitedNonUpgradable = null,
        #[Description('Pass _True_ to exclude gifts that were assigned from the TON blockchain and can"t be resold or transferred in Telegram')]
        public ?bool $excludeFromBlockchain = null,
        #[Description('Pass _True_ to exclude unique gifts')]
        public ?bool $excludeUnique = null,
        #[Description('Pass _True_ to sort results by gift price instead of send date. Sorting is applied before pagination.')]
        public ?bool $sortByPrice = null,
        #[Description('Offset of the first entry to return as received from the previous request; use an empty string to get the first chunk of results')]
        public ?string $offset = null,
        #[Description('The maximum number of gifts to be returned; 1-100. Defaults to 100')]
        public ?int $limit = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            OwnedGiftsTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getUserGifts;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"exclude_unlimited":{"property":"excludeUnlimited","tgPropName":"exclude_unlimited","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"exclude_limited_upgradable":{"property":"excludeLimitedUpgradable","tgPropName":"exclude_limited_upgradable","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"exclude_limited_non_upgradable":{"property":"excludeLimitedNonUpgradable","tgPropName":"exclude_limited_non_upgradable","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"exclude_from_blockchain":{"property":"excludeFromBlockchain","tgPropName":"exclude_from_blockchain","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"exclude_unique":{"property":"excludeUnique","tgPropName":"exclude_unique","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"sort_by_price":{"property":"sortByPrice","tgPropName":"sort_by_price","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"offset":{"property":"offset","tgPropName":"offset","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"limit":{"property":"limit","tgPropName":"limit","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
