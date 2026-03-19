<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;
use BAGArt\TelegramBot\Outbound\OutboundTask;

/**
 * Spy-middleware: records call order, optionally throws an exception.
 * Marks its entry with a string in the shared $log list.
 */
function recordingMiddleware(string $mark, array &$log): OutboundMiddleware
{
    return new class ($mark, $log) implements OutboundMiddleware {
        public function __construct(
            private readonly string $mark,
            private array &$log,
        ) {
        }

        public function handle(OutboundEnvelope $envelope, Closure $next): void
        {
            $this->log[] = "enter:{$this->mark}";

            try {
                $next($envelope);
                $this->log[] = "exit:{$this->mark}";
            } catch (Throwable $e) {
                $this->log[] = "exit:{$this->mark}";
                throw $e;
            }
        }
    };
}

function makeTaskForPipeline(): OutboundTask
{
    return new OutboundTask(
        id: 't1',
        botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
        dtoClass: 'App\\SendMessage',
        dtoData: ['chat_id' => 1],
    );
}

describe('OutboundPipeline', function () {
    it('calls middleware in declaration order, innermost last', function () {
        $log = [];
        $pipeline = new OutboundPipeline([
            recordingMiddleware('A', $log),
            recordingMiddleware('B', $log),
            recordingMiddleware('C', $log),
        ]);

        $pipeline->execute(new OutboundEnvelope(makeTaskForPipeline(), new \BAGArt\TelegramBot\Outbound\OutboundTaskState()));

        // Outer enters first, exits last (stack nesting).
        expect($log)->toBe([
            'enter:A', 'enter:B', 'enter:C',
            'exit:C', 'exit:B', 'exit:A',
        ]);
    });

    it('executes an empty pipeline without error', function () {
        $pipeline = new OutboundPipeline([]);

        expect(fn () => $pipeline->execute(
            new OutboundEnvelope(makeTaskForPipeline(), new \BAGArt\TelegramBot\Outbound\OutboundTaskState()),
        ))->not->toThrow(Throwable::class);
    });

    it('propagates OutboundSkipException without swallowing', function () {
        $throwing = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new OutboundSkipException('expired');
            }
        };
        $pipeline = new OutboundPipeline([$throwing]);

        expect(fn () => $pipeline->execute(
            new OutboundEnvelope(makeTaskForPipeline(), new \BAGArt\TelegramBot\Outbound\OutboundTaskState()),
        ))->toThrow(OutboundSkipException::class);
    });

    it('propagates OutboundRetryException without swallowing', function () {
        $throwing = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new OutboundRetryException(delaySec: 5, reason: 'rate_limit');
            }
        };
        $pipeline = new OutboundPipeline([$throwing]);

        expect(fn () => $pipeline->execute(
            new OutboundEnvelope(makeTaskForPipeline(), new \BAGArt\TelegramBot\Outbound\OutboundTaskState()),
        ))->toThrow(OutboundRetryException::class);
    });

    it('runs finally (exit markers) on all layers when innermost throws', function () {
        $log = [];
        $throwing = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new OutboundSkipException('boom');
            }
        };
        $pipeline = new OutboundPipeline([
            recordingMiddleware('A', $log),
            $throwing,
            recordingMiddleware('C', $log), // won't be called — B throws before $next
        ]);

        try {
            $pipeline->execute(new OutboundEnvelope(makeTaskForPipeline(), new \BAGArt\TelegramBot\Outbound\OutboundTaskState()));
        } catch (OutboundSkipException) {
            // expected
        }

        // A enters, then B (throwing) throws — C is not called.
        // A still catches in finally and logs exit (our spy middleware).
        expect($log)->toBe(['enter:A', 'exit:A']);
    });
});
