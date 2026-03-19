<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\OutboundTaskState;

describe('OutboundTaskState', function () {
    it('starts as pending with zero attempts', function () {
        $state = new OutboundTaskState();

        expect($state->getStatus())->toBe(OutboundTaskState::STATUS_PENDING)
            ->and($state->getAttempt())->toBe(0)
            ->and($state->getLastError())->toBeNull()
            ->and($state->getErrorContext())->toBeNull()
            ->and($state->isTerminal())->toBeFalse();
    });

    it('transitions pending -> in_progress -> delivered via lifecycle methods', function () {
        $state = new OutboundTaskState();
        $state->markInProgress();

        expect($state->getStatus())->toBe(OutboundTaskState::STATUS_IN_PROGRESS);

        $state->markDelivered();

        expect($state->getStatus())->toBe(OutboundTaskState::STATUS_DELIVERED)
            ->and($state->isTerminal())->toBeTrue()
            ->and($state->getLastError())->toBeNull()
            ->and($state->getErrorContext())->toBeNull();
    });

    it('clears error fields on markDelivered even if previously set', function () {
        $state = new OutboundTaskState(
            status: OutboundTaskState::STATUS_IN_PROGRESS,
            attempt: 3,
            lastError: 'boom',
            errorContext: ['x' => 1],
        );
        $state->markDelivered();

        expect($state->getLastError())->toBeNull()
            ->and($state->getErrorContext())->toBeNull();
    });

    it('markBusinessError stores reason+context and becomes terminal', function () {
        $state = new OutboundTaskState();
        $state->markBusinessError('bad_request', ['code' => 400]);

        expect($state->getStatus())->toBe(OutboundTaskState::STATUS_BUSINESS_ERROR)
            ->and($state->isTerminal())->toBeTrue()
            ->and($state->getLastError())->toBe('bad_request')
            ->and($state->getErrorContext())->toBe(['code' => 400]);
    });

    it('incrementAttempt returns the new value and mutates state', function () {
        $state = new OutboundTaskState();

        expect($state->incrementAttempt())->toBe(1)
            ->and($state->incrementAttempt())->toBe(2)
            ->and($state->getAttempt())->toBe(2);
    });

    it('setRetryContext returns to pending while remembering last error', function () {
        $state = new OutboundTaskState(status: OutboundTaskState::STATUS_IN_PROGRESS, attempt: 2);
        $state->setRetryContext('network_timeout', ['attempt' => 2]);

        expect($state->getStatus())->toBe(OutboundTaskState::STATUS_PENDING)
            ->and($state->getLastError())->toBe('network_timeout')
            ->and($state->getErrorContext())->toBe(['attempt' => 2]);
    });

    it('round-trips through jsonSerialize / fromArray', function () {
        $state = new OutboundTaskState(
            status: OutboundTaskState::STATUS_BUSINESS_ERROR,
            attempt: 4,
            lastError: 'expired',
            errorContext: ['age' => 3601, 'attempts' => 2],
        );
        $restored = OutboundTaskState::fromArray($state->jsonSerialize());

        expect($restored->getStatus())->toBe(OutboundTaskState::STATUS_BUSINESS_ERROR)
            ->and($restored->getAttempt())->toBe(4)
            ->and($restored->getLastError())->toBe('expired')
            ->and($restored->getErrorContext())->toBe(['age' => 3601, 'attempts' => 2]);
    });

    it('restores default-pending state from minimal data', function () {
        $restored = OutboundTaskState::fromArray([]);

        expect($restored->getStatus())->toBe(OutboundTaskState::STATUS_PENDING)
            ->and($restored->getAttempt())->toBe(0)
            ->and($restored->getLastError())->toBeNull();
    });

    it('rejects unknown schemaVersion', function () {
        expect(fn () => OutboundTaskState::fromArray(['schemaVersion' => 7]))
            ->toThrow(RuntimeException::class);
    });
});
