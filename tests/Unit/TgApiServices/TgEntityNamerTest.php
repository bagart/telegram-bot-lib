<?php

declare(strict_types=1);

use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;

describe('TgEntityNamer', function () {
    $namer = new TgEntityNamer();

    describe('name()', function () use ($namer) {
        it('returns username with @ prefix when username exists', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: false,
                firstName: 'John',
                lastName: 'Doe',
                username: 'johndoe',
            );

            expect($namer->name($user))->toBe('@johndoe');
        });

        it('returns username with @ prefix for chat', function () use ($namer) {
            $chat = new ChatTypeDTO(
                id: '123456789',
                type: \BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum::PRIVATE,
                username: 'johndoe',
            );

            expect($namer->name($chat))->toBe('@johndoe');
        });

        it('returns first name when no username', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: false,
                firstName: 'John',
            );

            expect($namer->name($user))->toBe('John');
        });

        it('returns full name when first and last name exist', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: false,
                firstName: 'John',
                lastName: 'Doe',
            );

            expect($namer->name($user))->toBe('John Doe');
        });

        it('returns robot emoji prefix for bot', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: true,
                firstName: 'TestBot',
            );

            expect($namer->name($user))->toContain('🤖');
            expect($namer->name($user))->toContain('TestBot');
        });

        it('returns id in brackets when no name or username', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: false,
                firstName: '',
            );

            expect($namer->name($user))->toBe('[123456789]');
        });

        it('returns id in brackets with robot for bot without name', function () use ($namer) {
            $user = new UserTypeDTO(
                id: '123456789',
                isBot: true,
                firstName: '',
            );

            expect($namer->name($user))->toContain('🤖');
            expect($namer->name($user))->toContain('123456789');
        });
    });
});
