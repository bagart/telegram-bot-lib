<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Pure;

use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;
use BAGArt\TelegramBot\Exceptions\TgUnexpectedDataFormatException;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgWebhookRequestParser
{
    public function __construct(
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly TypeDTOProcessorRegistry $processorRegistry,
        private readonly TgBotsSecretServiceContract $secretService,
        private readonly TgBotLogWrapper $logger,
    ) {
    }

    public function parse(array $data, ?string $secret): bool
    {
        try {
            $botId = $this->secretService->botId($secret);

            $updateDTO = $this->build($data);
            $result = $this->process($updateDTO, $botId);
            if (!$result) {
                $this->logger->info('TgWebhook: Response is true, but processing error', [
                    'botId' => $botId,
                    'update' => $data,
                ]);
            }

            return true;
        } catch (TgBotInvalidSecretException $e) {
            $this->logger->warning('TgWebhook: Secret is not allowed: '.$e->getMessage());
        } catch (TgUnexpectedDataFormatException $e) {
            $this->logger->critical('Webhook request parse error', [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'update' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logger->critical('Webhook request Unexpected error', [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'update' => $data,
            ]);
        }

        return false;
    }

    public function process(TgApiTypeDTOContract $dto, string $botId): bool
    {
        $result = true;
        foreach ($this->processorRegistry->get($dto::class) as $processor) {
            if ($processor->support($dto)) {
                try {
                    $processor->process($dto, $botId);
                } catch (\Throwable $e) {
                    $result = false;
                    $this->logger->error('Webhook request process error', [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    public function build(array $data): TgApiTypeDTOContract
    {
        $typeDTO = $this->tgApiDTOMapper->fromArray(
            UpdateTypeDTO::class,
            $data,
        );
        assert($typeDTO instanceof TgApiTypeDTOContract);

        return $typeDTO;
    }
}
