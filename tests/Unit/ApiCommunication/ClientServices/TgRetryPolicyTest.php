<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;

describe('TgRetryPolicy', function () {
    $policy = new TgRetryPolicy();

    describe('shouldRetry()', function () use ($policy) {
        it('returns false after max attempts', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 3, new \Exception('error')))->toBeFalse();
        });

        it('returns false when no error provided', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1))->toBeFalse();
        });

        it('returns true for timeout errors', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new \Exception('Timed out')))->toBeTrue();
        });

        it('returns true for cURL timeout errors', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new \Exception('cURL error 28')))->toBeTrue();
        });

        it('returns false for rate limit errors', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new \Exception('rate limit exceeded')))->toBeFalse();
        });

        it('returns true for 429 status code', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new \Exception('HTTP 429 Too Many Requests')))->toBeTrue();
        });

        it('returns false for other errors', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new \Exception('Bad Request')))->toBeFalse();
        });
    });

    describe('getDelay()', function () use ($policy) {
        it('returns 1 second for first attempt', function () use ($policy) {
            expect($policy->getDelay(1))->toBe(1);
        });

        it('returns 2 seconds for second attempt', function () use ($policy) {
            expect($policy->getDelay(2))->toBe(2);
        });

        it('returns 4 seconds for third attempt', function () use ($policy) {
            expect($policy->getDelay(3))->toBe(4);
        });
    });

    describe('getMaxAttempts()', function () use ($policy) {
        it('returns 3', function () use ($policy) {
            expect($policy->getMaxAttempts())->toBe(3);
        });
    });
});
