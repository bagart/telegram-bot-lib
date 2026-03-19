<?php

declare(strict_types=1);

use BAGArt\TelegramBot\TgIntegration\BotSecretDTO;

describe('BotSecretDTO', function () {
    $token = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

    describe('constructor', function () use ($token) {
        it('creates DTO with valid token', function () use ($token) {
            $dto = new BotSecretDTO(token: $token);

            expect($dto->token())->toBe($token);
        });

        it('creates DTO with optional secret', function () use ($token) {
            $dto = new BotSecretDTO(token: $token, secret: 'my-secret');

            expect($dto->secret())->toBe('my-secret');
        });

        it('has null secret by default', function () use ($token) {
            $dto = new BotSecretDTO(token: $token);

            expect($dto->secret())->toBeNull();
        });

        it('throws exception for invalid token format', function () {
            expect(fn () => new BotSecretDTO(token: 'invalid'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for token without colon', function () {
            expect(fn () => new BotSecretDTO(token: '123456789ABC'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for token with non-numeric botId', function () {
            expect(fn () => new BotSecretDTO(token: 'abc:ABCdefGHI'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('botId()', function () use ($token) {
        it('extracts botId from token', function () use ($token) {
            $dto = new BotSecretDTO(token: $token);

            expect($dto->botId())->toBe('123456789');
        });
    });

    describe('toArray()', function () use ($token) {
        it('returns empty array for security', function () use ($token) {
            $dto = new BotSecretDTO(token: $token, secret: 'secret');

            expect($dto->toArray())->toBe([]);
        });
    });

    describe('toJson()', function () use ($token) {
        it('returns empty JSON object for security', function () use ($token) {
            $dto = new BotSecretDTO(token: $token, secret: 'secret');

            expect($dto->toJson())->toBe('{}');
        });
    });
});
