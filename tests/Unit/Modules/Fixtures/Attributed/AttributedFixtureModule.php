<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed;

use BAGArt\TelegramBot\Modules\TgModuleContract;
use BAGArt\TelegramBot\Modules\TgModuleDescriptor;
use BAGArt\TelegramBot\Modules\TgModuleRegistrar;

/**
 * Fixture module declaring all its components via attributes only —
 * register() is a single registerAttributed() call.
 */
class AttributedFixtureModule implements TgModuleContract
{
    public static function descriptor(): TgModuleDescriptor
    {
        return new TgModuleDescriptor(
            id: 'attributed_fixture',
            name: 'AttributedFixture',
            version: '1.0.0',
        );
    }

    public static function register(TgModuleRegistrar $registrar): void
    {
        $registrar->registerAttributed(self::class);
    }
}
