<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel\Middlewares;

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TgIpValidatorMiddleware
{
    public function __construct(
        private readonly TelegramIpValidator $validator,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Opt-in escape hatch for local/staging load runs and smoke tests:
        // loopback + private-network sources skip the Telegram CIDR check.
        // Production must keep this off (default).
        if (config('telegram.webhook_allow_local_ips') === true && self::isLocalOrPrivate((string) $request->ip())) {
            return $next($request);
        }

        if (!$this->validator->validate($request->ip())) {
            // HttpException directly (not abort()) keeps the lib free of the
            // foundation helper/container dependency.
            throw new HttpException(403, 'Forbidden: invalid IP');
        }

        return $next($request);
    }

    /** True when the IP is loopback or falls into private/reserved ranges. */
    private static function isLocalOrPrivate(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
