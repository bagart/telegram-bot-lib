<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestOrderingContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

final class TgRequestOrderingManager implements TgRequestOrderingContract
{
    private const string LOCK_PREFIX = 'tg_ordering_';

    public function __construct(
        private readonly SchedulerContract $scheduler,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public function shouldWait(TgOutboundRequestDTO $request): bool
    {
        return $request->executionConfig->ordered;
    }

    public function acquire(TgOutboundRequestDTO $request): bool
    {
        $lockKey = $this->getLockKey($request);

        $acquired = $this->scheduler->acquireLock($lockKey);

        $this->logger?->debug(
            sprintf(
                'Ordering acquire: key=%s requestId=%s acquired=%s',
                $lockKey,
                $request->requestId,
                $acquired ? 'yes' : 'no',
            )
        );

        return $acquired;
    }

    public function release(TgOutboundRequestDTO $request): void
    {
        $lockKey = $this->getLockKey($request);

        $this->scheduler->releaseLock($lockKey);

        $this->logger?->debug(
            sprintf(
                'Ordering release: key=%s requestId=%s',
                $lockKey,
                $request->requestId,
            )
        );
    }

    private function getLockKey(TgOutboundRequestDTO $request): string
    {
        $orderingKey = $request->executionConfig->orderingKey;

        if ($orderingKey !== null && $orderingKey !== '') {
            return self::LOCK_PREFIX . $this->sanitize($orderingKey);
        }

        return self::LOCK_PREFIX . 'bot_' . sha1($request->token);
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $value);

        if (!is_string($value) || $value === '') {
            return 'default';
        }

        return $value;
    }
}
