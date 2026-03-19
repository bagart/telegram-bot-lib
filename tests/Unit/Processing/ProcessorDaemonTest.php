<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\UpdateRouterContract;
use BAGArt\TelegramBot\Processing\ProcessorUpdateDaemon;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

function processorDaemonValidToken(): string
{
    return '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcde1234';
}

function processorDaemonLogger(): ASKLogWrapper
{
    return new ASKLogWrapper(logger: new \Psr\Log\NullLogger());
}

describe('ProcessorDaemon', function () {
    it('tick dispatches from queue to router', function () {
        $queue = Mockery::mock(ASKQueueAdapterContract::class);
        $router = Mockery::mock(UpdateRouterContract::class);
        $daemon = new ProcessorUpdateDaemon(
            queue: $queue,
            updateRouter: $router,
            logger: processorDaemonLogger(),
            queueName: 'tg-test',
        );

        $context = new UpdateContext(
            dto: new UpdateTypeDTO(updateId: 1),
            processor: 'TestProcessor',
            botConfig: new TgBotConfig(token: processorDaemonValidToken()),
            executionKey: null,
            jobId: 'test-job',
        );
        $queue->shouldReceive('pop')->once()->with('tg-test')->andReturn(serialize($context));
        $router->shouldReceive('dispatch')->with(Mockery::type(UpdateContext::class))->once();

        $daemon->tick(0);
    });

    it('tick skips dispatch when queue empty', function () {
        $queue = Mockery::mock(ASKQueueAdapterContract::class);
        $router = Mockery::mock(UpdateRouterContract::class);
        $daemon = new ProcessorUpdateDaemon(
            queue: $queue,
            updateRouter: $router,
            logger: processorDaemonLogger(),
            queueName: 'tg-test',
        );

        $queue->shouldReceive('pop')->once()->with('tg-test')->andReturn(null);

        $router->shouldNotReceive('dispatch');

        $daemon->tick(0);
    });

    it('shutdown ticks once and returns true when queue empty', function () {
        $queue = Mockery::mock(ASKQueueAdapterContract::class);
        $router = Mockery::mock(UpdateRouterContract::class);
        $daemon = new ProcessorUpdateDaemon(
            queue: $queue,
            updateRouter: $router,
            logger: processorDaemonLogger(),
            queueName: 'tg-test',
        );

        $router->shouldReceive('tickable')->once()->andReturn([]);
        $queue->shouldReceive('pop')->once()->with('tg-test')->andReturn(null);
        $queue->shouldReceive('size')->once()->with('tg-test')->andReturn(0);

        expect($daemon->shutdown())->toBeTrue();
    });
});
