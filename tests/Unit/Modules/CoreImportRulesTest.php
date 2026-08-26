<?php

declare(strict_types=1);

/**
 * INV-4 architecture rule: the pure core namespaces must not import the
 * UI-host module types (dependency direction, D34) nor Laravel types.
 */
describe('INV-4 core import rules', function () {
    it('keeps Laravel and UI-host imports out of the Modules namespace', function () {
        $srcDir = __DIR__.'/../../../src/Modules';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
        $violations = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $code = file_get_contents($file->getPathname());
            foreach (preg_split('/\R/', $code) as $line) {
                if (preg_match('/^use\s+(\S+);/', $line, $m) !== 1) {
                    continue;
                }
                $import = ltrim($m[1], '\\');
                if (str_starts_with($import, 'BAGArt\TelegramBotMenu')
                    || str_starts_with($import, 'Illuminate')
                    || in_array($import, ['Laravel', 'App'], true)) {
                    $violations[] = "{$file->getPathname()}: $import";
                }
            }
        }

        expect($violations)->toBe([]);
    });
});
