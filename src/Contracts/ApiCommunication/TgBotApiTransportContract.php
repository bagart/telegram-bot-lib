<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * Interface TgBotApiTransportContract
 *
 * Defines the contract for different transport implementations (Guzzle, CurlMulti, etc.)
 */
interface TgBotApiTransportContract
{
    /**
     * Send a request to the Telegram Bot API.
     *
     * @param  string  $tgMethodName  The Telegram method name (e.g., sendMessage)
     * @param  array  $parameters  The method parameters
     * @param  string  $token  The bot token
     *
     * @return array The raw response from Telegram
     * @throws \Throwable
     */
    public function request(string $tgMethodName, array $parameters, string $token): array;

    /**
     * Send an asynchronous request to the Telegram Bot API.
     *
     * @param  string  $tgMethodName  The Telegram method name (e.g., sendMessage)
     * @param  array  $parameters  The method parameters
     * @param  string  $token  The bot token
     *
     * @return PromiseInterface A promise that resolves to the array response
     * @throws \Throwable
     */
    public function requestAsync(string $tgMethodName, array $parameters, string $token): PromiseInterface;

    public function tick(): void;

    public function hasActiveHandles(): bool;
}
