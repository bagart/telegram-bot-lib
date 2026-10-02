<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgPollerConfig;

describe('TgPollerConfig allowed updates (ADR-002 layer 1)', function () {
    it('subscribes chat-member update types by default', function () {
        expect((new TgPollerConfig())->allowedUpdates)
            ->toBe([
                'message',
                'callback_query',
                'edited_channel_post',
                'chat_member',
                'my_chat_member',
            ]);
    });

    it('exposes the default subscription as a constant for other subscription paths', function () {
        expect(TgPollerConfig::DEFAULT_ALLOWED_UPDATES)
            ->toContain('chat_member')
            ->toContain('my_chat_member')
            ->toBe((new TgPollerConfig())->allowedUpdates);
    });

    it('keeps an explicitly supplied subscription untouched', function () {
        expect((new TgPollerConfig(allowedUpdates: ['message']))->allowedUpdates)
            ->toBe(['message']);
    });
});
