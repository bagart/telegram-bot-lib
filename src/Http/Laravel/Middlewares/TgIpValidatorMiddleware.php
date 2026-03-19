<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel\Middlewares;

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TgIpValidatorMiddleware
{
    public function __construct(
        private readonly TelegramIpValidator $validator,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->validator->validate($request->ip())) {
            abort(403, 'Forbidden: invalid IP');
        }

        return $next($request);
    }
}
