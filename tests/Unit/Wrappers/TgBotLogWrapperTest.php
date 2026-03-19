<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use Psr\Log\LoggerInterface;

describe('ASKLogWrapper', function () {
    describe('constructor', function () {
        it('creates wrapper with injected logger', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $wrapper = new ASKLogWrapper($innerLogger);

            expect($wrapper)->toBeInstanceOf(ASKLogWrapper::class);
        });
    });

    describe('log methods', function () {
        it('delegates log to inner logger', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('log')->with('info', 'test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->log('info', 'test message');
        });

        it('adds prefix to emergency messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('emergency')->with('[!!!!!] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->emergency('test message');
        });

        it('adds prefix to alert messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('alert')->with('[!!] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->alert('test message');
        });

        it('adds prefix to critical messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('critical')->with('[!!!!] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->critical('test message');
        });

        it('adds prefix to error messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('error')->with('[!!!] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->error('test message');
        });

        it('adds prefix to warning messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('warning')->with('[!] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->warning('test message');
        });

        it('delegates notice without prefix', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('notice')->with('test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->notice('test message');
        });

        it('delegates info without prefix', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('info')->with('test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger);
            $wrapper->info('test message');
        });

        it('adds prefix to debug messages', function () {
            $innerLogger = Mockery::mock(LoggerInterface::class);
            $innerLogger->shouldReceive('debug')->with('[DBG] test message', [])->once();

            $wrapper = new ASKLogWrapper($innerLogger, ASKLogWrapper::LEVEL_DEBUG);
            $wrapper->debug('test message');
        });
    });
});
