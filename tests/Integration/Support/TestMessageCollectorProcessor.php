<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Integration\Support;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotSetup;

class TestMessageCollectorProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
    ): self {
        return new static();
    }

    /** @var array<array{dto: MessageTypeDTO, botId: string}> */
    public array $collected = [];

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null
    ): bool {
        return $dto instanceof MessageTypeDTO;
    }

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        \BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        $this->collected[] = ['dto' => $dto, 'botId' => $botConfig->botId];
    }

    public function count(): int
    {
        return count($this->collected);
    }

    public function last(): ?array
    {
        return end($this->collected) ?: null;
    }

    public function reset(): void
    {
        $this->collected = [];
    }
}
