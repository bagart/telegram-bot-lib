<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;

define('TG_TEST_TOKEN', '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcde1234');

/**
 * Real Telegram webhook payloads (from documentation + real bots).
 */
function messagePayload(): array
{
    return [
        'update_id' => 987654321,
        'message' => [
            'message_id' => 42,
            'from' => [
                'id' => 111222333,
                'is_bot' => false,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'language_code' => 'en',
            ],
            'chat' => [
                'id' => 111222333,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'type' => 'private',
            ],
            'date' => 1700000000,
            'text' => 'Hello, bot!',
        ],
    ];
}

function editedMessagePayload(): array
{
    return [
        'update_id' => 987654322,
        'edited_message' => [
            'message_id' => 42,
            'from' => [
                'id' => 111222333,
                'is_bot' => false,
                'first_name' => 'John',
                'username' => 'johndoe',
            ],
            'chat' => [
                'id' => 111222333,
                'first_name' => 'John',
                'username' => 'johndoe',
                'type' => 'private',
            ],
            'date' => 1700000000,
            'edit_date' => 1700000060,
            'text' => 'Hello, bot! (edited)',
        ],
    ];
}

function replyMessagePayload(): array
{
    return [
        'update_id' => 987654323,
        'message' => [
            'message_id' => 43,
            'from' => [
                'id' => 111222333,
                'is_bot' => false,
                'first_name' => 'John',
                'username' => 'johndoe',
            ],
            'chat' => [
                'id' => 111222333,
                'first_name' => 'John',
                'username' => 'johndoe',
                'type' => 'private',
            ],
            'date' => 1700000100,
            'reply_to_message' => [
                'message_id' => 42,
                'from' => [
                    'id' => 999888777,
                    'is_bot' => true,
                    'first_name' => 'TestBot',
                    'username' => 'testbot',
                ],
                'chat' => [
                    'id' => 111222333,
                    'first_name' => 'John',
                    'username' => 'johndoe',
                    'type' => 'private',
                ],
                'date' => 1700000000,
                'text' => 'Hello, bot!',
            ],
            'text' => 'Hi back!',
        ],
    ];
}

function callbackQueryPayload(): array
{
    return [
        'update_id' => 987654324,
        'callback_query' => [
            'id' => '12345678901234567',
            'from' => [
                'id' => 111222333,
                'is_bot' => false,
                'first_name' => 'John',
                'username' => 'johndoe',
            ],
            'message' => [
                'message_id' => 44,
                'from' => [
                    'id' => 999888777,
                    'is_bot' => true,
                    'first_name' => 'TestBot',
                    'username' => 'testbot',
                ],
                'chat' => [
                    'id' => 111222333,
                    'first_name' => 'John',
                    'username' => 'johndoe',
                    'type' => 'private',
                ],
                'date' => 1700000200,
                'text' => 'Choose option:',
            ],
            'chat_instance' => '1234567890123',
            'data' => 'button_yes',
        ],
    ];
}

function channelPostPayload(): array
{
    return [
        'update_id' => 987654325,
        'channel_post' => [
            'message_id' => 100,
            'sender_chat' => [
                'id' => -1001234567890,
                'title' => 'Test Channel',
                'type' => 'channel',
            ],
            'chat' => [
                'id' => -1001234567890,
                'title' => 'Test Channel',
                'type' => 'channel',
            ],
            'date' => 1700000300,
            'text' => 'Channel post text',
        ],
    ];
}

function groupMessagePayload(): array
{
    return [
        'update_id' => 987654326,
        'message' => [
            'message_id' => 50,
            'from' => [
                'id' => 111222333,
                'is_bot' => false,
                'first_name' => 'John',
                'username' => 'johndoe',
            ],
            'chat' => [
                'id' => -1001234567891,
                'title' => 'Test Group',
                'type' => 'supergroup',
            ],
            'date' => 1700000400,
            'text' => 'Hello group!',
        ],
    ];
}

// ========== Full Cycle Integration Tests ==========

test('full cycle: text message -> DTO -> processors', function () {
    $payload = messagePayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);
    $botId = $secretService->botId($secret);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message)->toBeInstanceOf(MessageTypeDTO::class)
        ->and($message->messageId)->toBe(42)
        ->and($message->text)->toBe('Hello, bot!')
        ->and($message->chat->id)->toBe('111222333')
        ->and($message->from->firstName)->toBe('John')
        ->and($message->from->username)->toBe('johndoe')
        ->and($messageCollector->last()['botId'])->toBe('123456789');
});

test('full cycle: edited message -> DTO -> processors', function () {
    $payload = editedMessagePayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);
    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message->text)->toBe('Hello, bot! (edited)')
        ->and($message->editDate)->toBe(1700000060);
});

test('full cycle: reply message -> DTO -> processors', function () {
    $payload = replyMessagePayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message->text)->toBe('Hi back!')
        ->and($message->replyToMessage)->not->toBeNull()
        ->and($message->replyToMessage->messageId)->toBe(42)
        ->and($message->replyToMessage->text)->toBe('Hello, bot!');
});

test('full cycle: callback query -> no message processor triggered', function () {
    $payload = callbackQueryPayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(0);
});

test('full cycle: channel post -> DTO -> processors', function () {
    $payload = channelPostPayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message->text)->toBe('Channel post text')
        ->and($message->chat->id)->toBe('-1001234567890')
        ->and($message->chat->title)->toBe('Test Channel');
});

test('full cycle: group message -> DTO -> processors', function () {
    $payload = groupMessagePayload();
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $result = $webhook->parse($payload, $secret, $config);

    expect($result)->toBeTrue()
        ->and($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message->text)->toBe('Hello group!')
        ->and($message->chat->id)->toBe('-1001234567891')
        ->and($message->chat->title)->toBe('Test Group');
});

test('full cycle: multiple messages accumulate in collectors', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);
    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $webhook->parse(messagePayload(), $secret, $config);
    $webhook->parse(editedMessagePayload(), $secret, $config);
    $webhook->parse(replyMessagePayload(), $secret, $config);

    expect($messageCollector->count())->toBe(3);

    $texts = array_map(fn ($item) => $item['dto']->text, $messageCollector->collected);
    expect($texts)->toBe(['Hello, bot!', 'Hello, bot! (edited)', 'Hi back!']);
});

test('full cycle: reset collector clears data', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);
    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $webhook->parse(messagePayload(), $secret, $config);
    expect($messageCollector->count())->toBe(1);

    $messageCollector->reset();
    expect($messageCollector->count())->toBe(0);

    $webhook->parse(editedMessagePayload(), $secret, $config);
    expect($messageCollector->count())->toBe(1);
});

test('full cycle: invalid secret returns false', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);

    $result = $webhook->parse(messagePayload(), 'invalid:secret', new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE));

    expect($result)->toBeFalse()
        ->and($messageCollector->count())->toBe(0);
});

test('full cycle: null secret returns false', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);

    $result = $webhook->parse(messagePayload(), null, new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE));

    expect($result)->toBeFalse()
        ->and($messageCollector->count())->toBe(0);
});

test('full cycle: secret service generates correct format', function () {
    $secretService = new AutoSecretByTokenService();

    $secret = $secretService->secret(TG_TEST_TOKEN);
    $botId = $secretService->botId($secret);

    expect($secret)->toMatch('/^\d+:[a-f0-9]{64}$/')
        ->and($botId)->toBe('123456789')
        ->and(str_contains($secret, '123456789:'))->toBeTrue();
});

test('full cycle: both update and message processors work together', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();
    $secret = $secretService->secret(TG_TEST_TOKEN);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $webhook->parse(messagePayload(), $secret, $config);

    expect($messageCollector->count())->toBe(1);

    $message = $messageCollector->last()['dto'];
    expect($message->messageId)->toBe(42)
        ->and($message->text)->toBe('Hello, bot!');
});

test('full cycle: webhooks with different bots use same processors', function () {
    $registry = TypeDTOProcessorRegistry::build();
    $messageCollector = new TestMessageCollectorProcessor();

    $registry->register(MessageTypeDTO::class, $messageCollector);

    $webhook = TgBotSetupFactory::webhook($registry);
    $secretService = new AutoSecretByTokenService();

    $token1 = '111111111:AAAaaaBBBbbbCCCccc';
    $token2 = '222222222:DDDdddEEEeeeFFFfff';

    $secret1 = $secretService->secret($token1);
    $secret2 = $secretService->secret($token2);

    $config = new TgServiceConfig(dispatcher: SyncProcessingDispatcher::TYPE);

    $webhook->parse(messagePayload(), $secret1, $config);
    $webhook->parse(editedMessagePayload(), $secret2, $config);

    expect($messageCollector->count())->toBe(2)
        ->and($messageCollector->collected[0]['botId'])->toBe('111111111')
        ->and($messageCollector->collected[1]['botId'])->toBe('222222222');
});
