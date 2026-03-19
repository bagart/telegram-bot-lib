<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiCaller;
use BAGArt\TelegramBot\TgBotSetup;

class MessageDTOEchoToUserProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
    ): self {
        return new static(
            logger: $botSetup->logger,
            tgApiCaller: $botSetup->tgApiCaller,
        );
    }

    public function __construct(
        private readonly ASKLogWrapper $logger,
        private readonly TgApiCaller $tgApiCaller,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO && $dto->text !== null;
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
        assert($dto instanceof MessageTypeDTO);

        $this->logger->debug('{MessageDTOEchoToUserProcessor}: echo init', [
            'bot_id' => $botConfig->botId,
            'chat_id' => $dto->chat->id,
            'text' => $dto->text,
            'from' => $dto->from?->id ?? 'null',
        ]);

        $sendMessageDto = new SendMessageMethodDTO(
            chatId: $dto->chat->id,
            text: "echo: {$dto->text}",
        );

        $this->tgApiCaller->call(
            botConfig: $botConfig,
            dto: $sendMessageDto,
            contextDto: $dto,
        );
    }

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }
}
