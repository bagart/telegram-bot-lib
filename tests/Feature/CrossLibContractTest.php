<?php

declare(strict_types=1);

/**
 * Cross-library contract smoke suite (04-qa-and-testing.md §11).
 *
 * Every concrete class in a BAGArt library that declares `implements
 * ...Contract` must be autoloadable and must expose every method of the
 * interface it claims to satisfy. This is the platform-wide tripwire for
 * contracts drifting between telegram-bot-lib / basic-lib / management-lib
 * and the async kernel ("strict contracts only" project rule).
 */
it('loads every BAGArt library contract interface', function () {
    $interfaces = bagart_contract_files();
    expect($interfaces)->not->toBeEmpty('no Contract interfaces found under misc/BAGArt');

    foreach ($interfaces as [$fqcn, $file]) {
        expect($fqcn)->not->toBeNull("interface not parseable in {$file}");
        expect(interface_exists($fqcn))->toBeTrue(
            "contract {$fqcn} is not autoloadable"
        );
    }
});

it('satisfies every declared contract implementation', function () {
    $checked = 0;

    foreach (glob_recursive_php(base_path('misc/BAGArt')) as $file) {
        $src = bagart_strip_comments((string) @file_get_contents($file));
        if (! preg_match('/\b(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)\s*(extends\s+[\w\\\\]+)?\s*(implements\s+([^\{]+))?\{/', $src, $class)) {
            continue;
        }
        $implements = $class[4] ?? '';
        if (trim($implements) === '' || ! str_contains($implements, 'Contract')) {
            continue;
        }
        $ns = '';
        if (preg_match('/namespace\s+([\w\\\\]+);/', $src, $nsM)) {
            $ns = $nsM[1];
        }
        $fqcn = $ns.'\\'.$class[1];
        if (! class_exists($fqcn)) {
            continue;
        }
        $ref = new ReflectionClass($fqcn);

        foreach (array_map('trim', explode(',', $implements)) as $short) {
            $contractFqcn = resolve_contract_name($src, $ns, trim($short, '\\? '));
            if ($contractFqcn === null || ! interface_exists($contractFqcn)) {
                continue;
            }
            if (! $ref->isInstantiable()) {
                continue; // abstract bases legitimately delegate methods
            }
            foreach (get_class_methods($contractFqcn) as $method) {
                expect($ref->hasMethod($method))->toBeTrue(
                    "{$fqcn} claims {$contractFqcn} but misses {$method}()"
                );
            }
            $checked++;
        }
    }

    expect($checked)->toBeGreaterThan(0, 'no contract implementations discovered');
});

/** FQCNs of interfaces named *Contract under misc/BAGArt, with file paths. */
function bagart_contract_files(): array
{
    $out = [];
    foreach (glob_recursive_php(base_path('misc/BAGArt')) as $file) {
        $src = bagart_strip_comments((string) @file_get_contents($file));
        if (preg_match('/\binterface\s+(\w+Contract)\b/', $src, $m)
            && preg_match('/namespace\s+([\w\\\\]+);/', $src, $ns)) {
            $out[] = [$ns[1].'\\'.$m[1], $file];
        }
    }

    return $out;
}

/** Remove comments so prose like "interface serves both" cannot match. */
function bagart_strip_comments(string $src): string
{
    $tokens = token_get_all($src);
    $out = '';
    foreach ($tokens as $token) {
        if (is_array($token)) {
            if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $out .= $token[1];
        } else {
            $out .= $token;
        }
    }

    return $out;
}

/** Resolve a short Contract name via use-imports or the shared namespace. */
function resolve_contract_name(string $src, string $ns, string $short): ?string
{
    if ($short === '' || ! str_ends_with($short, 'Contract')) {
        return null;
    }
    if (str_contains($short, '\\')) {
        return $short;
    }
    if (preg_match('/use\s+([\w\\\\]+\\\\'.preg_quote($short, '/').');/', $src, $m)) {
        return $m[1];
    }
    $shared = $ns.'\\'.$short;

    return interface_exists($shared) ? $shared : null;
}

/** Recursively list .php files below a path, skipping generated trees. */
function glob_recursive_php(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );
    $files = [];
    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        if ($item->isFile() && $item->getExtension() === 'php'
            && ! str_contains($item->getPathname(), 'TgApi')) {
            $files[] = str_replace('\\', '/', $item->getPathname());
        }
    }

    return $files;
}
