<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApiServices;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;

interface TgApiDTORegistryContract
{
    /**
     * @param TgApiEntityScopeEnum|TgApiEntityScopeEnumContract|class-string<TgApiEntityScopeEnumContract> $tgApiEntityScopeEnum
     */
    public static function build(
        TgApiEntityScopeEnumContract|string $tgApiEntityScopeEnum = TgApiEntityScopeEnum::class,
        ?ASKLogWrapper $logger = null,
    ): TgApiDTORegistryContract;

    public function register(
        string|TgApiDTOContract $dtoClassName,
        ?TgApiEntityEnumContract $entityName = null,
        ?TgApiEntityScopeEnumContract $entityScope = null,
    ): self;

    /** @return TgApiDTOContract|string */
    public function getDTO(
        string|TgApiEntityEnumContract $tgEntityName,
        ?TgApiEntityScopeEnumContract $tgEntityScope = null,
    ): string;
}
