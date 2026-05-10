<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel;

use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TgUpdateConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * @see https://core.telegram.org/bots/api#update
 */
class TgWebhookController extends Controller
{
    public function post(
        TgWebhookWithAutoSecretRequest $request,
        TgWebhookRequestParser $webhookRequestParser,
    ): JsonResponse {
        return response()->json([
            'ok' => $webhookRequestParser->parse(
                data: $request->post(),
                secret: $request->secret(),
                config: new TgUpdateConfig(bot: new TgBotConfig(token: 'hidden')),
            )
        ], 200);
    }
}
