<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Psr\Log\LoggerInterface;

function createTestLogger(): TgBotLogWrapper
{
    return new TgBotLogWrapper(
        logger: new class implements LoggerInterface {
            public function log($level, string|\Stringable $message, array $context = []): void {}
            public function emergency(string|\Stringable $message, array $context = []): void {}
            public function alert(string|\Stringable $message, array $context = []): void {}
            public function critical(string|\Stringable $message, array $context = []): void {}
            public function error(string|\Stringable $message, array $context = []): void {}
            public function warning(string|\Stringable $message, array $context = []): void {}
            public function notice(string|\Stringable $message, array $context = []): void {}
            public function info(string|\Stringable $message, array $context = []): void {}
            public function debug(string|\Stringable $message, array $context = []): void {}
        }
    );
}

describe('TgWebhookRequestParser', function () {
    describe('parse()', function () {
        it('returns true for valid secret', function () {
            $secretService = new AutoSecretByTokenService();
            $token = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';
            $secret = $secretService->secret($token);

            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper::class),
                processorRegistry: new TypeDTOProcessorRegistry(),
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

            $result = $parser->parse($data, $secret);

            expect($result)->toBeTrue();
        });

        it('returns false for null secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper::class),
                processorRegistry: new TypeDTOProcessorRegistry(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], null);

            expect($result)->toBeFalse();
        });

        it('returns false for invalid secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper::class),
                processorRegistry: new TypeDTOProcessorRegistry(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], 'invalid:secret');

            expect($result)->toBeFalse();
        });

        it('returns false for empty secret', function () {
            $parser = new TgWebhookRequestParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper::class),
                processorRegistry: new TypeDTOProcessorRegistry(),
                secretService: new AutoSecretByTokenService(),
                logger: createTestLogger(),
            );

            $result = $parser->parse([], '');

            expect($result)->toBeFalse();
        });
    });
});
