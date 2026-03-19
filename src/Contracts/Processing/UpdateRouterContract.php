<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

interface UpdateRouterContract extends WithASKTickableContract
{
    public function dispatch(UpdateContext $updateContext): void;
}
