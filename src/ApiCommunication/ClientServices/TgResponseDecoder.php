<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use Throwable;

/**
 * Pure JSON response decoder — no I/O, no side effects.
 * Shared by all transports that talk to Telegram Bot API.
 */
final class TgResponseDecoder
{
    /**
     * @return array<string, mixed>
     *
     * @throws TgApiNetworkException
     */
    public function decode(string $response): array
    {
        try {
            $decoded = json_decode(
                $response,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (Throwable $e) {
            throw new TgApiNetworkException(
                tgMethodName: 'unknown',
                message: "Invalid Telegram JSON response: {$e->getMessage()}",
                previous: $e,
            );
        }

        if (!is_array($decoded)) {
            throw new TgApiNetworkException(
                tgMethodName: 'unknown',
                message: 'Telegram returned non-array JSON response',
            );
        }

        return $decoded;
    }
}
