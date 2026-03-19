<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Distributed;

use BAGArt\ASKClient\Contracts\Client\ASKClientContract;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgExecutionContext;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgOperation;

final class TgDistributedExecutor
{
    public function __construct(
        private ASKClientContract $client,
        private TgNodeRouter $router,
    ) {
    }

    public function execute(TgOperation $op): mixed
    {
        $node = $this->router->route($op);

        return $this->client->execute(
            new TgEnvelope(
                $op,
                new TgExecutionContext(
                    traceId: uniqid('trace_', true),
                    tags: ['node' => $node],
                ),
            ),
        )->await();
    }
}
