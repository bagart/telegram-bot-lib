<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\OutboundBusinessErrorException;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;

describe('OutboundRetryException', function () {
    it('exposes delaySec and reason as readonly properties', function () {
        $e = new OutboundRetryException(delaySec: 30, reason: 'telegram_rate_limit');

        expect($e->delaySec)->toBe(30)
            ->and($e->reason)->toBe('telegram_rate_limit');
    });

    it('builds a descriptive message including reason and delay', function () {
        $e = new OutboundRetryException(delaySec: 5, reason: 'network_timeout');

        expect($e->getMessage())->toContain('network_timeout')
            ->and($e->getMessage())->toContain('5');
    });

    it('preserves the underlying cause as previous', function () {
        $cause = new RuntimeException('upstream');
        $e = new OutboundRetryException(delaySec: 1, reason: 'r', previous: $cause);

        expect($e->getPrevious())->toBe($cause);
    });

    it('extends RuntimeException (control-flow via exceptions)', function () {
        expect(new OutboundRetryException(1, 'r'))->toBeInstanceOf(RuntimeException::class);
    });
});

describe('OutboundBusinessErrorException', function () {
    it('exposes reason and context', function () {
        $e = new OutboundBusinessErrorException(reason: 'bad_request', context: ['code' => 400]);

        expect($e->reason)->toBe('bad_request')
            ->and($e->context)->toBe(['code' => 400]);
    });

    it('defaults context to empty array', function () {
        $e = new OutboundBusinessErrorException('expired');

        expect($e->context)->toBe([]);
    });

    it('message contains the reason', function () {
        $e = new OutboundBusinessErrorException('lease_expired');

        expect($e->getMessage())->toContain('lease_expired');
    });
});

describe('OutboundSkipException', function () {
    it('exposes reason', function () {
        $e = new OutboundSkipException('max_attempts');

        expect($e->reason)->toBe('max_attempts')
            ->and($e->getMessage())->toBe('max_attempts');
    });

    it('extends RuntimeException', function () {
        expect(new OutboundSkipException('expired'))->toBeInstanceOf(RuntimeException::class);
    });
});
