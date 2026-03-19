<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgEntityToDTORegistryFactory
{
    public function __construct(
        private TgBotLogWrapper $logger,
    ) {
    }

    /**
     * @param TgApiEntityScopeEnum|TgApiEntityScopeEnumContract|string $tgApiEntityScopeEnum
     */
    public function default(
        TgApiEntityScopeEnumContract|string $tgApiEntityScopeEnum = TgApiEntityScopeEnum::class,
    ): TgEntityToDTORegistry {
        $tgEntityNameToDTORegistry = new TgEntityToDTORegistry(
            logger: $this->logger,
        );

        /** @var TgApiTypesEnum|TgApiMethodsEnum $entityScopeEnum */
        /** @var TgApiEntityEnumContract $entityDTOEnum */
        foreach ($tgApiEntityScopeEnum::cases() as $dtoScopeEnum) {
            foreach ($dtoScopeEnum->value::cases() as $entityDTOEnum) {
                $tgEntityNameToDTORegistry->register(
                    $entityDTOEnum->value,
                    $entityDTOEnum,
                    $dtoScopeEnum,
                );
            }
        }

        return $tgEntityNameToDTORegistry;
    }
}
