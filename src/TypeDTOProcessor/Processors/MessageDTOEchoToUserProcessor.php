<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Throwable;

class MessageDTOEchoToUserProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        $logger = $logger ?? TgBotLogWrapper::build();
        return new static(
            dtoClient: TgBotApiDTOClient::build(
                cache: TgBotCacheWrapper::build(),
                logger: $logger,
            ),
            logger: $logger,
        );
    }

    public function __construct(
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly TgBotLogWrapper $logger,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool {
        return $dto instanceof MessageTypeDTO && $dto->text !== null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
        assert($dto instanceof MessageTypeDTO);

        try {
            $this->dtoClient->request(
                token: $config->bot->token,
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
