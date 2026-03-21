<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Throwable;

class MessageEchoProcessor implements TgUpdateProcessorContract
{
    public function __construct(
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly TgBotLogWrapper $logger,
        private readonly string $token,
    ) {
    }

    public function support(TgApiTypeDTOContract $dto): bool
    {
        return $dto instanceof MessageTypeDTO && $dto->text !== null;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): void
    {
        assert($dto instanceof MessageTypeDTO);

        try {
            $this->dtoClient->request(
                token: $this->token,
                dto: new SendMessageMethodDTO(
                    chatId: $dto->chat->id,
                    text: "echo: {$dto->text}",
                ),
            );
        } catch (Throwable $e) {
            $this->logger->error('Echo reply failed', [
                'chat_id' => $dto->chat->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
