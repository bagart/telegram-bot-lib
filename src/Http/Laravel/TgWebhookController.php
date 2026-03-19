<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @see https://core.telegram.org/bots/api#update
 */
class TgWebhookController extends Controller
{
    public function post(
        TgWebhookWithAutoSecretRequest $request,
        TgWebhookRequestParser $webhookRequestParser,
        TgBotConfig $botConfig,
    ): JsonResponse {
        $config = new TgServiceConfig();
        $config->dispatcher = SyncProcessingDispatcher::TYPE;

        return response()->json([
            'ok' => $webhookRequestParser->parse(
                data: $request->post(),
                secret: $request->secret(),
                config: $config,
                botConfig: $botConfig,
            ),
        ], 200);
    }

    public function postByBotId(
        Request $request,
        TgWebhookRequestParser $webhookRequestParser,
        TgBotConfig $botConfig,
    ): JsonResponse {
        $config = new TgServiceConfig();
        $config->dispatcher = SyncProcessingDispatcher::TYPE;
        $secret = $request->headers->get('X-Telegram-Bot-Api-Secret-Token');

        return response()->json([
            'ok' => $webhookRequestParser->parse(
                data: $request->post(),
                secret: $secret,
                config: $config,
                botConfig: $botConfig,
            ),
        ], 200);
    }
}
