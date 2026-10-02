<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\Pipeline\ASKFutureContract;
use BAGArt\AskQueue\Contracts\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\ApiCommunication\Polling\TgPollerDaemon;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgPollerConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\Processing\TgUpdateProcessorSelectorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use Psr\Log\NullLogger;

function pollerDaemonToken(): string
{
    return '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcde1234';
}

describe('TgPollerDaemon getUpdates pass-through', function () {
    it('forwards the configured allowed_updates subscription to getUpdates', function () {
        $captured = null;

        $dtoClient = Mockery::mock(TgBotApiDTOClientContract::class);
        $dtoClient->shouldReceive('requestAsync')
            ->once()
            ->with(
                Mockery::type(TgBotConfig::class),
                Mockery::type(GetUpdatesMethodDTO::class),
                10,
            )
            ->andReturnUsing(function (TgBotConfig $botConfig, TgApiMethodDTOContract $dto, ?int $timeout = null) use (&$captured): ASKFutureContract {
                $captured = $dto;

                $future = Mockery::mock(ASKFutureContract::class);
                $future->shouldReceive('await')
                    ->once()
                    ->andReturn(new TgApiResponse(ok: true, possibleResultTypes: [], result: []));

                return $future;
            });

        $daemon = new TgPollerDaemon(
            botConfig: new TgBotConfig(token: pollerDaemonToken()),
            queue: Mockery::mock(ASKQueueAdapterContract::class),
            dtoClient: $dtoClient,
            updateProcessorSelector: Mockery::mock(TgUpdateProcessorSelectorContract::class),
            logger: new ASKLogWrapper(logger: new NullLogger()),
        );

        $daemon->produce(0);

        expect($captured)->toBeInstanceOf(GetUpdatesMethodDTO::class)
            ->and($captured->allowedUpdates)->toBe(TgPollerConfig::DEFAULT_ALLOWED_UPDATES)
            ->toContain('chat_member')
            ->toContain('my_chat_member');
    });
});
