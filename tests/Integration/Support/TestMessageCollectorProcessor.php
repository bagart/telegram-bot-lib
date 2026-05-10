<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Integration\Support;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TestMessageCollectorProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static();
    }

    /** @var array<array{dto: MessageTypeDTO, botId: string}> */
    public array $collected = [];

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null
    ): bool {
        return $dto instanceof MessageTypeDTO;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
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
