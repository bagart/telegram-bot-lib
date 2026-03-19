<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundBusinessErrorException;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;

if (!class_exists('ControllableClock')) {
    require_once __DIR__.'/../../Helpers.php';
}

function leaseEnvelope(string $id = 't1'): OutboundEnvelope
{
    $envelope = new OutboundEnvelope(
        new OutboundTask(id: $id, botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []),
        new OutboundTaskState(),
    );
    $envelope->deliveryId = "delivery-{$id}";

    return $envelope;
}

describe('LeaseRenewer', function () {
    it('starts idle with no tracked envelopes', function () {
        $renewer = new LeaseRenewer(
            new InMemoryOutboundQueue(new ControllableClock()),
            new ControllableClock(),
        );

        expect($renewer->isIdle())->toBeTrue()
            ->and($renewer->queueSize())->toBe(0);
    });

    it('track adds an envelope, untrack removes it', function () {
        $renewer = new LeaseRenewer(
            new InMemoryOutboundQueue(new ControllableClock()),
            new ControllableClock(),
        );

        $envelope = leaseEnvelope('t1');
        $renewer->track($envelope);

        expect($renewer->isIdle())->toBeFalse()
            ->and($renewer->queueSize())->toBe(1);

        $renewer->untrack($envelope->deliveryId);

        expect($renewer->isIdle())->toBeTrue();
    });

    it('tick does nothing when queue is not LeaseRenewableQueueContract', function () {
        $clock = new ControllableClock(1000000);
        $nonRenewable = Mockery::mock(\BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract::class);
        $renewer = new LeaseRenewer($nonRenewable, $clock);

        $renewer->track(leaseEnvelope('t1'));

        $renewer->tick(0);

        expect($renewer->queueSize())->toBe(1);
    });

    it('throws when maxRenewals exceeded', function () {
        $clock = new ControllableClock(1000000);
        $queue = new InMemoryOutboundQueue($clock);
        $renewer = new LeaseRenewer($queue, $clock, renewIntervalSec: 1, maxRenewals: 2);

        $queue->push(new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []));
        $envelope = $queue->pop(60);
        expect($envelope)->not->toBeNull();
        $renewer->track($envelope);

        $clock->advance(2);
        $renewer->tick(0);
        expect($renewer->queueSize())->toBe(1);

        $clock->advance(2);
        $renewer->tick(0);
        expect($renewer->queueSize())->toBe(1);

        $clock->advance(2);
        expect(fn () => $renewer->tick(0))
            ->toThrow(OutboundBusinessErrorException::class);
    });
});
