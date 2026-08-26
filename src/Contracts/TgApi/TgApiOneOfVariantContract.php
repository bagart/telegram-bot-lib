<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApi;

/**
 * Marker for generated DTOs that are a concrete variant of a oneOf contract
 * (e.g. ChatMemberMember of ChatMember). Carries the discriminator literal(s)
 * the Telegram payload must contain for this variant to be the right target,
 * so the mapper can pick the correct class among constructor-compatible
 * candidates instead of taking the first that hydrates.
 */
interface TgApiOneOfVariantContract
{
    /**
     * @return array<string, list<string>> payload field name → accepted literal values
     */
    public static function tgDiscriminators(): array;
}
