<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApiServices;

class TgApiProperty
{
    public function __construct(
        public string $property,
        public string $tgPropName,
        public array $types,
        public array $tgTypes,
        public bool $nullable,
        public bool $required,
    ) {
    }
}
