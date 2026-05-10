<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transport;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Exceptions\TgTransportException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use Throwable;

final class TgCurlMultiTransport implements TgBotApiTransportContract
{
    /**
     * @var array<int, \CurlHandle>
     */
    private array $handles = [];

    /**
     * @var array<int, Promise>
     */
    private array $pendingPromises = [];

    private const WAIT_SLEEP_MICROSECONDS = 10_000;
    private const MAX_WAIT_ITERATIONS = 12_000;

    /**
     * Normal API request timeout.
     */
    private const DEFAULT_TOTAL_TIMEOUT = 60;

    /**
     * DNS + TCP connect timeout.
     */
    private const DEFAULT_CONNECT_TIMEOUT = 15;

    /**
     * Long polling timeout for getUpdates.
     *
     * Telegram waits on server side.
     */
    private const GET_UPDATES_TIMEOUT = 65;

    /**
     * Connect timeout for polling.
     */
    private const GET_UPDATES_CONNECT_TIMEOUT = 20;

    private readonly TgCurlMultiHandle $multiHandle;
    public function __construct(
        ?TgCurlMultiHandle $multiHandle = null,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
        $this->multiHandle = $multiHandle ?? new TgCurlMultiHandle();
    }

    public function request(
        string $tgMethodName,
        array $parameters,
        string $token,
    ): array {
        $promise = $this->requestAsync(
            $tgMethodName,
            $parameters,
            $token
        );

        $this->waitUntilSettled($promise);

        /** @var array<string, mixed> $result */
        $result = $promise->wait();

        return $result;
    }

    public function requestAsync(
        string $tgMethodName,
        array $parameters,
        string $token,
    ): PromiseInterface {
        /**
         * Critical:
         * transport-level long polling protection.
         */
        if ($tgMethodName === 'getUpdates') {
            $parameters['timeout'] ??= 30;
        }

        $request = new TgCurlRequest(
            url: sprintf(
                'https://api.telegram.org/bot%s/%s',
                $token,
                $tgMethodName
            ),
            method: 'POST',
            parameters: $parameters,
        );

        /**
         * REAL FIX:
         * apply proper curl timeouts.
         */
        $this->configureTimeouts(
            $request,
            $tgMethodName
        );

        $ch = $this->multiHandle->add($request);
        $handleId = spl_object_id($ch);

        $this->handles[$handleId] = $ch;

        $promise = new Promise(
            function () use (&$promise): void {
                $this->waitUntilSettled($promise);
            }
        );

        $this->pendingPromises[$handleId] = $promise;

        return $promise;
    }

    public function tick(): void
    {
        $active = 0;

        $this->multiHandle->execute($active);

        foreach (
            $this->multiHandle->readCompletedHandles() as $ch
        ) {
            $this->processCompletedHandle($ch);
        }

        /**
         * Required:
         * flush then()/fiber callbacks
         */
        while (!Utils::queue()->isEmpty()) {
            Utils::queue()->run();
        }
    }

    public function hasActiveHandles(): bool
    {
        return $this->multiHandle->hasActive();
    }

    private function waitUntilSettled(
        PromiseInterface $promise
    ): void {
        $iterations = 0;

        while (
            $promise->getState()
            === PromiseInterface::PENDING
        ) {
            $this->tick();

            if (
                $promise->getState()
                !== PromiseInterface::PENDING
            ) {
                break;
            }

            usleep(self::WAIT_SLEEP_MICROSECONDS);

            ++$iterations;

            /**
             * 12_000 * 10ms = ~120 sec
             *
             * Required for stable long polling.
             */
            if ($iterations > self::MAX_WAIT_ITERATIONS) {
                throw new TgTransportException(
                    sprintf(
                        'Curl transport timeout after %d iterations',
                        self::MAX_WAIT_ITERATIONS
                    )
                );
            }
        }
    }

    private function processCompletedHandle(
        \CurlHandle $ch
    ): void {
        $handleId = spl_object_id($ch);

        if (!isset($this->handles[$handleId])) {
            return;
        }

        $response = curl_multi_getcontent($ch);
        $error = curl_error($ch);

        try {
            $this->multiHandle->remove($ch);
        } finally {
            curl_close($ch);
            unset($this->handles[$handleId]);
        }

        if (!isset($this->pendingPromises[$handleId])) {
            return;
        }

        $promise = $this->pendingPromises[$handleId];
        unset($this->pendingPromises[$handleId]);

        if (
            $promise->getState()
            !== PromiseInterface::PENDING
        ) {
            return;
        }

        try {
            if ($error !== '') {
                $promise->reject(
                    new TgTransportException(
                        'Curl request failed: ' . $error
                    )
                );

                return;
            }

            if ($response === false) {
                $promise->reject(
                    new TgTransportException(
                        'Failed to read curl response'
                    )
                );

                return;
            }

            $decoded = $this->decodeResponse($response);

            $promise->resolve($decoded);
        } catch (Throwable $e) {
            if (
                $promise->getState()
                === PromiseInterface::PENDING
            ) {
                $promise->reject($e);
            }
        }
    }

    private function configureTimeouts(
        TgCurlRequest $request,
        string $tgMethodName,
    ): void {
        if ($tgMethodName === 'getUpdates') {
            $request->setCurlOption(
                CURLOPT_CONNECTTIMEOUT,
                self::GET_UPDATES_CONNECT_TIMEOUT
            );

            $request->setCurlOption(
                CURLOPT_TIMEOUT,
                self::GET_UPDATES_TIMEOUT
            );

            return;
        }

        $request->setCurlOption(
            CURLOPT_CONNECTTIMEOUT,
            self::DEFAULT_CONNECT_TIMEOUT
        );

        $request->setCurlOption(
            CURLOPT_TIMEOUT,
            self::DEFAULT_TOTAL_TIMEOUT
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(
        string $response
    ): array {
        try {
            $decoded = json_decode(
                $response,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new TgTransportException(
                sprintf(
                    'Invalid Telegram JSON response: %s',
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (!is_array($decoded)) {
            throw new TgTransportException(
                'Telegram returned non-array JSON response'
            );
        }

        return $decoded;
    }
}
