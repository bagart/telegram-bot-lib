<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Services;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use Throwable;

final class ProcessingErrorUserNotifier
{
    public function __construct(
        private readonly TgBotApiDTOClientContract $client,
        private readonly ASKLogWrapper $logger,
    ) {
    }

    public function notify(
        UpdateTypeDTO $updateDTO,
        TgBotConfig $botConfig,
    ): void {
        try {
            $chatId = $updateDTO->message?->chat?->id;

            if ($chatId === null) {
                return;
            }

            $this->client->request(
                botConfig: $botConfig,
                dto: new SendMessageMethodDTO(
                    chatId: (string)$chatId,
                    text: 'Processing message error',
                ),
            );
        } catch (Throwable $e) {
            $this->logger?->error("Failed to notify user about processing error: {$e->getMessage()}");
        }
    }
}
