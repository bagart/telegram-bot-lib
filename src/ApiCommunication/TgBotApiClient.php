<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\ApiCommunication\Transport\GuzzleTransport;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgApiException;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalWithEntityException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Throwable;

final class TgBotApiClient implements TgBotApiClientContract
{
    public function __construct(
        private readonly TgRateLimiterContract $rateLimiter,
        private readonly TgCircuitBreakerContract $circuitBreaker,
        private readonly TgRetryPolicyContract $retryPolicy,
        private readonly TgBotApiTransportContract $transport,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public static function build(
        TgBotCacheWrapper $cache,
        ?TgBotLogWrapper $logger = null,
        ?TgBotApiTransportContract $transport = null,
    ): self {
        return new self(
            rateLimiter: new TgRateLimiter($cache),
            circuitBreaker: new TgCircuitBreaker($cache),
            retryPolicy: new TgRetryPolicy(),
            transport: $transport ?? new GuzzleTransport(),
            logger: $logger,
        );
    }

    public function request(
        string $token,
        TgApiEntityEnumContract|string $tgMethod,
        array $params = [],
    ): array {
        return $this
            ->requestAsync(
                token: $token,
                tgMethod: $tgMethod,
                params: $params,
            )
            ->wait();
    }

    public function requestAsync(
        string $token,
        TgApiEntityEnumContract|string $tgMethod,
        array $params = [],
        int $attempt = 1,
    ): PromiseInterface {
        $tgMethodName = $tgMethod instanceof TgApiEntityEnumContract
            ? $tgMethod->name
            : $tgMethod;

        if ($tgMethodName === 'getUpdates') {
            $params['timeout'] ??= 30;
        }

        $this->logger?->debug(
            sprintf(
                'API requestAsync method=%s attempt=%d',
                $tgMethodName,
                $attempt,
            )
        );

        if (!$this->circuitBreaker->canExecute($tgMethodName)) {
            return Create::rejectionFor(
                new TgBotTechnicalWithEntityException(
                    $tgMethodName,
                    "Circuit breaker open for {$tgMethodName}",
                )
            );
        }

        return Create::promiseFor(null)
            ->then(function () use (
                $token,
                $tgMethodName
            ): void {
                $acquired = $this->rateLimiter->acquire(
                    $this->getRateLimitKey(
                        $token,
                        $tgMethodName,
                    )
                );

                if (!$acquired) {
                    throw new TgApiRateLimitException($tgMethodName);
                }
            })
            ->then(function () use (
                $tgMethodName,
                $params,
                $token
            ): PromiseInterface {
                return $this->transport->requestAsync(
                    $tgMethodName,
                    $params,
                    $token,
                );
            })
            ->then(function (array $response) use (
                $tgMethodName
            ): array {
                if (($response['ok'] ?? false) === true) {
                    $this->circuitBreaker->recordSuccess(
                        $tgMethodName
                    );

                    return $response;
                }

                $this->circuitBreaker->recordFailure(
                    $tgMethodName
                );

                throw new TgApiReturnException(
                    tgEntityName: $tgMethodName,
                    response: new TgApiResponse(
                        ok: (bool)($response['ok'] ?? false),
                        possibleResultTypes: [],
                        result: $response['result'] ?? null,
                    )
                );
            })
            ->otherwise(function (
                Throwable|string $error
            ) use (
                $tgMethodName,
                $token,
                $params,
                $attempt,
            ) {
                $exception = $this->normalizeException(
                    $error,
                    $tgMethodName,
                );

                if (
                    $tgMethodName === 'getUpdates'
                    && !$exception instanceof TgApiRateLimitException
                    && $attempt >= 2
                ) {
                    throw $exception;
                }

                if (
                    $this->shouldRetry(
                        $tgMethodName,
                        $attempt,
                        $exception,
                    )
                ) {
                    $delaySeconds = $this->retryPolicy->getDelay(
                        $attempt
                    );

                    if ($tgMethodName === 'getUpdates') {
                        $delaySeconds = max(
                            3,
                            $delaySeconds,
                        );
                    }

                    usleep(
                        (int)($delaySeconds * 1_000_000)
                    );

                    return $this->requestAsync(
                        token: $token,
                        tgMethod: $tgMethodName,
                        params: $params,
                        attempt: $attempt + 1,
                    );
                }

                if ($exception instanceof RequestException) {
                    throw new TgApiNetworkException(
                        tgEntityName: $tgMethodName,
                        previous: $exception,
                    );
                }

                throw $exception;
            });
    }

    private function shouldRetry(
        string $method,
        int $attempt,
        Throwable $exception,
    ): bool {
        if (
            !$exception instanceof TgApiRateLimitException
            && !$exception instanceof RequestException
        ) {
            return false;
        }

        return $this->retryPolicy->shouldRetry(
            $method,
            $attempt,
            $exception,
        );
    }

    private function normalizeException(
        Throwable|string $error,
        string $method,
    ): Throwable {
        if ($error instanceof TelegramBotException) {
            return $error;
        }

        if ($error instanceof Throwable) {
            return $error;
        }

        return new TgApiException(
            'TgBotApiClient::requestAsync(): '
            . (
                is_scalar($error)
                ? (string)$error
                : "Unknown async failure for {$method}"
            )
        );
    }

    private function getRateLimitKey(
        string $token,
        string $method,
    ): string {
        return "tg_bot_{$token}_{$method}";
    }
}
