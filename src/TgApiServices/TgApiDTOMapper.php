<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedDataFormatException;

readonly class TgApiDTOMapper implements TgApiDTOMapperContract
{
    private const string UPLOAD_FILE_SCHEME = 'file://';

    public function __construct(
        private TgApiDTORegistryContract $tgApiDTORegistry,
        private ?ASKLogWrapper $logger = null,
    ) {
    }

    public function toArray(TgApiDTOContract $dto): array
    {
        $data = [];
        foreach ($dto::tgPropertyMetas() as $tgPropName => $dtoProperty) {
            $value = $this->normalizeValue($dto, $dtoProperty, $dto->{$dtoProperty->property});
            if ($value !== null || $dtoProperty->required) {
                $data[$tgPropName] = $value;
            }
        }

        return $data;
    }

    public function splitRequest(TgApiMethodDTOContract $dto): array
    {
        $parameters = [];
        $files = [];

        foreach ($dto::tgPropertyMetas() as $tgPropName => $dtoProperty) {
            $rawValue = $dto->{$dtoProperty->property};

            // Uploadable local files travel in ASKHttpRequest::$files instead
            // of the JSON body (Track B, todo.tts.md §6).
            if (
                is_string($rawValue)
                && str_starts_with($rawValue, self::UPLOAD_FILE_SCHEME)
                && self::acceptsUploadedFile($dtoProperty)
            ) {
                $path = substr($rawValue, strlen(self::UPLOAD_FILE_SCHEME));

                if ($path !== '') {
                    $files[$tgPropName] = $path;

                    continue;
                }
            }

            $value = $this->normalizeValue($dto, $dtoProperty, $rawValue);

            if ($value !== null || $dtoProperty->required) {
                $parameters[$tgPropName] = $value;
            }
        }

        return ['parameters' => $parameters, 'files' => $files];
    }

    private function normalizeValue(TgApiDTOContract $dto, TgApiProperty $dtoProperty, mixed $value): mixed
    {
        if ($value instanceof TgApiDTOContract) {
            return $this->toArray($value);
        }

        if ($value instanceof TgApiEnumContract) {
            return $value->value;
        }

        return $value;
    }

    private static function acceptsUploadedFile(TgApiProperty $dtoProperty): bool
    {
        foreach ($dtoProperty->tgTypes as $tgType) {
            if (($tgType['type'] ?? null) === 'input-file') {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws TgUnexpectedDataFormatException
     */
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
                $this->logger?->debug('SKIP API double: thumbnail|thumb');
            } else {
                $unexpectedArg[$key] = $propValue;
            }
        }

        if ($unexpectedArg !== []) {
            $this->logger?->warning(
                'Unexpected '
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
                throw new TgUnexpectedDataFormatException(
                    $entity,
                    $phpTypes,
                    $propValue,
                );
            }

            return null;
        }

        $phpTypes = $this->categorizeTypes($phpTypes);
        // oneOf unions: try discriminator-matched variants first, then the rest
        $phpTypes = $this->prioritizeDiscriminatorMatches($phpTypes, $propValue);

        foreach ($phpTypes as $phpType) {
            if (is_array($phpType)) {
                if (! is_array($propValue)) {
                    continue;
                }

                $result = [];
                foreach ($propValue as $key => $subValue) {
                    try {
                        $result[$key] = $this->prepareFormat($entity, $phpType, $subValue);
                    } catch (TgUnexpectedDataFormatException $e) {
                        continue;
                    }
                }

                return $result;
            }
            assert(is_string($phpType));

            if (str_ends_with($phpType, 'DTO')) {
                try {
                    return $this->fromArray($phpType, $propValue);
                } catch (TgUnexpectedDataFormatException|\ArgumentCountError|\TypeError) {
                    // candidate does not fit the payload (oneOf union) — try next
                    continue;
                }
            }
            if (str_ends_with($phpType, 'Enum')) {
                /** @var TgApiEnumContract $phpType */
                return $phpType::tryFrom($propValue)
                    ?? throw new TgUnexpectedDataFormatException(
                        $entity,
                        $phpType,
                        $propValue,
                    );
            }
            if ($phpType === 'string' && ! is_string($propValue) && is_numeric($propValue)) {
                // int52|float
                $propValue = (string) $propValue;
            }

            if ($this->matchPrimitiveType($phpType, $propValue)) {
                return $propValue;
            }
        }

        throw new TgUnexpectedDataFormatException(
            $entity,
            $phpTypes,
            $propValue,
        );
    }

    /**
     * oneOf unions hydrate several constructor-compatible candidates; variants
     * carrying a TgApiOneOfVariantContract whose discriminator literals match
     * the payload must be tried first, otherwise the first hydratable class
     * would win regardless of the data.
     *
     * @param  array<string|list<string>>  $phpTypes
     */
    private function prioritizeDiscriminatorMatches(array $phpTypes, mixed $propValue): array
    {
        if (! is_array($propValue)) {
            return $phpTypes;
        }

        $matched = [];
        $rest = [];
        foreach ($phpTypes as $type) {
            $candidates = is_array($type) ? $type : [$type];
            $hit = [];
            foreach ($candidates as $candidate) {
                if (
                    is_string($candidate)
                    && str_ends_with($candidate, 'DTO')
                    && is_subclass_of($candidate, \BAGArt\TelegramBot\Contracts\TgApi\TgApiOneOfVariantContract::class)
                    && $this->discriminatorsMatch($candidate, $propValue)
                ) {
                    $hit[] = $candidate;
                }
            }

            if ($hit !== [] && is_array($type)) {
                // array-of union: matched variants first inside the nested list
                $rest[] = array_values(array_merge($hit, array_diff($candidates, $hit)));
            } elseif ($hit !== []) {
                $matched = array_merge($matched, $hit);
            } else {
                $rest[] = $type;
            }
        }

        return array_merge($matched, $rest);
    }

    private function discriminatorsMatch(string $class, array $data): bool
    {
        foreach ($class::tgDiscriminators() as $field => $literals) {
            $actual = $data[$field] ?? null;
            if (! is_string($actual) || ! in_array($actual, $literals, true)) {
                return false;
            }
        }

        return true;
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
