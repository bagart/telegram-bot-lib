<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedApiReturnException;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

readonly class TgApiDTOMapper implements TgApiDTOMapperContract
{
    public function __construct(
        private TgBotLogWrapper $logger,
        private TgApiDTORegistryContract $tgApiDTORegistry,
    ) {
    }

    public function toArray(TgApiDTOContract $dto): array
    {
        $data = [];
        foreach ($dto::tgPropertyMetas() as $tgPropName => $dtoProperty) {
            $dtoPop = $dtoProperty->property;
            $value = $dto->$dtoPop;
            if ($value instanceof TgApiDTOContract) {
                $value = $this->toArray($value);
            } elseif ($value instanceof TgApiEnumContract) {
                $value = $value->value;
            }
            if ($value !== null || $dtoProperty->required) {
                $data[$tgPropName] = $value;
            }
        }

        return $data;
    }

    public function fromArray(
        string|TgApiDTOContract|TgApiEntityEnumContract $entity,
        array $data,
    ): TgApiDTOContract {
        if (is_a($entity, TgApiDTOContract::class, true)) {
            $class = is_object($entity) ? $entity::class : $entity;
        } elseif ($entity instanceof TgApiEntityEnumContract) {
            $class = $entity->value;
        } else {
            $class = $this->tgApiDTORegistry->getDTO($entity);
        }
        $entity = $class::tgApiEntity()->name;

        $propMetas = $class::tgPropertyMetas();
        $resultArg = [];
        $unexpectedArg = [];
        foreach ($data as $key => $propValue) {
            if (isset($propMetas[$key])) {
                $resultArg[$propMetas[$key]->property] = $this->prepareFormat(
                    $entity,
                    $propMetas[$key]->types,
                    $propValue,
                );
            } elseif (
                $key === 'thumb'
                && isset($propMetas['thumbnail'])
                && isset($data['thumbnail'])
            ) {
                $this->logger->debug('SKIP API double: thumbnail|thumb');
            } else {
                $unexpectedArg[$key] = $propValue;
            }
        }

        if ($unexpectedArg !== []) {
            $this->logger->warning(
                '[WARN] Unexpected '
                .$class.'::tgPropertyMetas keys: '
                .implode(', ', array_keys($unexpectedArg))
                .";\ndata=".json_encode($data)
            );
        }

        return new $class(...$resultArg);
    }

    private function prepareFormat(
        string $entity,
        array|string|TgApiDTOContract $phpTypes,
        mixed $propValue,
    ): mixed {
        if ($phpTypes === [] || $propValue === null) {
            if ($propValue !== null) {
                throw new TgUnexpectedApiReturnException(
                    $entity,
                    $phpTypes,
                    $propValue,
                );
            }

            return null;
        }

        $phpTypes = $this->categorizeTypes($phpTypes);

        foreach ($phpTypes as $phpType) {
            if (is_array($phpType)) {
                if (!is_array($propValue)) {
                    continue;
                }

                $result = [];
                foreach ($propValue as $key => $subValue) {
                    try {
                        $result[$key] = $this->prepareFormat($entity, $phpType, $subValue);
                    } catch (TgUnexpectedApiReturnException $e) {
                        continue;
                    }
                }

                return $result;
            }
            assert(is_string($phpType));

            if (str_ends_with($phpType, 'DTO')) {
                try {
                    return $this->fromArray($phpType, $propValue);
                } catch (TgUnexpectedApiReturnException $e) {
                    continue;
                }
            }
            if (str_ends_with($phpType, 'Enum')) {
                /** @var TgApiEnumContract $phpType */
                return $phpType::tryFrom($propValue)
                    ?? throw new TgUnexpectedApiReturnException(
                        $entity,
                        $phpType,
                        $propValue,
                    );
            }
            if ($phpType === 'string' && !is_string($propValue) && is_numeric($propValue)) {
                //int52|float
                $propValue = (string)$propValue;
            }

            if ($this->matchPrimitiveType($phpType, $propValue)) {
                return $propValue;
            }
        }

        throw new TgUnexpectedApiReturnException(
            $entity,
            $phpTypes,
            $propValue,
        );
    }

    public function categorizeTypes(array $phpTypes): array
    {
        $priority1 = [];
        $priority2 = [];
        $priority3 = [];
        foreach ($phpTypes as $phpType) {
            if (is_array($phpType)) {
                $priority3[] = $phpType;
            } elseif (
                str_ends_with($phpType, 'DTO')
                || str_ends_with($phpType, 'Enum')
            ) {
                $priority1[] = $phpType;
            } else {
                $priority2[] = $phpType;
            }
        }

        return array_merge($priority1, $priority2, $priority3);
    }

    public function matchPrimitiveType(string $phpType, mixed $value): bool
    {
        return match ($phpType) {
            'null' => is_null($value),
            'int' => is_int($value),
            'bool' => is_bool($value),
            'string' => is_string($value),
            'float' => is_float($value),
            default => false,
        };
    }
}
