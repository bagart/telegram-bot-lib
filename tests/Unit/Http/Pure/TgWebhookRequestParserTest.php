<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher\LaravelProcessingDispatcher;
use BAGArt\TelegramBot\Processing\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\ProcessingDispatcherRegistry;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use Psr\Log\LoggerInterface;

function createTestLogger(): ASKLogWrapper
{
    return new ASKLogWrapper(
        logger: new class () implements LoggerInterface {
            public function log($level, string|\Stringable $message, array $context = []): void
            {
            }

            public function emergency(string|\Stringable $message, array $context = []): void
            {
            }

            public function alert(string|\Stringable $message, array $context = []): void
            {
            }

            public function critical(string|\Stringable $message, array $context = []): void
            {
            }

            public function error(string|\Stringable $message, array $context = []): void
            {
            }

            public function warning(string|\Stringable $message, array $context = []): void
            {
            }

            public function notice(string|\Stringable $message, array $context = []): void
            {
            }

            public function info(string|\Stringable $message, array $context = []): void
            {
            }

            public function debug(string|\Stringable $message, array $context = []): void
            {
            }
        }
    );
}

function createTestSelector(?TypeDTOProcessorRegistry $registry = null): RegisteredUpdateProcessorSelector
{
    $registry ??= TypeDTOProcessorRegistry::build();
    $factory = TgBotSetupFactory::build();
    $setup = $factory->create(
        serviceConfig: new TgServiceConfig(),
        processorRegistryOverride: $registry,
    );

    return new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: $setup,
    );
}

describe('TgWebhookRequestParser', function () {
    describe('parse()', function () {
        it('returns true for valid secret', function () {
            $secretService = new AutoSecretByTokenService();
            $token = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';
            $secret = $secretService->secret($token);

            $mapper = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class);
            $mapper->shouldReceive('fromArray')
                ->with(\BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO::class, Mockery::any())
                ->andReturn(new \BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO(updateId: 1));

            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: $mapper,
                selector: createTestSelector(),
                secretService: $secretService,
                logger: createTestLogger(),
            );

            $data = [
                'update_id' => 1,
                'message' => [
                    'message_id' => 1,
                    'from' => ['id' => 1, 'is_bot' => false, 'first_name' => 'Test'],
                    'chat' => ['id' => 1, 'type' => 'private'],
                    'date' => 1000,
                    'text' => 'Hello',
                ],
            ];

            $result = $parser->parse($data, $secret, new TgServiceConfig('test'));

            expect($result)->toBeTrue();
        });

        it('returns false for null secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                selector: createTestSelector(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], null, new TgServiceConfig('test'));

            expect($result)->toBeFalse();
        });

        it('returns false for invalid secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                selector: createTestSelector(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], 'invalid:secret', new TgServiceConfig('test'));

            expect($result)->toBeFalse();
        });

        it('returns false for empty secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                selector: createTestSelector(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], '', new TgServiceConfig('test'));

            expect($result)->toBeFalse();
        });
    });

    describe('async dispatch', function () {
        it('uses async dispatcher when dispatcherRegistry is provided', function () {
            $secretService = new AutoSecretByTokenService();
            $token = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';
            $secret = $secretService->secret($token);

            $mapper = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class);
            $mapper->shouldReceive('fromArray')
                ->with(\BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO::class, Mockery::any())
                ->andReturn(new \BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO(updateId: 1));

            $dispatcherRegistry = ProcessingDispatcherRegistry::build();
            $processorRegistry = TypeDTOProcessorRegistry::build();
            $processorRegistry->register(
                \BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO::class,
                UpdateDTOInitProcessor::class,
            );

            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: $mapper,
                selector: createTestSelector($processorRegistry),
                secretService: $secretService,
                logger: createTestLogger(),
                dispatcherRegistry: $dispatcherRegistry,
            );

            $data = [
                'update_id' => 2,
                'message' => [
                    'message_id' => 2,
                    'from' => ['id' => 2, 'is_bot' => false, 'first_name' => 'Test'],
                    'chat' => ['id' => 2, 'type' => 'private'],
                    'date' => 2000,
                    'text' => 'Async test',
                ],
            ];

            $config = new TgServiceConfig();
            $config->dispatcher = LaravelProcessingDispatcher::TYPE;
            $result = $parser->parse($data, $secret, $config);

            expect($result)->toBeTrue();
        });

        it('falls back to sync when dispatcherRegistry is null', function () {
            $secretService = new AutoSecretByTokenService();
            $token = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';
            $secret = $secretService->secret($token);

            $mapper = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class);
            $mapper->shouldReceive('fromArray')
                ->with(\BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO::class, Mockery::any())
                ->andReturn(new \BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO(updateId: 1));

            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: $mapper,
                selector: createTestSelector(),
                secretService: $secretService,
                logger: createTestLogger(),
            );

            $data = [
                'update_id' => 1,
                'message' => [
                    'message_id' => 1,
                    'from' => ['id' => 1, 'is_bot' => false, 'first_name' => 'Test'],
                    'chat' => ['id' => 1, 'type' => 'private'],
                    'date' => 1000,
                    'text' => 'Sync fallback',
                ],
            ];

            $result = $parser->parse($data, $secret, new TgServiceConfig('test'));

            expect($result)->toBeTrue();
        });
    });
});
