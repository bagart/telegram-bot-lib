<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\ASKClient\Dto\ASKHttpRequest;
use BAGArt\TelegramBot\Configs\TgBotConfig;

final class TgRequestFactory
{
    public function make(
        string $tgMethodName,
        array $parameters,
        TgBotConfig $botConfig,
        ?int $timeout = null,
        array $files = [],
    ): ASKHttpRequest {
        $url = "https://api.telegram.org/bot{$botConfig->token}/{$tgMethodName}";

        // Endpoint pinning (03 §61): the Bot API is only ever called over TLS
        // on the official host — a non-https URL here means caller error.
        if (! str_starts_with($url, 'https://api.telegram.org/')) {
            throw new \InvalidArgumentException('Telegram API requests must target https://api.telegram.org');
        }

        $request = ASKHttpRequest::fromParameters(
            url: $url,
            method: 'POST',
            parameters: $parameters,
            requestName: $tgMethodName,
            files: $files,
        );

        if ($timeout !== null && defined('CURLOPT_TIMEOUT')) {
            $request = $request->withCurlOption(CURLOPT_TIMEOUT, $timeout);
        }

        return $request;
    }
}
