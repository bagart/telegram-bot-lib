<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Psr\Log\LoggerInterface;

describe('TgBotLogWrapper', function () {
    describe('constructor', function () {
        it('creates wrapper with injected logger', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $wrapper = new TgBotLogWrapper($innerLogger);

            expect($wrapper)->toBeInstanceOf(TgBotLogWrapper::class);
        });

        it('uses static logger when no logger provided', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            TgBotLogWrapper::init($innerLogger);

            $wrapper = new TgBotLogWrapper();

            expect($wrapper)->toBeInstanceOf(TgBotLogWrapper::class);
        });

        it('throws exception when no logger available', function () {
            TgBotLogWrapper::$initLogger = null;

            expect(fn () => new TgBotLogWrapper())
                ->toThrow(\RuntimeException::class);
        });
    });

    describe('log methods', function () {
        it('delegates log to inner logger', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('log')->with('info', 'test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->log('info', 'test message');
        });

        it('adds prefix to emergency messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('emergency')->with('[!!!!!] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->emergency('test message');
        });

        it('adds prefix to alert messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('alert')->with('[!!] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->alert('test message');
        });

        it('adds prefix to critical messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('critical')->with('[!!!!] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->critical('test message');
        });

        it('adds prefix to error messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('error')->with('[!!!] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->error('test message');
        });

        it('adds prefix to warning messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('warning')->with('[!] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->warning('test message');
        });

        it('delegates notice without prefix', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('notice')->with('test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->notice('test message');
        });

        it('delegates info without prefix', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('info')->with('test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->info('test message');
        });

        it('adds prefix to debug messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('debug')->with('[DBG] test message', [])->once();

            $wrapper = new TgBotLogWrapper($innerLogger);
            $wrapper->debug('test message');
        });
    });
});
