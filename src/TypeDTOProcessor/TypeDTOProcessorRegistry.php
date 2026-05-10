<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use Generator;

class TypeDTOProcessorRegistry
{
    public bool $check = true;

    /** @var array<class-string<TgApiTypeDTOContract>, array<TgTypeDTOProcessorContract|string>> */
    private array $processors = [];
    private static array $default = [
        UpdateTypeDTO::class => [
            UpdateDTOInitProcessor::class,
        ],
    ];

    public static function build(
        array $processorsByDtoTypeList = [],
        bool $injectDefault = true,
    ): self {
        $registry = new self();
        if ($injectDefault) {
            foreach (self::$default as $dtoClass => $processorClasses) {
                foreach ($processorClasses as $processorClass) {
                    $registry->register($dtoClass, $processorClass);
                }
            }
        }

        foreach ($processorsByDtoTypeList as $dtoClass => $processorClasses) {
            foreach ($processorClasses as $processorClass) {
                $registry->register($dtoClass, $processorClass);
            }
        }

        return $registry;
    }

    /**
     * @param  class-string<TgApiTypeDTOContract>  $dtoClass
     * @param  TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>  $processor
     */
    public function register(string $dtoClass, TgTypeDTOProcessorContract|string $processor): self
    {
        if ($this->check && is_string($processor)) {
            assert(is_a($processor, TgTypeDTOProcessorContract::class, true));
        }

        $this->processors[$dtoClass][] = $processor;

        return $this;
    }

    /**
     * @param  class-string<TgApiTypeDTOContract>  $dtoClass
     * @return Generator<TgTypeDTOProcessorContract>
     */
    public function get(
        string $dtoClass,
        TgUpdateConfig $config,
    ): Generator {
        foreach ($this->processors[$dtoClass] ?? [] as $key => $processor) {
            yield is_object($processor)
                ? $processor
                : $processor::build($config);
        }
    }

    public function __destruct()
    {
        $this->processors = [];
    }
}
