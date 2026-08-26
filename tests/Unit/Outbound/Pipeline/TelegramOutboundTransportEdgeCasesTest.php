<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\Transport\HttpTransportContract;
use BAGArt\ASKClient\Dto\ASKHttpRequest;
use BAGArt\ASKClient\Dto\ASKHttpResponse;
use BAGArt\ASKClient\Exceptions\ASKNetworkException;
use BAGArt\AsyncKernel\Contracts\ASKPromiseContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Promise\ASKPromise;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRequestFactory;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgResponseDecoder;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundNextHandlerContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Outbound\OutboundBusinessErrorException;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TelegramOutboundExecutor;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;

/**
 * Telegram transport edge cases through the outbound send path
 * (04-qa-and-testing.md §10): malformed/empty/non-array bodies, 5xx payloads,
 * connection reset / DNS failure / read timeout surfaced by the HTTP layer,
 * oversized responses, and request timeout propagation.
 *
 * Unlike TelegramOutboundExecutorTest these go through the REAL
 * TgBotApiDTOClient (transport → decoder → response parser), so the whole
 * wire-format pipeline is exercised, only the socket is faked.
 */

/**
 * Serves canned RAW bodies or rejects with a network-layer Throwable —
 * mirrors what curl surfaces for reset/DNS/timeout failures.
 */
class EdgeRawBodyTransport implements HttpTransportContract
{
    public ?string $rawBody = null;

    public ?ASKNetworkException $networkError = null;

    public ?ASKHttpRequest $lastRequest = null;

    public function respondWith(string $rawBody): void
    {
        $this->rawBody = $rawBody;
        $this->networkError = null;
    }

    public function failWith(ASKNetworkException $error): void
    {
        $this->networkError = $error;
        $this->rawBody = null;
    }

    public function request(ASKHttpRequest $request): ASKHttpResponse
    {
        return $this->requestAsync($request)->await();
    }

    public function requestAsync(ASKHttpRequest $request): ASKPromiseContract
    {
        $this->lastRequest = $request;

        if ($this->networkError !== null) {
            return ASKPromise::rejected($this->networkError);
        }

        return ASKPromise::resolved(ASKHttpResponse::fromString($this->rawBody ?? ''));
    }

    /** @return ASKTickableContract[] */
    public function tickable(): array
    {
        return [];
    }
}

class EdgeRateLimiter implements OutboundRateLimiterContract
{
    /** @var array<string, float> */
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

function makeEdgeTask(): OutboundTask
{
    return new OutboundTask(
        id: 't-edge',
        botConfig: new TgBotConfig(token: 'edge:token', botId: 'bot-edge'),
        dtoClass: SendMessageMethodDTO::class,
        dtoData: ['chat_id' => 123, 'text' => 'hi'],
        orderingKey: '123',
    );
}

function makeEdgeExecutor(EdgeRawBodyTransport $http, EdgeRateLimiter $limiter = new EdgeRateLimiter()): TelegramOutboundExecutor
{
    $mapper = new TgApiDTOMapper(TgEntityToDTORegistry::build());
    $dtoClient = TgBotApiDTOClient::build(new TgBotApiTransport($http));

    return new TelegramOutboundExecutor($dtoClient, $limiter, $mapper);
}

/**
 * Captures the control exception thrown by the executor for one raw scenario.
 */
function edgeSend(TelegramOutboundExecutor $executor): Throwable
{
    $neverNext = new class () implements OutboundNextHandlerContract {
        public function handle(OutboundEnvelope $envelope): void
        {
            throw new LogicException('executor is final — $next must never be called');
        }
    };

    try {
        $executor->handle(
            new OutboundEnvelope(makeEdgeTask(), new OutboundTaskState()),
            $neverNext,
        );
    } catch (Throwable $e) {
        return $e;
    }

    throw new LogicException('expected executor to throw');
}

describe('Telegram outbound transport edge cases', function () {
    it('retries (not DLQ) when a reverse proxy returns an HTML error page', function () {
        $http = new EdgeRawBodyTransport();
        $http->respondWith('<html><body><h1>502 Bad Gateway</h1></body></html>');
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('network_timeout')
            ->and($e->getPrevious())->toBeInstanceOf(TgApiNetworkException::class);
    });

    it('retries on an empty response body', function () {
        $http = new EdgeRawBodyTransport();
        $http->respondWith('');
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('network_timeout');
    });

    it('retries when the body is valid JSON but not an object/array', function () {
        $http = new EdgeRawBodyTransport();
        $http->respondWith('42');
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('network_timeout');
    });

    it('retries unknown_transport_error on a 5xx-shaped Telegram payload', function () {
        $http = new EdgeRawBodyTransport();
        $http->respondWith('{"ok":false,"error_code":502,"description":"Bad Gateway"}');
        $e = edgeSend(makeEdgeExecutor($http));

        // Server-side failure is transient: retry, never business-error/DLQ.
        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('unknown_transport_error')
            ->and($e)->not->toBeInstanceOf(OutboundBusinessErrorException::class);
    });

    it('does not honor retry_after outside of 429', function () {
        $http = new EdgeRawBodyTransport();
        $http->respondWith('{"ok":false,"error_code":500,"parameters":{"retry_after":999}}');
        $limiter = new EdgeRateLimiter();
        $e = edgeSend(makeEdgeExecutor($http, $limiter));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('unknown_transport_error')
            ->and($e->delaySec)->not->toBe(999)
            ->and($limiter->registered)->toBe([]);
    });

    it('classifies connection reset as retry', function () {
        $http = new EdgeRawBodyTransport();
        $http->failWith(new ASKNetworkException('Recv failure: Connection reset by peer'));
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('unknown_transport_error');
    });

    it('classifies DNS resolution failure as retry', function () {
        $http = new EdgeRawBodyTransport();
        $http->failWith(new ASKNetworkException('Could not resolve host: api.telegram.org'));
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('unknown_transport_error');
    });

    it('classifies read timeout as retry', function () {
        $http = new EdgeRawBodyTransport();
        $http->failWith(new ASKNetworkException('Operation timed out after 30000 milliseconds'));
        $e = edgeSend(makeEdgeExecutor($http));

        expect($e)->toBeInstanceOf(OutboundRetryException::class)
            ->and($e->reason)->toBe('unknown_transport_error');
    });

    it('decodes a multi-megabyte valid response without truncation', function () {
        $updates = [];
        for ($i = 0; $i < 50000; $i++) {
            $updates[] = ['update_id' => $i, 'message' => ['message_id' => $i, 'text' => str_repeat('x', 32)]];
        }
        $payload = json_encode(['ok' => true, 'result' => $updates], JSON_THROW_ON_ERROR);
        expect(strlen($payload))->toBeGreaterThan(1000000);

        $decoded = (new TgResponseDecoder())->decode($payload);

        expect(count($decoded['result']))->toBe(50000)
            ->and($decoded['result'][49999]['message']['text'])->toBe(str_repeat('x', 32));
    });

    it('rejects bodies over the configured size cap (03 §61)', function () {
        $decoder = new TgResponseDecoder(maxBytes: 1024);
        $oversized = str_pad('{"ok":true,"result":', 2048, 'a').'}';

        try {
            $decoder->decode($oversized);
            expect('should have thrown')->toBe('threw');
        } catch (TgApiNetworkException $e) {
            expect($e->getMessage())->toContain('exceeds the 1024 byte limit');
        }
    });
});

describe('TgRequestFactory timeout propagation', function () {
    it('propagates an explicit timeout as CURLOPT_TIMEOUT', function () {
        $factory = new TgRequestFactory();
        $request = $factory->make(
            tgMethodName: 'sendMessage',
            parameters: ['chat_id' => 1],
            botConfig: new TgBotConfig(token: 'edge:token', botId: 'bot-edge'),
            timeout: 42,
        );

        expect($request->curlOptions[CURLOPT_TIMEOUT])->toBe(42);
    });

    it('leaves the adapter default untouched when no timeout is given', function () {
        $factory = new TgRequestFactory();
        $request = $factory->make(
            tgMethodName: 'getMe',
            parameters: [],
            botConfig: new TgBotConfig(token: 'edge:token', botId: 'bot-edge'),
        );

        expect($request->curlOptions)->not->toHaveKey(CURLOPT_TIMEOUT);
    });

    it('pins requests to the official TLS endpoint (03 §61)', function () {
        $factory = new TgRequestFactory();
        $request = $factory->make(
            tgMethodName: 'sendMessage',
            parameters: ['chat_id' => 1],
            botConfig: new TgBotConfig(token: 'edge:token', botId: 'bot-edge'),
        );

        expect($request->url)->toStartWith('https://api.telegram.org/bot');
    });
});
