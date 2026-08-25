<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;

/**
 * Webhook transport edge cases, end-to-end through the real route stack
 * (04-qa-and-testing.md §10). The lib's unit tests mock the collaborators;
 * these verify the Laravel wiring: middleware order, status codes and that
 * no update body is accepted without a valid secret.
 */
it('rejects webhook calls from non-Telegram IPs', function () {
    $this->postJson('/tg/', ['update_id' => 1])
        ->assertForbidden();
});

it('requires the secret header once the IP gate passes', function () {
    $this->instance(TelegramIpValidator::class, new class extends TelegramIpValidator
    {
        public function validate(string $ip): bool
        {
            return true; // simulate a call from a Telegram range
        }
    });

    $this->postJson('/tg/', ['update_id' => 1])
        ->assertStatus(401);
});

it('rejects an invalid secret token', function () {
    $this->instance(TelegramIpValidator::class, new class extends TelegramIpValidator
    {
        public function validate(string $ip): bool
        {
            return true;
        }
    });

    $this->postJson('/tg/', ['update_id' => 1], [
        'X-Telegram-Bot-Api-Secret-Token' => 'not-a-real-secret',
    ])->assertStatus(403);
});

it('applies the same gates to the per-bot endpoint', function () {
    // Non-Telegram source IP is rejected before anything else.
    $this->postJson('/tg/tg_webhook/123', ['update_id' => 1])
        ->assertForbidden();
});
