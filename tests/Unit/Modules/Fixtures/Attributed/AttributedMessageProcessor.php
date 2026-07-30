<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Modules\Attributes\TgProcessorAttribute;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

#[TgProcessorAttribute(dto: MessageTypeDTO::class)]
class AttributedMessageProcessor implements TgTypeDTOProcessorContract
{
    public static function build(BotProcessorContext $context): self
    {
        return new self();
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
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

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): void {
    }
}
