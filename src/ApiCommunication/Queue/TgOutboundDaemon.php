<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestOrderingContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueConsumerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueProducerContract;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use BAGArt\TelegramBot\Exceptions\TgQueueException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use SplObjectStorage;
use Throwable;

final class TgOutboundDaemon
{
    private const string RATE_LIMIT_KEY_TPL = 'tg_bot_%s_%s';

    private const float ORDERING_LOCK_RETRY_DELAY = 0.5;

    private const float RATE_LIMIT_RETRY_DELAY = 1.0;

    private const int IDLE_SLEEP_US = 200000;

    private const int MIN_SLEEP_US = 1000;

    private const int MAX_CONCURRENT_FIBERS = 500;

    private bool $shouldStop = false;

    private int $totalProcessed = 0;

    private int $totalErrors = 0;

    private int $concurrentFibers = 0;

    /**
     * @var SplObjectStorage<Fiber, float>
     */
    private SplObjectStorage $delayedUnpark;

    public function __construct(
        private readonly QueueConsumerContract $consumer,
        private readonly QueueProducerContract $producer,
        private readonly SchedulerContract $scheduler,
        private readonly TgBotApiDTOClient $dtoClient,
        private readonly TgRequestOrderingContract $ordering,
        private readonly TgRateLimiterContract $rateLimiter,
        private readonly TgRetryPolicyContract $retryPolicy,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
        $this->delayedUnpark = new SplObjectStorage();
    }

    public function run(): void
    {
        $this->logInfo('TgOutboundDaemon started');
        $this->consumer->connect();
        $this->producer->connect();

        $this->setupSignalHandlers();

        while (!$this->shouldStop) {
            try {
                $this->processDelayedUnparks();

                if ($this->concurrentFibers < self::MAX_CONCURRENT_FIBERS) {
                    $request = $this->consumer->consume();

                    if ($request !== null) {
                        $this->dispatchRequest($request);
                    }
                }

                $this->scheduler->tick();

                $this->adaptiveSleep();
            } catch (Throwable $e) {
                $this->logger?->error(
                    sprintf(
                        'Daemon loop error: %s',
                        $e->getMessage(),
                    ),
                    ['exception' => $e::class]
                );

                usleep(self::IDLE_SLEEP_US);
            }
        }

        $this->shutdown();
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    public function parkWithDelay(float $delaySeconds): void
    {
        $fiber = Fiber::getCurrent();

        if ($fiber === null) {
            usleep((int) ($delaySeconds * 1_000_000));
            return;
        }

        $this->delayedUnpark->attach(
            $fiber,
            microtime(true) + $delaySeconds,
        );

        $this->scheduler->parkCurrentFiber();
    }

    private function dispatchRequest(TgOutboundRequestDTO $request): void
    {
        ++$this->concurrentFibers;

        $daemon = $this;

        $fiber = new Fiber(function () use ($request, $daemon): void {
            $responseDTO = null;

            try {
                $daemon->acquireOrderingLock($request);

                try {
                    $apiResponse = $daemon->executeWithRetry($request);
                } finally {
                    $daemon->ordering->release($request);
                }

                $responseDTO = new TgOutboundResponseDTO(
                    requestId: $request->requestId,
                    success: true,
                    result: $apiResponse,
                    responseQueue: $request->responseQueue,
                    completedAt: time(),
                );

                ++$daemon->totalProcessed;

                $daemon->logger?->debug(
                    sprintf(
                        'Request completed: method=%s requestId=%s',
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                    )
                );
            } catch (Throwable $e) {
                $responseDTO = new TgOutboundResponseDTO(
                    requestId: $request->requestId,
                    success: false,
                    result: null,
                    error: $e->getMessage(),
                    errorCode: $e->getCode() !== 0 ? $e->getCode() : null,
                    retryAfter: $daemon->extractRetryAfter($e),
                    responseQueue: $request->responseQueue,
                    completedAt: time(),
                );

                ++$daemon->totalErrors;

                $daemon->logger?->error(
                    sprintf(
                        'Request failed after retries: method=%s requestId=%s error=%s',
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                        $e->getMessage(),
                    ),
                    [
                        'exception' => $e::class,
                        'request_id' => $request->requestId,
                    ]
                );
            } finally {
                --$daemon->concurrentFibers;
            }

            if ($responseDTO !== null) {
                try {
                    $daemon->producer->publishResponse($responseDTO);
                } catch (Throwable $e) {
                    $daemon->logger?->error(
                        sprintf(
                            'Failed to publish response: requestId=%s error=%s',
                            $request->requestId,
                            $e->getMessage(),
                        ),
                        ['exception' => $e::class]
                    );
                }
            }
        });

        $this->scheduler->enqueue($fiber);
    }

    private function executeWithRetry(TgOutboundRequestDTO $request): TgApiResponse
    {
        $maxAttempts = $request->executionConfig->maxRetryAttempts + 1;

        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            $this->acquireRateLimit($request);

            try {
                $this->logger?->debug(
                    sprintf(
                        'Executing request: method=%s requestId=%s',
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                    )
                );

                $promise = $this->dtoClient->requestAsync(
                    token: $request->token,
                    dto: $request->dto,
                );

                $apiResponse = $this->scheduler->await($promise);

                if (!$apiResponse instanceof TgApiResponse) {
                    throw new TgQueueException(
                        sprintf(
                            'DTO client returned non-TgApiResponse for method=%s requestId=%s',
                            $request->dto->tgApiEntity()->name,
                            $request->requestId,
                        )
                    );
                }

                if (!$apiResponse->ok) {
                    throw new TgQueueException(
                        sprintf(
                            'Telegram API returned error for method=%s requestId=%s',
                            $request->dto->tgApiEntity()->name,
                            $request->requestId,
                        )
                    );
                }

                if ($apiResponse->ok) {
                    return $apiResponse;
                }

                if (
                    $this->handleErrorResponse(
                        $request,
                        $apiResponse,
                        $attempt,
                        $maxAttempts,
                    )
                ) {
                    continue;
                }

                throw new TgBotTechnicalException(
                    sprintf(
                        'Telegram API error: %s (code: %s)',
                        $apiResponse->result ?? 'Unknown error',
                        (string) ($apiResponse->errorCode ?? 'N/A'),
                    )
                );
            } catch (Throwable $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                if (
                    !$this->retryPolicy->shouldRetry(
                        $request->dto->tgApiEntity()->name,
                        $attempt,
                        $e,
                    )
                ) {
                    throw $e;
                }

                $delay = $this->retryPolicy->getDelay($attempt);

                $this->logger?->debug(
                    sprintf(
                        'Retry attempt %d/%d for method=%s requestId=%s delay=%ds error=%s',
                        $attempt,
                        $maxAttempts,
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                        $delay,
                        $e->getMessage(),
                    )
                );

                $this->parkWithDelay((float) $delay);
            }
        }

        throw new TgBotTechnicalException(
            sprintf(
                'Max retry attempts exceeded for method=%s requestId=%s',
                $request->dto->tgApiEntity()->name,
                $request->requestId,
            )
        );
    }

    private function handleErrorResponse(
        TgOutboundRequestDTO $request,
        TgApiResponse $apiResponse,
        int $attempt,
        int $maxAttempts,
    ): bool {
        $errorCode = $apiResponse->errorCode;

        if ($errorCode === 429) {
            $retryAfter = $apiResponse->retryAfter ?? 1;

            if ($attempt >= $maxAttempts) {
                return false;
            }

            $this->logger?->warning(
                sprintf(
                    'Rate limited (429): method=%s requestId=%s attempt=%d/%d retry_after=%d',
                    $request->dto->tgApiEntity()->name,
                    $request->requestId,
                    $attempt,
                    $maxAttempts,
                    $retryAfter,
                )
            );

            $this->parkWithDelay((float) max(1, $retryAfter));

            return true;
        }

        return false;
    }

    private function acquireRateLimit(TgOutboundRequestDTO $request): void
    {
        $rateKey = sprintf(
            self::RATE_LIMIT_KEY_TPL,
            sha1($request->token),
            $request->dto->tgApiEntity()->name,
        );

        while (!$this->rateLimiter->acquire($rateKey)) {
            $this->logger?->debug(
                sprintf(
                    'Rate limit wait: method=%s requestId=%s key=%s',
                    $request->dto->tgApiEntity()->name,
                    $request->requestId,
                    $rateKey,
                )
            );

            $this->parkWithDelay(self::RATE_LIMIT_RETRY_DELAY);
        }
    }

    private function acquireOrderingLock(TgOutboundRequestDTO $request): void
    {
        if (!$this->ordering->shouldWait($request)) {
            return;
        }

        while (!$this->ordering->acquire($request)) {
            $this->logger?->debug(
                sprintf(
                    'Ordering lock wait: requestId=%s key=%s',
                    $request->requestId,
                    $request->executionConfig->orderingKey
                    ?? 'bot:' . sha1($request->token),
                )
            );

            $this->parkWithDelay(self::ORDERING_LOCK_RETRY_DELAY);
        }
    }

    private function processDelayedUnparks(): void
    {
        if ($this->delayedUnpark->count() === 0) {
            return;
        }

        $now = microtime(true);
        $readyFibers = [];

        foreach ($this->delayedUnpark as $fiber) {
            $unparkAt = $this->delayedUnpark->getInfo();

            if ($now >= $unparkAt) {
                $readyFibers[] = $fiber;
            }
        }

        foreach ($readyFibers as $fiber) {
            $this->delayedUnpark->detach($fiber);

            try {
                $this->scheduler->unpark($fiber);
            } catch (Throwable) {
            }
        }
    }

    private function adaptiveSleep(): void
    {
        if (
            !$this->scheduler->isIdle()
            && $this->concurrentFibers > 0
        ) {
            return;
        }

        if ($this->delayedUnpark->count() > 0) {
            $nextUnpark = null;

            foreach ($this->delayedUnpark as $fiber) {
                $time = $this->delayedUnpark->getInfo();

                if ($nextUnpark === null || $time < $nextUnpark) {
                    $nextUnpark = $time;
                }
            }

            if ($nextUnpark !== null) {
                $remainingUs = (int) (
                    ($nextUnpark - microtime(true)) * 1_000_000
                );

                if ($remainingUs > self::MIN_SLEEP_US) {
                    usleep(
                        min(
                            $remainingUs,
                            self::IDLE_SLEEP_US,
                        )
                    );
                }

                return;
            }
        }

        usleep(self::IDLE_SLEEP_US);
    }

    private function extractRetryAfter(Throwable $e): ?int
    {
        if (method_exists($e, 'getRetryAfter')) {
            $retryAfter = $e->getRetryAfter();

            if (is_int($retryAfter) && $retryAfter > 0) {
                return $retryAfter;
            }
        }

        $message = $e->getMessage();

        if (preg_match('/retry after (\d+)/i', $message, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function setupSignalHandlers(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_async_signals(true);

        $daemon = $this;

        pcntl_signal(SIGTERM, static function () use ($daemon): void {
            $daemon->logger?->info(
                'Received SIGTERM, shutting down...',
            );

            $daemon->stop();
        });

        pcntl_signal(SIGINT, static function () use ($daemon): void {
            $daemon->logger?->info(
                'Received SIGINT, shutting down...',
            );

            $daemon->stop();
        });
    }

    private function shutdown(): void
    {
        $this->logInfo(
            sprintf(
                'Daemon shutting down. Processed: %d, Errors: %d, Concurrent: %d',
                $this->totalProcessed,
                $this->totalErrors,
                $this->concurrentFibers,
            )
        );

        $this->scheduler->drainUntilIdle();
    }

    private function logInfo(string $message): void
    {
        $this->logger?->info(
            sprintf(
                '[TgOutboundDaemon] %s',
                $message,
            )
        );
    }
}
