<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\Ordering\DefaultOrderingStrategy;
use BAGArt\TelegramBot\Outbound\OutboundTask;

describe('DefaultOrderingStrategy', function () {
    $strategy = new DefaultOrderingStrategy();

    it('prefers the explicit orderingKey when set', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: ['chat_id' => 99],
            orderingKey: '99:session-42',
        );

        expect($strategy->keyFor($task))->toBe('99:session-42');
    });

    it('falls back to chat_id from dtoData when orderingKey is null', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: ['chat_id' => 12345, 'text' => 'hi'],
        );

        expect($strategy->keyFor($task))->toBe('12345');
    });

    it('returns null for broadcast (no orderingKey, no chat_id)', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: ['offset' => 0],
        );

        expect($strategy->keyFor($task))->toBeNull();
    });

    it('explicit null orderingKey + present chat_id still resolves to chat_id', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: ['chat_id' => 7],
            orderingKey: null,
        );

        expect($strategy->keyFor($task))->toBe('7');
    });

    it('coerces non-string chat_id to string', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: ['chat_id' => 55],
        );

        expect($strategy->keyFor($task))->toBe('55');
    });

    it('explicit orderingKey wins even when chat_id is absent', function () use ($strategy) {
        $task = new OutboundTask(
            id: 't',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: [],
            orderingKey: 'custom-key',
        );

        expect($strategy->keyFor($task))->toBe('custom-key');
    });
});
