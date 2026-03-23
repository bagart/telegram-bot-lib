<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Integration\Support;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

class TestUpdateCollectorProcessor implements TgUpdateProcessorContract
{
    /** @var array<array{dto: UpdateTypeDTO, botId: string}> */
    public array $collected = [];

    public function support(TgApiTypeDTOContract $dto): bool
    {
        return $dto instanceof UpdateTypeDTO;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): void
    {
        $this->collected[] = ['dto' => $dto, 'botId' => $botId];
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
