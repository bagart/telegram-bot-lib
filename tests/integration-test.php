<?php

declare(strict_types=1);

/**
 * Standalone integration test runner for telegram-bot-lib.
 * Run: php integration-test.php
 */

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/Unit/Integration/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Unit/Integration/Support/TestUpdateCollectorProcessor.php';

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\Tests\Unit\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\Tests\Unit\Integration\Support\TestUpdateCollectorProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

$passed = 0;
$failed = 0;
$errors = [];

function assert_test(string $name, bool $condition, string $message = ''): void
{
    global $passed, $failed, $errors;
    if ($condition) {
        $passed++;
        echo "  PASS: $name\n";
    } else {
        $failed++;
        $errors[] = "$name: $message";
        echo "  FAIL: $name - $message\n";
    }
}

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

// ========== Run Tests ==========

echo "\n=== Integration Tests: Full Cycle Processing ===\n\n";

$secretService = new AutoSecretByTokenService();
$secret = $secretService->secret(TOKEN);

// Test 1: Text message
echo "Test 1: Text message -> DTO -> processors\n";
$registry = new TypeDTOProcessorRegistry();
$messageCollector = new TestMessageCollectorProcessor();
$updateCollector = new TestUpdateCollectorProcessor();
$registry->register(MessageTypeDTO::class, $messageCollector);
$registry->register(UpdateTypeDTO::class, $updateCollector);
$webhook = TgPureFactory::webhook($registry);
$result = $webhook->parse(messagePayload(), $secret);

assert_test('parse returns true', $result === true);
assert_test('update collector count', $updateCollector->count() === 1, "expected 1, got {$updateCollector->count()}");
assert_test('message collector count', $messageCollector->count() === 1, "expected 1, got {$messageCollector->count()}");
assert_test('update id', $updateCollector->last()['dto']->updateId === 987654321);
assert_test('message id', $messageCollector->last()['dto']->messageId === 42);
assert_test('message text', $messageCollector->last()['dto']->text === 'Hello, bot!');
assert_test('chat id', $messageCollector->last()['dto']->chat->id === '111222333');
assert_test('from first name', $messageCollector->last()['dto']->from->firstName === 'John');
assert_test('bot id', $messageCollector->last()['botId'] === '123456789');

// Test 2: Edited message
echo "\nTest 2: Edited message -> DTO -> processors\n";
$registry2 = new TypeDTOProcessorRegistry();
$messageCollector2 = new TestMessageCollectorProcessor();
$registry2->register(MessageTypeDTO::class, $messageCollector2);
$webhook2 = TgPureFactory::webhook($registry2);
$result2 = $webhook2->parse(editedMessagePayload(), $secret);

assert_test('parse returns true', $result2 === true);
assert_test('message collector count', $messageCollector2->count() === 1);
assert_test('edited text', $messageCollector2->last()['dto']->text === 'Hello, bot! (edited)');
assert_test('edit date', $messageCollector2->last()['dto']->editDate === 1700000060);

// Test 3: Reply message
echo "\nTest 3: Reply message -> DTO -> processors\n";
$registry3 = new TypeDTOProcessorRegistry();
$messageCollector3 = new TestMessageCollectorProcessor();
$registry3->register(MessageTypeDTO::class, $messageCollector3);
$webhook3 = TgPureFactory::webhook($registry3);
$result3 = $webhook3->parse(replyMessagePayload(), $secret);

assert_test('parse returns true', $result3 === true);
assert_test('message collector count', $messageCollector3->count() === 1);
assert_test('reply text', $messageCollector3->last()['dto']->text === 'Hi back!');
assert_test('reply_to_message not null', $messageCollector3->last()['dto']->replyToMessage !== null);
assert_test('reply_to_message id', $messageCollector3->last()['dto']->replyToMessage->messageId === 42);

// Test 4: Callback query (no message processor)
echo "\nTest 4: Callback query -> no message processor triggered\n";
$registry4 = new TypeDTOProcessorRegistry();
$messageCollector4 = new TestMessageCollectorProcessor();
$updateCollector4 = new TestUpdateCollectorProcessor();
$registry4->register(MessageTypeDTO::class, $messageCollector4);
$registry4->register(UpdateTypeDTO::class, $updateCollector4);
$webhook4 = TgPureFactory::webhook($registry4);
$result4 = $webhook4->parse(callbackQueryPayload(), $secret);

assert_test('parse returns true', $result4 === true);
assert_test('update collector count', $updateCollector4->count() === 1);
assert_test('message collector count (should be 0)', $messageCollector4->count() === 0, "expected 0, got {$messageCollector4->count()}");

// Test 5: Channel post
echo "\nTest 5: Channel post -> DTO -> processors\n";
$registry5 = new TypeDTOProcessorRegistry();
$messageCollector5 = new TestMessageCollectorProcessor();
$registry5->register(MessageTypeDTO::class, $messageCollector5);
$webhook5 = TgPureFactory::webhook($registry5);
$result5 = $webhook5->parse(channelPostPayload(), $secret);

assert_test('parse returns true', $result5 === true);
assert_test('message collector count', $messageCollector5->count() === 1);
assert_test('channel text', $messageCollector5->last()['dto']->text === 'Channel post text');
assert_test('channel chat id', $messageCollector5->last()['dto']->chat->id === '-1001234567890');

// Test 6: Multiple messages accumulate
echo "\nTest 6: Multiple messages accumulate in collectors\n";
$registry6 = new TypeDTOProcessorRegistry();
$messageCollector6 = new TestMessageCollectorProcessor();
$registry6->register(MessageTypeDTO::class, $messageCollector6);
$webhook6 = TgPureFactory::webhook($registry6);
$webhook6->parse(messagePayload(), $secret);
$webhook6->parse(editedMessagePayload(), $secret);
$webhook6->parse(replyMessagePayload(), $secret);

assert_test('collector count after 3 messages', $messageCollector6->count() === 3, "expected 3, got {$messageCollector6->count()}");
$texts = array_map(fn ($item) => $item['dto']->text, $messageCollector6->collected);
assert_test('accumulated texts', $texts === ['Hello, bot!', 'Hello, bot! (edited)', 'Hi back!'], json_encode($texts));

// Test 7: Reset collector
echo "\nTest 7: Reset collector clears data\n";
$messageCollector6->reset();
assert_test('count after reset', $messageCollector6->count() === 0);

// Test 8: Invalid secret
echo "\nTest 8: Invalid secret returns false\n";
$registry8 = new TypeDTOProcessorRegistry();
$messageCollector8 = new TestMessageCollectorProcessor();
$registry8->register(MessageTypeDTO::class, $messageCollector8);
$webhook8 = TgPureFactory::webhook($registry8);
$result8 = $webhook8->parse(messagePayload(), 'invalid:secret');

assert_test('parse returns false', $result8 === false);
assert_test('message collector count (should be 0)', $messageCollector8->count() === 0);

// Test 9: Null secret
echo "\nTest 9: Null secret returns false\n";
$registry9 = new TypeDTOProcessorRegistry();
$messageCollector9 = new TestMessageCollectorProcessor();
$registry9->register(MessageTypeDTO::class, $messageCollector9);
$webhook9 = TgPureFactory::webhook($registry9);
$result9 = $webhook9->parse(messagePayload(), null);

assert_test('parse returns false', $result9 === false);
assert_test('message collector count (should be 0)', $messageCollector9->count() === 0);

// Test 10: Secret format
echo "\nTest 10: Secret service generates correct format\n";
$generatedSecret = $secretService->secret(TOKEN);
$botId = $secretService->botId($generatedSecret);
assert_test('secret format', preg_match('/^\d+:[a-f0-9]{64}$/', $generatedSecret) === 1, $generatedSecret);
assert_test('botId extraction', $botId === '123456789');

// Test 11: Both processors work together
echo "\nTest 11: Both update and message processors work together\n";
$registry11 = new TypeDTOProcessorRegistry();
$messageCollector11 = new TestMessageCollectorProcessor();
$updateCollector11 = new TestUpdateCollectorProcessor();
$registry11->register(MessageTypeDTO::class, $messageCollector11);
$registry11->register(UpdateTypeDTO::class, $updateCollector11);
$webhook11 = TgPureFactory::webhook($registry11);
$webhook11->parse(messagePayload(), $secret);

assert_test('update collector count', $updateCollector11->count() === 1);
assert_test('message collector count', $messageCollector11->count() === 1);
assert_test('update message matches', $updateCollector11->last()['dto']->message->messageId === $messageCollector11->last()['dto']->messageId);

// Test 12: Multiple bots
echo "\nTest 12: Multiple bots use same processors\n";
$registry12 = new TypeDTOProcessorRegistry();
$messageCollector12 = new TestMessageCollectorProcessor();
$registry12->register(MessageTypeDTO::class, $messageCollector12);
$webhook12 = TgPureFactory::webhook($registry12);

$token1 = '111111111:AAAaaaBBBbbbCCCccc';
$token2 = '222222222:DDDdddEEEeeeFFFfff';
$secret1 = $secretService->secret($token1);
$secret2 = $secretService->secret($token2);

$webhook12->parse(messagePayload(), $secret1);
$webhook12->parse(editedMessagePayload(), $secret2);

assert_test('collector count for 2 bots', $messageCollector12->count() === 2);
assert_test('first bot id', $messageCollector12->collected[0]['botId'] === '111111111');
assert_test('second bot id', $messageCollector12->collected[1]['botId'] === '222222222');

// Summary
echo "\n========================================\n";
echo "Results: $passed passed, $failed failed\n";
echo "========================================\n";

if ($failed > 0) {
    echo "\nFailed tests:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
}

exit(0);
