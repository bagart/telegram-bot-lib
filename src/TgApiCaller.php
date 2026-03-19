<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Processors\ApiCallProcessor;

final class TgApiCaller implements TgSenderContract
{
    public function __construct(
        private readonly TgSenderContract $sender,
        private readonly ProcessingDispatcherRegistry $dispatcherRegistry,
        private readonly TgServiceConfig $serviceConfig,
        private readonly ASKLogWrapper $logger,
        private readonly ?ProcessingDispatcherContract $dispatcher = null,
    ) {
    }

    public function send(TgBotConfig $botConfig, TgApiMethodDTOContract $dto): void
    {
        $this->sender->send($botConfig, $dto);
    }

    public function call(
        TgBotConfig $botConfig,
        TgApiMethodDTOContract $dto,
        ?TgApiTypeDTOContract $contextDto = null,
    ): void {
        $this->logger->info('TgApiCaller: calling method', [
            'method' => $dto::tgApiEntity()->name,
            'botId' => $botConfig->botId,
        ]);

        if ($contextDto === null) {
            $this->sender->send($botConfig, $dto);

            return;
        }

        $processor = new ApiCallProcessor(
            sender: $this->sender,
            methodDto: $dto,
            logger: $this->logger,
        );

        if ($this->dispatcher !== null) {
            $dispatcher = $this->dispatcher;
        } else {
            $dispatcher = $this->dispatcherRegistry->make(
                dispatcherType: $this->serviceConfig->dispatcher,
                logger: $this->logger,
            );
        }

        $dispatcher->dispatch(
            serviceConfig: $this->serviceConfig,
            botConfig: $botConfig,
            dto: $contextDto,
            processors: [$processor],
            action: 'api:'.$dto::tgApiEntity()->name,
        );
    }
}
