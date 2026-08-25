<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgExecutionContext;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgOperation;
use BAGArt\TelegramBot\ApiCommunication\Middleware\CircuitBreakerMiddleware;
use BAGArt\TelegramBot\ApiCommunication\Middleware\RateLimitMiddleware;
use BAGArt\TelegramBot\ApiCommunication\Middleware\RetryMiddleware;
use BAGArt\TelegramBot\ApiCommunication\Middleware\TgMiddlewareContract;
use BAGArt\TelegramBot\ApiCommunication\Middleware\TgMiddlewarePipeline;
use BAGArt\TelegramBot\ApiCommunication\Middleware\TgNextHandler;
use BAGArt\TelegramBot\ApiCommunication\Middleware\TgNextHandlerContract;
use BAGArt\TelegramBot\ApiCommunication\Middleware\TgTerminalHandler;

/**
 * Fixture: records enter/exit order around $next and captures the received handler type.
 */
final class TgMwLogMiddleware implements TgMiddlewareContract
{
    public function __construct(
        private readonly stdClass $log,
        private readonly string $name,
    ) {}

    public function handle(TgEnvelope $env, TgNextHandlerContract $next): mixed
    {
        $this->log->entries[] = "enter:{$this->name}";
        $this->log->handlers[$this->name] = $next;
        $result = $next->handle($env);
        $this->log->entries[] = "exit:{$this->name}";

        return $result;
    }
}

/**
 * Fixture: returns a value without delegating to $next.
 */
final class TgMwShortCircuitMiddleware implements TgMiddlewareContract
{
    public function __construct(
        private readonly mixed $value,
        private readonly stdClass $log,
    ) {}

    public function handle(TgEnvelope $env, TgNextHandlerContract $next): mixed
    {
        unset($env, $next);

        $this->log->entries[] = 'short-circuit';

        return $this->value;
    }
}

describe('TgMiddlewarePipeline', function () {
    it('wraps the core in list order: first middleware is outermost', function () {
        $log = new stdClass;
        $log->entries = [];
        $log->handlers = [];

        $pipeline = new TgMiddlewarePipeline;
        $pipeline
            ->add(new TgMwLogMiddleware($log, 'a'))
            ->add(new TgMwLogMiddleware($log, 'b'));

        $result = $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-1')),
            function (TgEnvelope $env) use ($log): string {
                $log->entries[] = 'core';

                return $env->operation->method;
            },
        );

        expect($result)->toBe('getMe');
        expect($log->entries)->toBe([
            'enter:a',
            'enter:b',
            'core',
            'exit:b',
            'exit:a',
        ]);
    });

    it('passes typed next-handlers implementing the contract', function () {
        $log = new stdClass;
        $log->entries = [];
        $log->handlers = [];

        $pipeline = new TgMiddlewarePipeline;
        $pipeline
            ->add(new TgMwLogMiddleware($log, 'outer'))
            ->add(new TgMwLogMiddleware($log, 'inner'));

        $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-2')),
            fn (TgEnvelope $env): null => null,
        );

        expect($log->handlers['outer'])->toBeInstanceOf(TgNextHandlerContract::class);
        expect($log->handlers['outer'])->toBeInstanceOf(TgNextHandler::class);
        expect($log->handlers['inner'])->toBeInstanceOf(TgTerminalHandler::class);
    });

    it('allows a middleware to short-circuit without calling $next', function () {
        $log = new stdClass;
        $log->entries = [];
        $log->handlers = [];

        $pipeline = new TgMiddlewarePipeline;
        $pipeline
            ->add(new TgMwShortCircuitMiddleware('cached', $log))
            ->add(new TgMwLogMiddleware($log, 'never'));

        $result = $pipeline->execute(
            new TgEnvelope(new TgOperation('sendMessage'), new TgExecutionContext('t-3')),
            fn (TgEnvelope $env): string => 'core-ran',
        );

        expect($result)->toBe('cached');
        expect($log->entries)->toBe(['short-circuit']);
    });

    it('propagates envelope mutations downstream to the core', function () {
        $pipeline = new TgMiddlewarePipeline;
        $pipeline->add(new class implements TgMiddlewareContract
        {
            public function handle(TgEnvelope $env, TgNextHandlerContract $next): mixed
            {
                $env->context->tags[] = 'tagged';

                return $next->handle($env);
            }
        });

        $seenTags = null;
        $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-4')),
            function (TgEnvelope $env) use (&$seenTags): array {
                $seenTags = $env->context->tags;

                return [];
            },
        );

        expect($seenTags)->toBe(['tagged']);
    });

    it('retries through the built-in RetryMiddleware before failing', function () {
        $attempts = 0;

        $pipeline = new TgMiddlewarePipeline;
        $pipeline->add(new RetryMiddleware(maxRetries: 2));

        $result = $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-5')),
            function () use (&$attempts): string {
                $attempts++;

                if ($attempts < 3) {
                    throw new RuntimeException("attempt {$attempts}");
                }

                return 'ok';
            },
        );

        expect($result)->toBe('ok');
        expect($attempts)->toBe(3);
    });

    it('rethrows when the retry budget is exhausted', function () {
        $pipeline = new TgMiddlewarePipeline;
        $pipeline->add(new RetryMiddleware(maxRetries: 1));

        $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-6')),
            fn (): string => throw new LogicException('permanent'),
        );
    })->throws(LogicException::class);

    it('accepts the placeholder stack middlewares as pass-throughs', function () {
        $pipeline = new TgMiddlewarePipeline;
        $pipeline
            ->add(new RateLimitMiddleware)
            ->add(new CircuitBreakerMiddleware);

        $result = $pipeline->execute(
            new TgEnvelope(new TgOperation('getMe'), new TgExecutionContext('t-7')),
            fn (TgEnvelope $env): string => $env->context->traceId,
        );

        expect($result)->toBe('t-7');
    });
});
