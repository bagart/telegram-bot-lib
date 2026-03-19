<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel\Middlewares;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TgSecretValidatorMiddleware
{
    public function __construct(
        private readonly TgBotsSecretServiceContract $secretService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $secret = $request->headers->get('X-Telegram-Bot-Api-Secret-Token');

        if ($secret === null || $secret === '') {
            throw new HttpException(401, 'Unauthorized: missing secret token');
        }

        try {
            $botId = $this->secretService->botId($secret);
        } catch (TgBotInvalidSecretException) {
            throw new HttpException(403, 'Forbidden: invalid secret token');
        }

        app()->instance(TgBotConfig::class, new TgBotConfig(token: '', botId: $botId));

        return $next($request);
    }
}
