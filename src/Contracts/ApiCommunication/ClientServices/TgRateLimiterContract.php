<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

use BAGArt\ASKClient\Contracts\RateLimiter\RateLimiterContract;

/**
 * Rate limiting contract for Telegram API requests.
 *
 * @see https://core.telegram.org/bots/api#rate-limits
 */
interface TgRateLimiterContract extends RateLimiterContract
{
    public function registerRetryAfter(string $key, float $seconds): void;
}
