<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use Throwable;

interface TgResponseNormalizerContract
{
    /**
     * Normalize the raw Telegram API response.
     *
     * Returns the response array on success ({@see ok} is {@see true}).
     *
     * @throws \BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException
     * @throws \BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException
     */
    public function normalizeResponse(array $response, string $tgMethodName): array;

    /**
     * Normalize a transport-level exception into a typed {@see TelegramBotException} descendant.
     *
     * @return \Throwable The normalized exception. Callers MUST throw the returned value;
     *         the method does not throw itself — it returns the exception to be thrown
     *         by the promise {@see otherwise} handler.
     */
    public function normalizeException(Throwable|string $exception, string $tgMethodName): Throwable;
}
