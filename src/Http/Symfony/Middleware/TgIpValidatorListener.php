<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Symfony\Middleware;

use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TgIpValidatorListener
{
    public function __construct(
        private readonly TelegramIpValidator $validator,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $ip = $event->getRequest()->getClientIp();

        if ($ip !== null && !$this->validator->validate($ip)) {
            $event->setResponse(new Response('Forbidden: invalid IP', 403));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
        ];
    }
}
