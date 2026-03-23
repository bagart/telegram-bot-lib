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
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use GuzzleHttp\Promise\PromiseInterface;

class TgBotApiClient implements TgBotApiClientContract
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
        ?TgBotApiTransportContract $transport = null,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static(
            rateLimiter: new TgRateLimiter($cache),
            circuitBreaker: new TgCircuitBreaker($cache),
            retryPolicy: new TgRetryPolicy(),
            transport: $transport ?? new GuzzleTransport(),
            logger: $logger,
        );
    }

    public function request(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params = [],
    ): array {
        $promise = $this->requestAsync($token, $method, $params);
        while ($promise->getState() === PromiseInterface::PENDING) {
            $this->transport->tick();
        }
        $result = $promise->wait();
        return $result;
    }

    public function requestAsync(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params = [],
        int $attempt = 1,
    ): PromiseInterface {
        $this->logger?->debug('API requestAsync: '.(is_string($method) ? $method : $method->name));
        $origMethod = $method instanceof TgApiEntityEnumContract
            ? $method->name
            : $method;

        $this->logger?->debug('SYNC: circuit breaker');
        $this->circuitBreaker->canExecute($origMethod)
            ?: throw new TgBotTechnicalException(
            $origMethod,
            "Circuit breaker open for {$origMethod}"
        );

        return $this->buildChain($origMethod, $params, $token, $attempt);
    }

    private function buildChain(
        string $origMethod,
        array $params,
        string $token,
        int $attempt,
    ): PromiseInterface {
        return \GuzzleHttp\Promise\Create::promiseFor(null)
            ->then(
                function () use ($origMethod, $token) {
                    $this->logger?->debug('THEN: rate limiter');
                    $this->rateLimiter->acquire($this->getRateLimitKey($token, $origMethod))
                        ?: throw new TgApiRateLimitException($origMethod);
                }
            )
            ->then(
                function () use ($origMethod, $params, $token) {
                    $this->logger?->debug('THEN: transport');
                    return $this->transport->requestAsync($origMethod, $params, $token);
                }
            )
            ->then(
                function (array $data) use ($origMethod) {
                    $this->logger?->debug('THEN: process response');
                    if ($data['ok'] ?? false) {
                        $this->circuitBreaker->recordSuccess($origMethod);
                    } else {
                        $this->circuitBreaker->recordFailure($origMethod);
                    }

                    return ($data['ok'] ?? false)
                        ? $data
                        : throw new TgApiReturnException(
                            tgEntityName: $origMethod,
                            response: new TgApiResponse(
                                $data['ok'] ?? false,
                                [],
                                $data['result'] ?? null
                            )
                        );
                }
            )
            ->then(
                null,
                function (\Throwable|string $error)
                use ($origMethod, $token, $attempt, $params) {
                    if (is_string($error)) {
                        $error = new \RuntimeException($error);
                    }
                    if ($error instanceof TgApiRateLimitException) {
                        if ($this->retryPolicy->shouldRetry($origMethod, $attempt, $error)) {
                            $delay = 1000000 * $this->retryPolicy->getDelay($attempt);
                            $this->logger?->debug('RETRY: rate limit attempt '.($attempt + 1));
                            return \GuzzleHttp\Promise\Create::promiseFor(null)
                                ->then(function () use ($delay) {
                                    usleep($delay);
                                })
                                ->then(function () use ($origMethod, $token, $params, $attempt) {
                                    return $this->requestAsync($token, $origMethod, $params, $attempt + 1);
                                });
                        }
                        throw $error;
                    }
                    if ($error instanceof TelegramBotException) {
                        throw $error;
                    }
                    if ($error instanceof \GuzzleHttp\Exception\RequestException) {
                        if ($this->retryPolicy->shouldRetry($origMethod, $attempt, $error)) {
                            $delay = 1000000 * $this->retryPolicy->getDelay($attempt);
                            $this?->logger->debug('RETRY: attempt '.($attempt + 1));
                            return \GuzzleHttp\Promise\Create::promiseFor(null)
                                ->then(function () use ($delay) {
                                    usleep($delay);
                                })
                                ->then(function () use ($origMethod, $token, $params, $attempt) {
                                    return $this->requestAsync($token, $origMethod, $params, $attempt + 1);
                                });
                        }

                        throw new TgApiNetworkException(
                            tgEntityName: $origMethod,
                            previous: $error,
                        );
                    }

                    throw $error;
                }
            );
    }

    private function getRateLimitKey(
        string $token,
        TgApiEntityEnumContract|string $method,
    ): string {
        return "tg_bot_{$token}_"
            .($method instanceof TgApiEntityEnumContract
                ? $method->name
                : $method);
    }

    public function tick(): void
    {
        $this->transport->tick();
    }
}
