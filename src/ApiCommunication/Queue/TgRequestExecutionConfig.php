<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use InvalidArgumentException;

final class TgRequestExecutionConfig
{
    public const string MODE_SYNC = 'sync';
    public const string MODE_ASYNC = 'async';
    private const int DEFAULT_TIMEOUT_SECONDS = 30;
    private const int DEFAULT_MAX_RETRIES = 3;
    private const int DEFAULT_RETRY_BASE_DELAY_MS = 1000;

    public function __construct(
        public readonly string $mode = self::MODE_SYNC,
        public readonly bool $ordered = false,
        public readonly ?string $orderingKey = null,
        public readonly int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
        public readonly int $maxRetryAttempts = self::DEFAULT_MAX_RETRIES,
        public readonly int $retryBaseDelayMs = self::DEFAULT_RETRY_BASE_DELAY_MS,
    ) {
        if ($mode !== self::MODE_SYNC && $mode !== self::MODE_ASYNC) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid execution mode "%s". Must be "%s" or "%s".',
                    $mode,
                    self::MODE_SYNC,
                    self::MODE_ASYNC,
                )
            );
        }

        if ($timeoutSeconds < 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'timeoutSeconds must be >= 1, got %d.',
                    $timeoutSeconds,
                )
            );
        }

        if ($maxRetryAttempts < 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'maxRetryAttempts must be >= 0, got %d.',
                    $maxRetryAttempts,
                )
            );
        }

        if ($retryBaseDelayMs < 100) {
            throw new InvalidArgumentException(
                sprintf(
                    'retryBaseDelayMs must be >= 100, got %d.',
                    $retryBaseDelayMs,
                )
            );
        }

        if ($ordered && ($orderingKey === null || $orderingKey === '')) {
            throw new InvalidArgumentException(
                'ordered=true requires non-empty orderingKey.'
            );
        }
    }
}
