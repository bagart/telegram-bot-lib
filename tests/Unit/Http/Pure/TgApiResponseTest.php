<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Http\Pure\TgApiResponse;

describe('TgApiResponse', function () {
    describe('constructor', function () {
        it('creates response with boolean result', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: ['bool'],
                result: true,
            );

            expect($response->ok)->toBeTrue()
                ->and($response->possibleResultTypes)->toBe(['bool'])
                ->and($response->result)->toBeTrue();
        });

        it('creates response with string result', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: ['string'],
                result: 'https://example.com/invoice',
            );

            expect($response->ok)->toBeTrue()
                ->and($response->result)->toBe('https://example.com/invoice');
        });

        it('creates response with int result', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: ['int'],
                result: 42,
            );

            expect($response->ok)->toBeTrue()
                ->and($response->result)->toBe(42);
        });

        it('creates response with null result', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: [],
                result: null,
            );

            expect($response->ok)->toBeTrue()
                ->and($response->result)->toBeNull();
        });

        it('creates response with array result', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: ['array'],
                result: [1, 2, 3],
            );

            expect($response->ok)->toBeTrue()
                ->and($response->result)->toBe([1, 2, 3]);
        });

        it('has readonly properties', function () {
            $response = new TgApiResponse(
                ok: true,
                possibleResultTypes: ['bool'],
                result: true,
            );

            expect($response->ok)->toBeTrue();
            expect($response->possibleResultTypes)->toBe(['bool']);
            expect($response->result)->toBeTrue();
        });
    });
});
