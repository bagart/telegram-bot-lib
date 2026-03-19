<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiConflictException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgBadRequestException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Outbound\OutboundBusinessErrorException;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TelegramOutboundExecutor;

// ----- Hand-rolled fakes -----

class ExecutorDtoClient implements TgBotApiDTOClientContract
{
    /** @var callable|null (TgBotConfig, TgApiMethodDTOContract) => TgApiResponse | throws */
    public $requestHandler = null;

    public ?TgBotConfig $lastBotConfig = null;

    public ?TgApiMethodDTOContract $lastDto = null;

    public function request(TgBotConfig $botConfig, TgApiMethodDTOContract $dto, ?int $timeout = null): TgApiResponse
    {
        $this->lastBotConfig = $botConfig;
        $this->lastDto = $dto;

        return ($this->requestHandler)($botConfig, $dto);
    }

    public function requestAsync(TgBotConfig $botConfig, TgApiMethodDTOContract $dto, ?int $timeout = null): ASKFutureContract
    {
        throw new RuntimeException('not used in executor tests');
    }

    public function tickable(): array
    {
        return [];
    }
}

class ExecutorMapper implements TgApiDTOMapperContract
{
    public ?string $lastDtoClass = null;

    public ?array $lastData = null;

    public function fromArray(string|BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract|BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract $entity, array $data): BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract
    {
        $this->lastDtoClass = is_string($entity) ? $entity : $entity::class;
        $this->lastData = $data;

        // Stub DTO — executor does not use its content, only passes to dtoClient.
        return new class () implements TgApiMethodDTOContract {
            public static function getReturnTypes(): array
            {
                return [];
            }

            public static function tgApiEntity(): BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract
            {
                throw new RuntimeException('not used');
            }

            public static function tgEntityScope(): BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract
            {
                throw new RuntimeException('not used');
            }

            public static function tgPropertyMetas(): array
            {
                return [];
            }
        };
    }

    public function toArray(BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract $dto): array
    {
        return [];
    }
}

class ExecutorRateLimiter implements OutboundRateLimiterContract
{
    /** @var array<string,float> key → registered seconds */
    public array $registered = [];

    public function getRetryDelay(string $key): float
    {
        return 0.0;
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
        $this->registered[$key] = $seconds;
    }

    public function markSent(string $key): void
    {
    }
}

// ----- Helpers -----

function makeExecutorTask(): OutboundTask
{
    return new OutboundTask(
        id: 't1',
        botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
        dtoClass: 'App\\SendMessageDTO',
        dtoData: ['chat_id' => 123, 'text' => 'hi'],
        orderingKey: '123',
    );
}

function makeExecutor(
    ExecutorDtoClient $client,
    ExecutorRateLimiter $limiter = new ExecutorRateLimiter(),
    ExecutorMapper $mapper = new ExecutorMapper(),
): TelegramOutboundExecutor {
    return new TelegramOutboundExecutor($client, $limiter, $mapper);
}

/**
 * Self-contained $next spy: Closure + mutable $called flag.
 * Returns [closure, box]; box->called is checked in the test.
 * No by-ref arguments (Pest does not pass references in helpers).
 *
 * @return array{0: Closure, 1: object}
 */
function makeExecutorSpy(): array
{
    $box = new class () {
        public bool $called = false;
    };

    $closure = static function (OutboundEnvelope $e) use ($box): void {
        $box->called = true;
    };

    return [$closure, $box];
}

describe('TelegramOutboundExecutor', function () {
    it('resolves the DTO, resolves the token, and calls dtoClient->request on success', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn (TgBotConfig $c, TgApiMethodDTOContract $d) => new TgApiResponse(true, [], null);
        $mapper = new ExecutorMapper();
        $middleware = makeExecutor($client, mapper: $mapper);

        [$spy, $box] = makeExecutorSpy();
        $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), $spy);

        expect($box->called)->toBeFalse() // executor is final — $next is NOT called.
            ->and($mapper->lastDtoClass)->toBe('App\\SendMessageDTO')
            ->and($mapper->lastData)->toBe(['chat_id' => 123, 'text' => 'hi'])
            ->and($client->lastBotConfig->token)->toBe('test:token');
    });

    it('classifies 429 as telegram_rate_limit retry with retryAfter from the exception', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiRateLimitException('sendMessage', retryAfter: 42);
        $limiter = new ExecutorRateLimiter();
        $middleware = makeExecutor($client, limiter: $limiter);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
            expect('should have thrown')->toBe('threw');
        } catch (OutboundRetryException $e) {
            expect($e->reason)->toBe('telegram_rate_limit')
                ->and($e->delaySec)->toBe(42);
        }
    });

    it('calls registerRetryAfter on 429 — fixes the dead-code bug', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiRateLimitException('sendMessage', retryAfter: 42);
        $limiter = new ExecutorRateLimiter();
        $middleware = makeExecutor($client, limiter: $limiter);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException) {
            // expected
        }

        // registerRetryAfter called with the same key as buildKey in RateLimitMiddleware.
        expect($limiter->registered)->toHaveKey('bot1:SendMessageDTO:123')
            ->and($limiter->registered['bot1:SendMessageDTO:123'])->toBe(42.0);
    });

    it('defaults retryAfter to 30 when the exception has none', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiRateLimitException('sendMessage'); // retryAfter = null
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->delaySec)->toBe(30);
        }
    });

    it('classifies 409 conflict as retry with delay 5', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiConflictException('getUpdates');
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->reason)->toBe('telegram_conflict')
                ->and($e->delaySec)->toBe(5);
        }
    });

    it('classifies network error as retry with delay 10', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiNetworkException('sendMessage');
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->reason)->toBe('network_timeout')
                ->and($e->delaySec)->toBe(10);
        }
    });

    it('classifies 400 bad request as business error (DLQ)', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgBadRequestException('Chat not found');
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundBusinessErrorException $e) {
            expect($e->reason)->toBe('bad_request')
                ->and($e->context['msg'])->toBe('Chat not found');
        }
    });

    it('classifies unknown exceptions as retry (best-effort, not DLQ)', function () {
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new RuntimeException('mystery');
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->reason)->toBe('unknown_transport_error')
                ->and($e->delaySec)->toBe(10);
        }
    });

    it('preserves the original exception as previous in the wrapping control-flow exception', function () {
        $original = new TgApiNetworkException('sendMessage', 'timeout');
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw $original;
        $middleware = makeExecutor($client);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->getPrevious())->toBe($original);
        }
    });

    it('uses the same key format as RateLimitMiddleware (single bucket)', function () {
        // Sanity: both Executor.registerRetryAfter and RateLimitMiddleware.getRetryDelay
        // should build the same key for one envelope.
        $client = new ExecutorDtoClient();
        $client->requestHandler = fn () => throw new TgApiRateLimitException('sendMessage', retryAfter: 1);
        $limiter = new ExecutorRateLimiter();
        $middleware = makeExecutor($client, limiter: $limiter);

        try {
            $middleware->handle(new OutboundEnvelope(makeExecutorTask(), new OutboundTaskState()), makeExecutorSpy()[0]);
        } catch (OutboundRetryException) {
            // expected
        }

        // bot1:SendMessageDTO:123 — same format as in RateLimitMiddlewareTest.
        expect($limiter->registered)->toHaveKey('bot1:SendMessageDTO:123');
    });
});
