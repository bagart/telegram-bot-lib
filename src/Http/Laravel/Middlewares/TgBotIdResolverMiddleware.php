<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel\Middlewares;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\BotTokenResolverContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves a Telegram bot from the URL {bot_id} parameter and validates
 * the X-Telegram-Bot-Api-Secret-Token header against the stored secret.
 *
 * Sets TgBotConfig in the container on success.
 */
class TgBotIdResolverMiddleware
{
    public function __construct(
        private readonly BotTokenResolverContract $tokenResolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $botId = $request->route('bot_id');

        if ($botId === null) {
            throw new NotFoundHttpException('Missing bot_id parameter');
        }

        try {
            $token = $this->tokenResolver->resolve($botId);
        } catch (\Throwable) {
            throw new NotFoundHttpException("Bot not found: {$botId}");
        }

        $botConfig = new TgBotConfig(token: $token, botId: $botId);

        app()->instance(TgBotConfig::class, $botConfig);

        return $next($request);
    }
}
