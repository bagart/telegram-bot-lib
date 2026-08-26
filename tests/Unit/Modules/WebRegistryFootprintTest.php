<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Modules\TgWebApiRegistry;
use BAGArt\TelegramBot\Modules\TgWebResourceRegistry;
use BAGArt\TelegramBot\Modules\TgWebUiRegistry;

describe('NFR-6 registry footprint', function () {
    it('keeps 100 modules x web class-strings under 5 MB per process', function () {
        $before = memory_get_peak_usage(true);

        $registries = [new TgWebUiRegistry(), new TgWebApiRegistry(), new TgWebResourceRegistry()];
        foreach ($registries as $registry) {
            for ($module = 0; $module < 100; $module++) {
                foreach (['Manifest', 'Handler', 'Provider'] as $kind) {
                    $registry->add("module-{$module}", "Fixture\\Module{$module}\\{$kind}");
                }
            }
            $registry->all();
        }

        expect(memory_get_peak_usage(true) - $before)->toBeLessThan(5 * 1024 * 1024);
    });
});
