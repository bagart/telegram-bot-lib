<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

describe('AutoSecretByTokenService', function () {
    $service = new AutoSecretByTokenService();

    describe('secret()', function () use ($service) {
        it('generates correct secret format', function () use ($service) {
            $secret = $service->secret(TOKEN);

            expect($secret)->toMatch('/^\d+:[a-f0-9]{64}$/');
        });

        it('extracts botId from token', function () use ($service) {
            $secret = $service->secret(TOKEN);

            expect($secret)->toStartWith('123456789:');
        });

        it('generates consistent secret for same token', function () use ($service) {
            $secret1 = $service->secret(TOKEN);
            $secret2 = $service->secret(TOKEN);

            expect($secret1)->toBe($secret2);
        });

        it('generates different secrets for different tokens', function () use ($service) {
            $secret1 = $service->secret('111111111:AAAaaaBBBbbbCCCccc');
            $secret2 = $service->secret('222222222:DDDdddEEEeeeFFFfff');

            expect($secret1)->not->toBe($secret2);
        });

        it('throws exception for invalid token format', function () use ($service) {
            expect(fn () => $service->secret('no-colon'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for empty botId', function () use ($service) {
            expect(fn () => $service->secret(':abc'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for non-numeric botId', function () use ($service) {
            expect(fn () => $service->secret('abc:def'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for empty token part', function () use ($service) {
            expect(fn () => $service->secret('abc:'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for empty string', function () use ($service) {
            expect(fn () => $service->secret(''))
                ->toThrow(TgBotInvalidSecretException::class);
        });
    });

    describe('botId()', function () use ($service) {
        it('extracts botId from valid secret', function () use ($service) {
            $secret = $service->secret(TOKEN);

            expect($service->botId($secret))->toBe('123456789');
        });

        it('throws exception for null secret', function () use ($service) {
            expect(fn () => $service->botId(null))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for invalid secret format', function () use ($service) {
            expect(fn () => $service->botId('no-colon'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for empty botId in secret', function () use ($service) {
            expect(fn () => $service->botId(':abc'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for non-numeric botId in secret', function () use ($service) {
            expect(fn () => $service->botId('abc:def'))
                ->toThrow(TgBotInvalidSecretException::class);
        });

        it('throws exception for empty secret part', function () use ($service) {
            expect(fn () => $service->botId('abc:'))
                ->toThrow(TgBotInvalidSecretException::class);
        });
    });
});
