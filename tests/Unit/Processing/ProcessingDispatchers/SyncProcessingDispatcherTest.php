<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;

function syncDispatcherValidToken(): string
{
    return '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcde1234';
}

describe('SyncProcessingDispatcher', function () {
    it('dispatches to all processors', function () {
        $dispatcher = new SyncProcessingDispatcher();
        $dto = Mockery::mock(TgApiTypeDTOContract::class);
        $botConfig = new TgBotConfig(token: syncDispatcherValidToken());
        $serviceConfig = new TgServiceConfig();

        $processor1 = Mockery::mock(TgTypeDTOProcessorContract::class);
        $processor1->shouldReceive('process')->with($dto, $botConfig, null, null)->once();
        $processor2 = Mockery::mock(TgTypeDTOProcessorContract::class);
        $processor2->shouldReceive('process')->with($dto, $botConfig, null, null)->once();

        $count = $dispatcher->dispatch($serviceConfig, $botConfig, $dto, [$processor1, $processor2]);

        expect($count)->toBe(2);
    });

    it('returns 0 for empty processors', function () {
        $dispatcher = new SyncProcessingDispatcher();
        $dto = Mockery::mock(TgApiTypeDTOContract::class);
        $botConfig = new TgBotConfig(token: syncDispatcherValidToken());
        $serviceConfig = new TgServiceConfig();

        $count = $dispatcher->dispatch($serviceConfig, $botConfig, $dto, []);

        expect($count)->toBe(0);
    });

    it('skips failed processor and continues to next', function () {
        $dispatcher = new SyncProcessingDispatcher();
        $dto = Mockery::mock(TgApiTypeDTOContract::class);
        $botConfig = new TgBotConfig(token: syncDispatcherValidToken());
        $serviceConfig = new TgServiceConfig();

        $processor1 = Mockery::mock(TgTypeDTOProcessorContract::class);
        $processor1->shouldReceive('process')->andThrow(new \RuntimeException('fail'));
        $processor2 = Mockery::mock(TgTypeDTOProcessorContract::class);
        $processor2->shouldReceive('process')->once();

        $count = $dispatcher->dispatch($serviceConfig, $botConfig, $dto, [$processor1, $processor2]);

        expect($count)->toBe(2);
    });
});
