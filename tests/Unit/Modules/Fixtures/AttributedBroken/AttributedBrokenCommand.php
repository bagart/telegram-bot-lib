<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\AttributedBroken;

use BAGArt\TelegramBot\Modules\Attributes\TgCommandAttribute;

#[TgCommandAttribute(name: 'broken_command')]
class AttributedBrokenCommand
{
}
