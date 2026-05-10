<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestCorrelationContract;
use BAGArt\TelegramBot\Exceptions\TgQueueException;

final class TgRequestCorrelation implements TgRequestCorrelationContract
{
    private const string RESPONSE_QUEUE_PREFIX = 'tg_response_';

    public function __construct(
        private readonly ?string $instanceId = null,
    ) {
    }

    public function generateRequestId(): string
    {
        $prefix = $this->resolveInstanceId();

        return sprintf(
            '%s_%s_%s',
            $prefix,
            bin2hex(random_bytes(8)),
            (string) hrtime(true),
        );
    }

    public function generateResponseQueue(
        TgOutboundRequestDTO $request,
    ): string {
        return $this->generateResponseQueueByRequestId(
            $request->requestId,
        );
    }

    public function generateResponseQueueByRequestId(
        string $requestId,
    ): string {
        if ($requestId === '') {
            throw new TgQueueException(
                'requestId must not be empty.'
            );
        }

        return self::RESPONSE_QUEUE_PREFIX . $requestId;
    }

    private function resolveInstanceId(): string
    {
        if (
            $this->instanceId !== null
            && $this->instanceId !== ''
        ) {
            return $this->sanitizeKeyPart($this->instanceId);
        }

        $hostname = gethostname();

        if (
            is_string($hostname)
            && $hostname !== ''
        ) {
            return $this->sanitizeKeyPart($hostname);
        }

        return 'tg_daemon';
    }

    private function sanitizeKeyPart(string $value): string
    {
        $sanitized = preg_replace(
            '/[^a-zA-Z0-9_\-]/',
            '_',
            $value,
        );

        if (
            !is_string($sanitized)
            || $sanitized === ''
        ) {
            return 'tg_daemon';
        }

        return $sanitized;
    }
}
