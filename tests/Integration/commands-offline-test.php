<?php

declare(strict_types=1);

/**
 * Offline integration tests for commands/ and public/index-sync.php
 * Tests the core logic without making any real API calls.
 *
 * Run: php offline-test.php
 */
require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\Tests\Integration\Support\TestTypeDTOCollectorProcessor;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use BAGArt\TelegramBot\TgIntegration\BotSecretDTO;

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

function telegram_webhook_payload(): array
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
            'text' => 'Hello from Telegram!',
        ],
    ];
}

// ========== Run Tests ==========

echo "\n=== Offline Integration Tests: Commands & Webhook ===\n\n";

// =====================================================
// SECTION 1: public/index-sync.php flow
// =====================================================
echo "=== Section 1: public/index-sync.php webhook flow ===\n\n";

$secretService = new AutoSecretByTokenService();

// Test 1.1: IP Validator (used in public/index-sync.php)
echo "Test 1.1: TelegramIpValidator\n";
$ipValidator = new TelegramIpValidator();

$telegramIps = [
    '149.154.160.1',
    '149.154.165.255',
    '91.108.4.1',
    '91.108.7.255',
];

foreach ($telegramIps as $ip) {
    assert_test("Telegram IP $ip allowed", $ipValidator->validate($ip) === true);
}

$nonTelegramIps = [
    '8.8.8.8',
    '1.1.1.1',
    '192.168.1.1',
    '10.0.0.1',
];

foreach ($nonTelegramIps as $ip) {
    assert_test("Non-Telegram IP $ip rejected", $ipValidator->validate($ip) === false);
}

// Test 1.2: Secret generation and validation (used in public/index-sync.php)
echo "\nTest 1.2: Secret generation and validation\n";
$expectedSecret = $secretService->secret(TOKEN);
assert_test('secret format is botId:hash', preg_match('/^\d+:[a-f0-9]{64}$/', $expectedSecret) === 1);
assert_test('secret matches token', $expectedSecret === $secretService->secret(TOKEN));

$botId = $secretService->botId($expectedSecret);
assert_test('botId extracted from secret', $botId === '123456789');

// Test 1.3: Webhook payload processing (public/index-sync.php core logic)
echo "\nTest 1.3: Webhook payload processing (index-sync.php core)\n";
$registry = TypeDTOProcessorRegistry::build();
$messageCollector = new TestMessageCollectorProcessor();
$updateCollector = new TestTypeDTOCollectorProcessor();
$registry->register(MessageTypeDTO::class, $messageCollector);
$registry->register(UpdateTypeDTO::class, $updateCollector);

$webhook = TgBotSetupFactory::webhook($registry);
$result = $webhook->parse(telegram_webhook_payload(), $expectedSecret);

assert_test('webhook parse succeeds', $result === true);
assert_test('message collected', $messageCollector->count() === 1);
assert_test('update collected', $updateCollector->count() === 1);
assert_test('message text', $messageCollector->last()['dto']->text === 'Hello from Telegram!');
assert_test('botId from collector', $messageCollector->last()['botId'] === '123456789');

// Test 1.4: Invalid webhook secret (public/index-sync.php validation)
echo "\nTest 1.4: Invalid webhook secret\n";
$registry2 = TypeDTOProcessorRegistry::build();
$messageCollector2 = new TestMessageCollectorProcessor();
$registry2->register(MessageTypeDTO::class, $messageCollector2);
$webhook2 = TgBotSetupFactory::webhook($registry2);

$invalidResult = $webhook2->parse(telegram_webhook_payload(), 'wrong:secret');
assert_test('invalid secret returns false', $invalidResult === false);
assert_test('no messages collected on invalid secret', $messageCollector2->count() === 0);

// Test 1.5: Null secret (public/index-sync.php validation)
echo "\nTest 1.5: Null secret\n";
$nullResult = $webhook2->parse(telegram_webhook_payload(), null);
assert_test('null secret returns false', $nullResult === false);

// =====================================================
// SECTION 2: commands/poller-sync.php flow
// =====================================================
echo "\n=== Section 2: commands/poller-sync.php flow ===\n\n";

// Test 2.1: BotSecretDTO (used in poller-sync.php)
echo "Test 2.1: BotSecretDTO\n";
$botDTO = new BotSecretDTO(token: TOKEN);
assert_test('botId extracted', $botDTO->botId() === '123456789');
assert_test('token stored', $botDTO->token() === TOKEN);
assert_test('secret is null by default', $botDTO->secret() === null);

$botDTOWithSecret = new BotSecretDTO(token: TOKEN, secret: 'custom-secret');
assert_test('custom secret stored', $botDTOWithSecret->secret() === 'custom-secret');

// Test 2.2: BotSecretRegistry (used in poller-sync.php)
echo "\nTest 2.2: BotSecretRegistry\n";
$botRegistry = TgPureFactory::botSecretRegistry();
$botRegistry->register($botDTO);

assert_test('bot registered', $botRegistry->has($botDTO->botId()));
assert_test('getBot returns bot', $botRegistry->getBot($botDTO->botId()) === $botDTO);
assert_test('getBotCount', $botRegistry->getBotCount() === 1);

// Test 2.3: UpdateProcessor with registry (used in poller-sync.php)
echo "\nTest 2.3: UpdateProcessor with registry\n";
$registry = TypeDTOProcessorRegistry::build();
$messageCollector3 = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $messageCollector3);

$dispatcher = Mockery::mock(\BAGArt\TelegramBot\Contracts\Processing\Update\Processing\ProcessingDispatcherContract::class);
$dispatcher->shouldReceive('dispatch')->andReturnNull();
$config = new TgServiceConfig(dispatcher: $dispatcher);
$factory = TgBotSetupFactory::build();
$setup = $factory->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $registry,
);
$processor = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup,
);
$updateDTO = $webhook->makeDTO(telegram_webhook_payload());

$processor->process($updateDTO);
assert_test('processor dispatches to collectors', $messageCollector3->count() === 1);
assert_test('message text from processor', $messageCollector3->last()['dto']->text === 'Hello from Telegram!');

// Test 2.4: Multiple updates processed (poller-sync.php loop simulation)
echo "\nTest 2.4: Multiple updates processed\n";
$registry4 = TypeDTOProcessorRegistry::build();
$messageCollector4 = new TestMessageCollectorProcessor();
$registry4->register(MessageTypeDTO::class, $messageCollector4);
$webhook4 = TgBotSetupFactory::webhook($registry4);

$updates = [
    [
        'update_id' => 1,
        'message' => [
            'message_id' => 1,
            'from' => ['id' => 1, 'is_bot' => false, 'first_name' => 'A'],
            'chat' => ['id' => 1, 'type' => 'private'],
            'date' => 1000,
            'text' => 'First'
        ]
    ],
    [
        'update_id' => 2,
        'message' => [
            'message_id' => 2,
            'from' => ['id' => 1, 'is_bot' => false, 'first_name' => 'A'],
            'chat' => ['id' => 1, 'type' => 'private'],
            'date' => 1001,
            'text' => 'Second'
        ]
    ],
    [
        'update_id' => 3,
        'message' => [
            'message_id' => 3,
            'from' => ['id' => 1, 'is_bot' => false, 'first_name' => 'A'],
            'chat' => ['id' => 1, 'type' => 'private'],
            'date' => 1002,
            'text' => 'Third'
        ]
    ],
];

foreach ($updates as $update) {
    $webhook4->parse($update, $secretService->secret(TOKEN));
}

assert_test('all 3 updates processed', $messageCollector4->count() === 3);
$texts = array_map(fn ($item) => $item['dto']->text, $messageCollector4->collected);
assert_test('texts in order', $texts === ['First', 'Second', 'Third']);

// =====================================================
// SECTION 3: commands/webhook-processing.php flow
// =====================================================
echo "\n=== Section 3: commands/webhook-processing.php flow ===\n\n";

// Test 3.1: AutoSecretByTokenService (used in webhook-processing.php)
echo "Test 3.1: AutoSecretByTokenService secret generation\n";
$secret1 = $secretService->secret('111111111:AAAaaaBBBbbbCCCccc');
$secret2 = $secretService->secret('222222222:DDDdddEEEeeeFFFfff');

assert_test('different tokens -> different secrets', $secret1 !== $secret2);
assert_test('same token -> same secret', $secret1 === $secretService->secret('111111111:AAAaaaBBBbbbCCCccc'));
assert_test('botId from secret1', $secretService->botId($secret1) === '111111111');
assert_test('botId from secret2', $secretService->botId($secret2) === '222222222');

// Test 3.2: Invalid token format throws exception
echo "\nTest 3.2: Invalid token format throws exception\n";
$invalidTokens = [
    'no-colon',
    ':abc',
    'abc:',
    'abc:def',
    '',
];

foreach ($invalidTokens as $invalidToken) {
    try {
        $secretService->secret($invalidToken);
        assert_test("token '$invalidToken' should throw", false, 'Expected exception not thrown');
    } catch (\BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException $e) {
        assert_test("token '$invalidToken' throws TgBotInvalidSecretException", true);
    }
}

// Test 3.3: Invalid secret format throws exception
echo "\nTest 3.3: Invalid secret format throws exception\n";
$invalidSecrets = [
    null,
    'no-colon',
    ':abc',
    'abc:',
    'abc:def',
];

foreach ($invalidSecrets as $invalidSecret) {
    try {
        $secretService->botId($invalidSecret);
        assert_test(
            "secret '".var_export($invalidSecret, true)."' should throw",
            false,
            'Expected exception not thrown'
        );
    } catch (\BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException $e) {
        assert_test("secret '".var_export($invalidSecret, true)."' throws exception", true);
    }
}

// Test 3.4: Exception is caught by TgWebhookRequestParser
echo "\nTest 3.4: Exception caught by TgWebhookRequestParser\n";
$registry5 = TypeDTOProcessorRegistry::build();
$messageCollector5 = new TestMessageCollectorProcessor();
$registry5->register(MessageTypeDTO::class, $messageCollector5);
$webhook5 = TgBotSetupFactory::webhook($registry5);

$caughtExceptions = [
    null,
    '',
    'bad',
    '123:',
    ':hash',
];

foreach ($caughtExceptions as $badSecret) {
    $result = $webhook5->parse(telegram_webhook_payload(), $badSecret);
    assert_test("secret '".var_export($badSecret, true)."' returns false", $result === false);
}

assert_test('no messages collected on invalid secrets', $messageCollector5->count() === 0);

// Test 3.5: Valid secret still works after invalid attempts
echo "\nTest 3.5: Valid secret works after invalid attempts\n";
$validResult = $webhook5->parse(telegram_webhook_payload(), $secretService->secret(TOKEN));
assert_test('valid secret returns true', $validResult === true);
assert_test('message collected with valid secret', $messageCollector5->count() === 1);

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
