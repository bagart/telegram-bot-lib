<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Laravel;

use Illuminate\Foundation\Http\FormRequest;

class TgWebhookWithAutoSecretRequest extends FormRequest
{
    public function botId(): ?string
    {
        return explode(':', $this->secret())[0] ?? null;
    }

    public function secret(): ?string
    {
        return $this->headers->get('X-Telegram-Bot-Api-Secret-Token');
    }
}
