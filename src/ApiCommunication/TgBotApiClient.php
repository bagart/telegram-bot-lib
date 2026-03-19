<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Exceptions\TgApiNetworkException;
use BAGArt\TelegramBot\ApiCommunication\Exceptions\TgApiRateLimitException;
use BAGArt\TelegramBot\ApiCommunication\Exceptions\TgApiReturnException;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use BAGArt\TelegramBot\Contracts\Infrastructure\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\Infrastructure\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\Infrastructure\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class TgBotApiClient implements TgBotApiClientContract
{
    private readonly ClientInterface $client;

    public function __construct(
        private readonly TgRateLimiterContract $rateLimiter,
        private readonly TgCircuitBreakerContract $circuitBreaker,
        private readonly TgRetryPolicyContract $retryPolicy,
        ?ClientInterface $guzzleClient = null,
    ) {
        $this->client = $guzzleClient ?? new Client();
    }

    /**
     * @see https://core.telegram.org/bots/api
     */
    public function requestAsync(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params,
        int $attempt = 1,
    ): PromiseInterface {
        $origMethod = $method instanceof TgApiEntityEnumContract
            ? $method->name
            : $method;

        return Create::promiseFor(null)
            ->then(
                fn (): bool => $this
                    ->rateLimiter
                    ->acquire($this->getRateLimitKey($token, $origMethod))
                    ?: throw new TgApiRateLimitException($origMethod)
            )
            ->then(
                fn (): bool => $this
                    ->circuitBreaker
                    ->canExecute($origMethod)
                    ?: throw new TgBotTechnicalException(
                        $origMethod,
                        "Circuit breaker open for {$origMethod}"
                    )
            )
            ->then(
                fn (): PromiseInterface => $this->client
                    ->postAsync("{$this->baseUrl($token)}/$origMethod", [
                        'form_params' => $params,
                    ])
            )
            ->then(fn (ResponseInterface $response): array => json_decode(
                (string)$response->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR + JSON_BIGINT_AS_STRING
            ))
            ->then(
                function (array $data) use ($origMethod) {
                    if ($data['ok'] ?? false) {
                        $this->circuitBreaker->recordSuccess($origMethod);
                    } else {
                        $this->circuitBreaker->recordFailure($origMethod);
                    }

                    return $data;
                }
            )
            ->then(
                fn (array $response) => ($response['ok'] ?? false)
                    ? $response
                    : throw new TgApiReturnException(
                        tgEntityName: $origMethod,
                        response: $response
                    ),
                function (Throwable $error) use ($origMethod, $token, $attempt, $params) {
                    if ($error instanceof TelegramBotException) {
                        throw $error;
                    }
                    if ($error instanceof RequestException) {
                        if ($this->retryPolicy->shouldRetry($origMethod, $attempt, $error)) {
                            return Create::promiseFor(null)
                                ->then(fn () => usleep(1000000 * $this->retryPolicy->getDelay($attempt)))
                                ->then(fn (): PromiseInterface => $this->requestAsync(
                                    $token,
                                    $origMethod,
                                    $params,
                                    $attempt + 1
                                ));
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

    private function baseUrl(string $token)
    {
        return "https://api.telegram.org/bot{$token}";
    }
}
