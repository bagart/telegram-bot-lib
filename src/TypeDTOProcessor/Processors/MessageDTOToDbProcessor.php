<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\DbLogger\TgDbLoggerWrapperContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class MessageDTOToDbProcessor implements TgTypeDTOProcessorContract
{
    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static(
            new TgEntityNamer(),
            app(TgDbLoggerWrapperContract::class),
        );
    }

    public function __construct(
        protected readonly TgEntityNamer $namer,
        private readonly TgDbLoggerWrapperContract $dbLogger,
    ) {
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
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
        $this->dbLogger?->log($dto, [
            'tg_bot_id' => $botId,
            'action' => $action,
        ]);
    }
}
