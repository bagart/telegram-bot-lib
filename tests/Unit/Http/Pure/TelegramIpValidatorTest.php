<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;

describe('TelegramIpValidator', function () {
    $validator = new TelegramIpValidator();

    describe('validate()', function () use ($validator) {
        it('allows Telegram IP 149.154.160.1', function () use ($validator) {
            expect($validator->validate('149.154.160.1'))->toBeTrue();
        });

        it('allows Telegram IP 149.154.165.255', function () use ($validator) {
            expect($validator->validate('149.154.165.255'))->toBeTrue();
        });

        it('allows Telegram IP 91.108.4.1', function () use ($validator) {
            expect($validator->validate('91.108.4.1'))->toBeTrue();
        });

        it('allows Telegram IP 91.108.7.255', function () use ($validator) {
            expect($validator->validate('91.108.7.255'))->toBeTrue();
        });

        it('rejects Google DNS 8.8.8.8', function () use ($validator) {
            expect($validator->validate('8.8.8.8'))->toBeFalse();
        });

        it('rejects Cloudflare DNS 1.1.1.1', function () use ($validator) {
            expect($validator->validate('1.1.1.1'))->toBeFalse();
        });

        it('rejects private IP 192.168.1.1', function () use ($validator) {
            expect($validator->validate('192.168.1.1'))->toBeFalse();
        });

        it('rejects private IP 10.0.0.1', function () use ($validator) {
            expect($validator->validate('10.0.0.1'))->toBeFalse();
        });

        it('rejects localhost 127.0.0.1', function () use ($validator) {
            expect($validator->validate('127.0.0.1'))->toBeFalse();
        });

        it('returns false for invalid IP format', function () use ($validator) {
            expect($validator->validate('not-an-ip'))->toBeFalse();
        });

        it('returns false for empty string', function () use ($validator) {
            expect($validator->validate(''))->toBeFalse();
        });
    });
});
