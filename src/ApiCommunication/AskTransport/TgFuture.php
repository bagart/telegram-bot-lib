<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

use BAGArt\ASKClient\Contracts\ASKFutureContract;

final class TgFuture implements TgFutureContract
{
    public function __construct(
        private ASKFutureContract $inner,
    ) {
    }

    public function await(): mixed
    {
        return $this->inner->await();
    }

    public function then(callable $cb): self
    {
        $this->inner = $this->inner->then($cb);

        return $this;
    }

    public function catch(callable $cb): self
    {
        $this->inner = $this->inner->catch($cb);

        return $this;
    }
}
