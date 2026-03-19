<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgResponseNormalizerContract;
use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiConflictException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use GuzzleHttp\Exception\RequestException;
use Throwable;

final class TgResponseNormalizer implements TgResponseNormalizerContract
{
    public function normalizeResponse(\GuzzleHttp\Psr7\Response|array $response, string $tgMethodName): array
    {
        $response = (array) $response;
        if (($response['ok'] ?? false) === true) {
            return $response;
        }

        $errorCode = $response['error_code'] ?? 0;
        $description = $response['description'] ?? 'Unknown error';

        if ($errorCode === 409 || str_contains($description, 'Conflict: terminated by other getUpdates request')) {
            throw new TgApiConflictException(
                tgMethodName: $tgMethodName,
                message: "Conflict: terminated by other getUpdates request; make sure that only one bot instance is running.",
            );
        }

        if ($errorCode === 429) {
            $retryAfter = null;

            if (isset($response['parameters']['retry_after'])) {
                $retryAfter = (int) $response['parameters']['retry_after'];
            } elseif (preg_match('/retry after (\d+)/i', $description, $matches)) {
                $retryAfter = (int) $matches[1];
            }

            throw new TgApiRateLimitException(
                tgMethodName: $tgMethodName,
                message: $description,
                retryAfter: $retryAfter,
            );
        }

        throw new TgApiReturnException(
            tgMethodName: $tgMethodName,
            response: new TgApiResponse(
                ok: false,
                possibleResultTypes: [],
                result: $response['result'] ?? null,
                errorCode: $errorCode !== 0 ? $errorCode : null,
                retryAfter: isset($response['parameters']['retry_after'])
                    ? (int) $response['parameters']['retry_after']
                    : null,
            ),
        );
    }

    public function normalizeException(
        Throwable|string $exception,
        string $tgMethodName,
    ): Throwable {
        $msg = is_string($exception) ? $exception : $exception->getMessage();
        if ($exception instanceof Throwable && $exception->getPrevious() !== null) {
            $msg .= ' ' . $exception->getPrevious()->getMessage();
        }

        if (
            str_contains($msg, '409 Conflict') ||
            str_contains($msg, '"error_code":409') ||
            str_contains($msg, 'Conflict: terminated by other getUpdates request')
        ) {
            return new TgApiConflictException(
                tgMethodName: $tgMethodName,
                message: "Conflict: terminated by other getUpdates request; make sure that only one bot instance is running.",
                previous: $exception instanceof Throwable ? $exception : null,
            );
        }

        if (is_string($exception)) {
            return new TgApiNetworkException(
                tgMethodName: $tgMethodName,
                message: "Telegram Api Request error with $tgMethodName: $exception",
            );
        }

        if ($exception instanceof TgApiNetworkException) {
            return new TgApiNetworkException(
                tgMethodName: $tgMethodName,
                message: "Telegram Api Request error with $tgMethodName: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        if ($exception instanceof TelegramBotException) {
            return $exception;
        }

        if ($exception instanceof RequestException) {
            return new TgApiNetworkException(
                tgMethodName: $tgMethodName,
                message: "Telegram Api Request error with $tgMethodName: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        throw $exception;
    }
}
