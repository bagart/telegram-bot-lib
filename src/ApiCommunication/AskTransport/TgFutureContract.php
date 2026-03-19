<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

interface TgFutureContract
{
    public function await(): mixed;

    public function then(callable $cb): self;

    public function catch(callable $cb): self;
}
