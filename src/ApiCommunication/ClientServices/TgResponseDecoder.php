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
     * Response size cap (03 §61). Bounds json_decode memory blowups from
     * oversized/hostile bodies regardless of which HTTP adapter is in use.
     */
    public const int DEFAULT_MAX_BYTES = 16 * 1024 * 1024;

    public function __construct(
        private readonly int $maxBytes = self::DEFAULT_MAX_BYTES,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws TgApiNetworkException
     */
    public function decode(string $response): array
    {
        if (strlen($response) > $this->maxBytes) {
            throw new TgApiNetworkException(
                tgMethodName: 'unknown',
                message: sprintf(
                    'Telegram response exceeds the %d byte limit (got %d)',
                    $this->maxBytes,
                    strlen($response),
                ),
            );
        }

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
