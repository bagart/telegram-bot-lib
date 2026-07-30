<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\AttributedBroken;

use BAGArt\TelegramBot\Modules\TgModuleContract;
use BAGArt\TelegramBot\Modules\TgModuleDescriptor;
use BAGArt\TelegramBot\Modules\TgModuleRegistrar;

/**
 * Fixture module whose directory contains an attributed class that does
 * NOT implement the required contract — discovery error, module must be
 * skipped by fault isolation.
 */
class AttributedBrokenModule implements TgModuleContract
{
    public static function descriptor(): TgModuleDescriptor
    {
        return new TgModuleDescriptor(
            id: 'attributed_broken_fixture',
            name: 'AttributedBrokenFixture',
            version: '1.0.0',
        );
    }

    public static function register(TgModuleRegistrar $registrar): void
    {
        $registrar->registerAttributed(self::class);
    }
}
