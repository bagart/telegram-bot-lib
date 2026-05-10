<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\Exceptions\TgUnregisteredEntityNameException;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgEntityToDTORegistry implements TgApiDTORegistryContract
{
    /**
     * @var array<array<string, class-string<TgApiDTOContract>>>
     */
    private array $entityToDTORegistry = [];

    public function __construct(
        private TgBotLogWrapper $logger,
    ) {
    }

    /**
     * @param TgApiEntityScopeEnum|TgApiEntityScopeEnumContract|class-string<TgApiEntityScopeEnumContract> $tgApiEntityScopeEnum
     */
    public static function build(
        TgApiEntityScopeEnumContract|string $tgApiEntityScopeEnum = TgApiEntityScopeEnum::class,
        ?TgBotLogWrapper $logger = null,
    ): TgEntityToDTORegistry {
        $tgEntityNameToDTORegistry = new TgEntityToDTORegistry(
            logger: $logger ?? TgBotLogWrapper::build(),
        );

        /** @var TgApiTypesEnum|TgApiMethodsEnum $entityScopeEnum */
        foreach ($tgApiEntityScopeEnum::cases() as $dtoScopeEnum) {
            /** @var TgApiEntityEnumContract $entityDTOEnum */
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

    public function register(
        #[DTO(TgApiDTOContract::class)]
        string|TgApiDTOContract $dtoClassName,
        ?TgApiEntityEnumContract $entityName = null,
        ?TgApiEntityScopeEnumContract $entityScope = null,
        bool $overwrite = true,
    ): self {
        $entityNameStr = $entityName ? $entityName->name : $dtoClassName::tgApiEntity()->name;
        $entityScopeStr = $entityScope ? $entityScope->name : $dtoClassName::tgEntityScope()->name;
        if (
            !$overwrite
            && isset($this->entityToDTORegistry[$entityScopeStr][$entityNameStr])
        ) {
            $this->logger?->warning(
                "TgEntityNameToDTORegistry: try to overwrite already registered $entityScope: $entityName"
            );
        }

        if (is_object($dtoClassName)) {
            $dtoClassName = $dtoClassName::class;
        }
        $this->entityToDTORegistry[$entityScopeStr][$entityNameStr] = $dtoClassName;

        return $this;
    }

    /** @return TgApiDTOContract|string */
    public function getDTO(
        string|TgApiEntityEnumContract $tgEntityName,
        ?TgApiEntityScopeEnumContract $tgEntityScope = null,
    ): string {
        if (is_object($tgEntityName)) {
            $tgEntityName = $tgEntityName->name;
        }

        if ($tgEntityScope !== null) {
            if (!isset($this->entityToDTORegistry[$tgEntityScope->name][$tgEntityName])) {
                throw new TgUnregisteredEntityNameException($tgEntityName, $tgEntityScope->name);
            }

            return $this->entityToDTORegistry[$tgEntityScope->name][$tgEntityName];
        }

        foreach (array_keys($this->entityToDTORegistry) as $entityScopeStr) {
            if (isset($this->entityToDTORegistry[$entityScopeStr][$tgEntityName])) {
                return $this->entityToDTORegistry[$entityScopeStr][$tgEntityName];
            }
        }

        throw new TgUnregisteredEntityNameException($tgEntityName);
    }
}
