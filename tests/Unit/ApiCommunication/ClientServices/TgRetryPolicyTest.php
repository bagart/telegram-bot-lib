<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;

describe('TgRetryPolicy', function () {
    $policy = new TgRetryPolicy();

    describe('shouldRetry()', function () use ($policy) {
        it('returns false after max attempts', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 3, new \Exception('error')))->toBeFalse();
        });

        it('returns false for getUpdates', function () use ($policy) {
            expect($policy->shouldRetry('getUpdates', 1, new TgApiRateLimitException('rate limit')))->toBeFalse();
        });

        it('returns true for TgApiNetworkException timeout', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new TgApiNetworkException('Timed out')))->toBeTrue();
        });

        it('returns true for TgApiNetworkException cURL error', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new TgApiNetworkException('cURL error 28')))->toBeTrue();
        });

        it('returns true for TgApiRateLimitException', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new TgApiRateLimitException('rate limit exceeded')))->toBeTrue();
        });

        it('returns true for 429 status code', function () use ($policy) {
            expect($policy->shouldRetry('sendMessage', 1, new TgApiNetworkException('HTTP 429 Too Many Requests')))->toBeTrue();
        });

        it('returns false for plain Exception', function () use ($policy) {
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
        it('returns 20', function () use ($policy) {
            expect($policy->getMaxAttempts())->toBe(20);
        });
    });
});
