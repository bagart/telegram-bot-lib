<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

final class TgEnvelope
{
    public function __construct(
        public TgOperation $operation,
        public TgExecutionContext $context,
    ) {
    }
}
