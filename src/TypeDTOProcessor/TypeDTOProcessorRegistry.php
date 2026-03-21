<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\TgUpdateProcessor;
use Generator;

class TypeDTOProcessorRegistry
{
    public bool $check = true;

    /** @var array<class-string<TgApiTypeDTOContract>, array<TgUpdateProcessorContract|string>> */
    private array $processors = [
        UpdateTypeDTO::class => [
            TgUpdateProcessor::class,
        ],
    ];

    /**
     * @param  class-string<TgApiTypeDTOContract>  $dtoClass
     * @param  TgUpdateProcessorContract|class-string<TgUpdateProcessorContract>  $processor
     */
    public function register(string $dtoClass, TgUpdateProcessorContract|string $processor): self
    {
        if ($this->check && is_string($processor)) {
            assert(is_a($processor, TgUpdateProcessorContract::class, true));
        }

        $this->processors[$dtoClass][] = $processor;

        return $this;
    }

    /**
     * @param  class-string<TgApiTypeDTOContract>  $dtoClass
     * @return Generator<TgUpdateProcessorContract>
     */
    public function get(string $dtoClass): Generator
    {
        foreach ($this->processors[$dtoClass] ?? [] as $key => $processor) {
            if (is_string($processor)) {
                $processor = $processor === TgUpdateProcessor::class
                    ? new $processor($this)
                    : new $processor();
                $this->processors[$dtoClass][$key] = $processor;
            }

            yield $processor;
        }
    }
}
