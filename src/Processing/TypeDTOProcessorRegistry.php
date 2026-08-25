<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\AsyncKernel\Exceptions\ASKInterruptException;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use Generator;

class TypeDTOProcessorRegistry
{
    public bool $check = true;

    /** @var array<class-string<TgApiTypeDTOContract>, array<TgTypeDTOProcessorContract|string>> */
    private array $processors = [];

    public static function build(
        array $processorsByDtoTypeList = [],
    ): self {
        $registry = new self;

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

        $classToRegister = is_string($processor) ? $processor : $processor::class;

        foreach ($this->processors[$dtoClass] ?? [] as $key => $existing) {
            $existingClass = is_string($existing) ? $existing : $existing::class;
            if ($existingClass === $classToRegister) {
                $this->processors[$dtoClass][$key] = $processor;

                return $this;
            }
        }

        $this->processors[$dtoClass][] = $processor;

        return $this;
    }

    /**
     * @param  class-string<TgApiTypeDTOContract>|TgApiTypeDTOContract  $dto
     * @return Generator<TgTypeDTOProcessorContract>
     */
    public function get(
        string|TgApiTypeDTOContract $dto,
        BotProcessorContext $context,
    ): Generator {
        if ($dto instanceof TgApiTypeDTOContract) {
            $dto = $dto::class;
        }
        foreach ($this->processors[$dto] ?? [] as $key => $processor) {
            if (! is_object($processor)) {
                try {
                    $processor = $processor::build($context);
                } catch (ASKInterruptException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    throw new ASKInterruptException(
                        source: 'processor.build',
                        message: "Build Processor error: $processor::build => ".$e::class." {$e->getMessage()}"
                    );
                }
                assert(is_a($processor, TgTypeDTOProcessorContract::class));
            }

            yield $processor;
        }
    }

    public function __destruct()
    {
        $this->processors = [];
    }
}
