<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\ASKClient\Request\ASKHttpRequest;
use BAGArt\TelegramBot\Configs\TgBotConfig;

final class TgRequestFactory
{
    public function make(
        string $tgMethodName,
        array $parameters,
        TgBotConfig $botConfig,
        ?int $timeout = null,
    ): ASKHttpRequest {
        $request = ASKHttpRequest::fromParameters(
            url: "https://api.telegram.org/bot{$botConfig->token}/{$tgMethodName}",
            method: 'POST',
            parameters: $parameters,
            requestName: $tgMethodName,
        );

        if ($timeout !== null && defined('CURLOPT_TIMEOUT')) {
            $request = $request->withCurlOption(CURLOPT_TIMEOUT, $timeout);
        }

        return $request;
    }
}
