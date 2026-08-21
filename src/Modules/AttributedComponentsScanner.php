<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Modules\Attributes\TgCommandAttribute;
use BAGArt\TelegramBot\Modules\Attributes\TgMiddlewareAttribute;
use BAGArt\TelegramBot\Modules\Attributes\TgProcessorAttribute;
use BAGArt\TelegramBot\Modules\Attributes\TgRuleAttribute;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRule;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/**
 * Scans a module's source directory for classes declared via Tg* attributes
 * and translates them into TgModuleRegistrar calls. Scope is strictly the
 * provider's own directory — no global classmap scanning.
 *
 * The attribute never replaces the contract: a class carrying an attribute
 * but not implementing the matching contract is a discovery error (throw),
 * not a silent skip.
 */
class AttributedComponentsScanner
{
    private const CACHE_PREFIX = 'tg.mod.attrib.';

    public function __construct(
        private readonly ?ASKCacheWrapper $cache = null,
    ) {
    }

    /**
     * Scan (or replay from cache) the module's source directory and register
     * the found components via the registrar.
     *
     * @param  class-string<TgModuleContract>  $providerClass
     * @return list<string> declared component class names
     */
    public function scanAndRegister(string $providerClass, TgModuleRegistrar $registrar): array
    {
        $moduleId = $providerClass::descriptor()->id;
        $files = $this->collectSourceFiles($providerClass);

        $cached = $this->cache?->get(self::CACHE_PREFIX.$moduleId);
        if ($this->isCacheHit($cached, $files)) {
            /** @var array{components: list<array{method: string, class: string, args: list<mixed>}>} $cached */
            $this->replay($cached['components'], $registrar);

            return $this->declaredNames($cached['components']);
        }

        $components = $this->scan($files);
        $this->cache?->forever(
            self::CACHE_PREFIX.$moduleId,
            ['files' => $files, 'components' => $components],
        );
        $this->replay($components, $registrar);

        return $this->declaredNames($components);
    }

    /**
     * @param  class-string<TgModuleContract>  $providerClass
     * @return array<string, int> file path => mtime
     */
    private function collectSourceFiles(string $providerClass): array
    {
        $dir = dirname((string) (new ReflectionClass($providerClass))->getFileName());
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $files[$file->getPathname()] = $file->getMTime();
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * @param  array<string, int>  $files
     */
    private function isCacheHit(mixed $cached, array $files): bool
    {
        return \is_array($cached)
            && isset($cached['files'], $cached['components'])
            && $cached['files'] === $files;
    }

    /**
     * @param  array<string, int>  $files
     * @return list<array{method: string, class: string, args: list<mixed>}> registrar call tuples
     */
    private function scan(array $files): array
    {
        $components = [];

        foreach (array_keys($files) as $path) {
            $source = (string) @file_get_contents($path);
            if ($source === '' || !$this->mayContainAttributes($source)) {
                continue;
            }

            foreach ($this->declaredClasses($source) as $fqcn) {
                $components = [...$components, ...$this->componentsOf($fqcn)];
            }
        }

        return $components;
    }

    private function mayContainAttributes(string $source): bool
    {
        foreach (['TgProcessor', 'TgRule', 'TgMiddleware', 'TgCommand'] as $marker) {
            if (str_contains($source, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string> fully-qualified class names declared in the file
     */
    private function declaredClasses(string $source): array
    {
        $classes = [];
        $namespace = '';
        $tokens = token_get_all($source);
        $count = \count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token[0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_NAME_QUALIFIED || $tokens[$j][0] === T_STRING) {
                        $namespace = $tokens[$j][1];
                        break;
                    }
                    if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }
                }
                continue;
            }

            if ($token[0] === T_CLASS) {
                // skip `Foo::class` and anonymous `new class`
                for ($j = $i - 1; $j >= 0; $j--) {
                    if (\is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                        continue;
                    }
                    if ($tokens[$j][0] === T_DOUBLE_COLON || $tokens[$j][0] === T_NEW) {
                        continue 2;
                    }
                    break;
                }

                for ($j = $i + 1; $j < $count; $j++) {
                    if (\is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $classes[] = ($namespace !== '' ? $namespace.'\\' : '').$tokens[$j][1];
                        break;
                    }
                    if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * @param  class-string  $fqcn
     * @return list<array{method: string, class: string, args: list<mixed>}>
     */
    private function componentsOf(string $fqcn): array
    {
        if (!class_exists($fqcn)) {
            return [];
        }

        $reflection = new ReflectionClass($fqcn);
        $components = [];

        foreach ($reflection->getAttributes(TgProcessorAttribute::class) as $attribute) {
            /** @var TgProcessorAttribute $instance */
            $instance = $attribute->newInstance();
            if (!is_a($fqcn, TgTypeDTOProcessorContract::class, true)) {
                throw new LogicException(
                    "$fqcn has #[TgProcessor] but does not implement TgTypeDTOProcessorContract.",
                );
            }
            $components[] = ['method' => 'processor', 'class' => $fqcn, 'args' => [$instance->dto, $fqcn]];
        }

        foreach ($reflection->getAttributes(TgRuleAttribute::class) as $attribute) {
            /** @var TgRuleAttribute $instance */
            $instance = $attribute->newInstance();
            if (!is_a($fqcn, MessageValidationRule::class, true)) {
                throw new LogicException(
                    "$fqcn has #[TgRule] but does not implement MessageValidationRule.",
                );
            }
            $components[] = ['method' => 'validationRule', 'class' => $fqcn, 'args' => [$fqcn, $instance->weight]];
        }

        foreach ($reflection->getAttributes(TgMiddlewareAttribute::class) as $attribute) {
            if (!is_a($fqcn, OutboundMiddleware::class, true)) {
                throw new LogicException(
                    "$fqcn has #[TgMiddleware] but does not implement OutboundMiddleware.",
                );
            }
            $components[] = ['method' => 'outboundMiddleware', 'class' => $fqcn, 'args' => [$fqcn]];
        }

        foreach ($reflection->getAttributes(TgCommandAttribute::class) as $attribute) {
            /** @var TgCommandAttribute $instance */
            $instance = $attribute->newInstance();
            if (!is_a($fqcn, TgTypeDTOProcessorContract::class, true)) {
                throw new LogicException(
                    "$fqcn has #[TgCommand] but does not implement TgTypeDTOProcessorContract.",
                );
            }
            $components[] = ['method' => 'command', 'class' => $fqcn, 'args' => [$instance->name, $fqcn]];
        }

        return $components;
    }

    /**
     * @param  list<array{method: string, class: string, args: list<mixed>}>  $components
     * @return list<string> unique declared component class names
     */
    private function declaredNames(array $components): array
    {
        return array_values(array_unique(array_column($components, 'class')));
    }

    /**
     * @param  list<array{method: string, class: string, args: list<mixed>}>  $components
     */
    private function replay(array $components, TgModuleRegistrar $registrar): void
    {
        foreach ($components as $component) {
            $registrar->{$component['method']}(...$component['args']);
        }
    }
}
