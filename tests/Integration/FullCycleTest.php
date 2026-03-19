<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;
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

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Support/TestTypeDTOCollectorProcessor.php';


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

// ========== Payloads ==========

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
                'language_code' => 'en'
            ],
            'chat' => [
                'id' => 111222333,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'type' => 'private'
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
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
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
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
            'date' => 1700000100,
            'reply_to_message' => [
                'message_id' => 42,
                'from' => ['id' => 999888777, 'is_bot' => true, 'first_name' => 'TestBot', 'username' => 'testbot'],
                'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
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
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'message' => [
                'message_id' => 44,
                'from' => ['id' => 999888777, 'is_bot' => true, 'first_name' => 'TestBot', 'username' => 'testbot'],
                'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
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
            'sender_chat' => ['id' => -1001234567890, 'title' => 'Test Channel', 'type' => 'channel'],
            'chat' => ['id' => -1001234567890, 'title' => 'Test Channel', 'type' => 'channel'],
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
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => -1001234567891, 'title' => 'Test Group', 'type' => 'supergroup'],
            'date' => 1700000400,
            'text' => 'Hello group!',
        ],
    ];
}

function inlineQueryPayload(): array
{
    return [
        'update_id' => 987654327,
        'inline_query' => [
            'id' => '998877665544332211',
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'query' => 'search text',
            'offset' => '',
        ],
    ];
}

function stickerMessagePayload(): array
{
    return [
        'update_id' => 987654328,
        'message' => [
            'message_id' => 51,
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
            'date' => 1700000500,
            'sticker' => [
                'file_id' => 'AgACAgIAAxkBAAI',
                'file_unique_id' => 'AQADjq8xG5',
                'width' => 512,
                'height' => 512,
                'is_animated' => false,
                'is_video' => false,
                'type' => 'regular'
            ],
        ],
    ];
}

function photoMessagePayload(): array
{
    return [
        'update_id' => 987654329,
        'message' => [
            'message_id' => 52,
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
            'date' => 1700000600,
            'photo' => [
                [
                    'file_id' => 'AgACAgIAAxkBAAIB',
                    'file_unique_id' => 'AQADjq',
                    'width' => 90,
                    'height' => 68,
                    'file_size' => 1234
                ],
                [
                    'file_id' => 'AgACAgIAAxkBAAIC',
                    'file_unique_id' => 'AQADjq2',
                    'width' => 320,
                    'height' => 240,
                    'file_size' => 12345
                ],
            ],
            'caption' => 'A nice photo',
        ],
    ];
}

function forwardMessagePayload(): array
{
    return [
        'update_id' => 987654330,
        'message' => [
            'message_id' => 53,
            'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John', 'username' => 'johndoe'],
            'chat' => ['id' => 111222333, 'first_name' => 'John', 'username' => 'johndoe', 'type' => 'private'],
            'date' => 1700000700,
            'forward_origin' => [
                'type' => 'user',
                'date' => 1699999000,
                'sender_user' => ['id' => 444555666, 'is_bot' => false, 'first_name' => 'Alice']
            ],
            'text' => 'Forwarded message',
        ],
    ];
}

// ========== Run Tests ==========

echo "\n=== Integration Tests: Full Cycle (52 tests) ===\n\n";

$secretService = new AutoSecretByTokenService();
$secret = $secretService->secret(TOKEN);

// --- Section 1: Webhook Processing ---

echo "Section 1: Webhook Processing\n";

$r1 = TypeDTOProcessorRegistry::build();
$m1 = new TestMessageCollectorProcessor();
$u1 = new TestTypeDTOCollectorProcessor();
$r1->register(MessageTypeDTO::class, $m1);
$r1->register(UpdateTypeDTO::class, $u1);
$factory1 = TgBotSetupFactory::build();
$setup1 = $factory1->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r1,
);
$init1 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup1,
);
$r1->register(UpdateTypeDTO::class, $init1);
$w1 = TgBotSetupFactory::webhook($r1);
assert_test('1.1 parse text message', $w1->parse(messagePayload(), $secret) === true);
assert_test('1.1 update_id', $u1->last()['dto']->updateId === 987654321);
assert_test('1.1 message text', $m1->last()['dto']->text === 'Hello, bot!');
assert_test('1.1 chat_id', $m1->last()['dto']->chat->id === '111222333');
assert_test('1.1 from username', $m1->last()['dto']->from->username === 'johndoe');
assert_test('1.1 bot_id', $m1->last()['botId'] === '123456789');

$r2 = TypeDTOProcessorRegistry::build();
$m2 = new TestMessageCollectorProcessor();
$r2->register(MessageTypeDTO::class, $m2);
$factory2 = TgBotSetupFactory::build();
$setup2 = $factory2->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r2,
);
$init2 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup2,
);
$r2->register(UpdateTypeDTO::class, $init2);
TgBotSetupFactory::webhook($r2)->parse(editedMessagePayload(), $secret);
assert_test('1.2 edited text', $m2->last()['dto']->text === 'Hello, bot! (edited)');
assert_test('1.2 edit_date', $m2->last()['dto']->editDate === 1700000060);

$r3 = TypeDTOProcessorRegistry::build();
$m3 = new TestMessageCollectorProcessor();
$r3->register(MessageTypeDTO::class, $m3);
$factory3 = TgBotSetupFactory::build();
$setup3 = $factory3->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r3,
);
$init3 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup3,
);
$r3->register(UpdateTypeDTO::class, $init3);
TgBotSetupFactory::webhook($r3)->parse(replyMessagePayload(), $secret);
assert_test('1.3 reply text', $m3->last()['dto']->text === 'Hi back!');
assert_test('1.3 reply_to_message', $m3->last()['dto']->replyToMessage !== null);
assert_test('1.3 reply_message_id', $m3->last()['dto']->replyToMessage->messageId === 42);

$r4 = TypeDTOProcessorRegistry::build();
$m4 = new TestMessageCollectorProcessor();
$u4 = new TestTypeDTOCollectorProcessor();
$r4->register(MessageTypeDTO::class, $m4);
$r4->register(UpdateTypeDTO::class, $u4);
$factory4 = TgBotSetupFactory::build();
$setup4 = $factory4->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r4,
);
$init4 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup4,
);
$r4->register(UpdateTypeDTO::class, $init4);
TgBotSetupFactory::webhook($r4)->parse(callbackQueryPayload(), $secret);
assert_test('1.4 callback update', $u4->count() === 1);
assert_test('1.4 no message for callback', $m4->count() === 0);

$r5 = TypeDTOProcessorRegistry::build();
$m5 = new TestMessageCollectorProcessor();
$r5->register(MessageTypeDTO::class, $m5);
$factory5 = TgBotSetupFactory::build();
$setup5 = $factory5->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r5,
);
$init5 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup5,
);
$r5->register(UpdateTypeDTO::class, $init5);
TgBotSetupFactory::webhook($r5)->parse(channelPostPayload(), $secret);
assert_test('1.5 channel text', $m5->last()['dto']->text === 'Channel post text');

$r6 = TypeDTOProcessorRegistry::build();
$m6 = new TestMessageCollectorProcessor();
$r6->register(MessageTypeDTO::class, $m6);
$factory6 = TgBotSetupFactory::build();
$setup6 = $factory6->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r6,
);
$init6 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup6,
);
$r6->register(UpdateTypeDTO::class, $init6);
TgBotSetupFactory::webhook($r6)->parse(groupMessagePayload(), $secret);
assert_test('1.6 group text', $m6->last()['dto']->text === 'Hello group!');

$r7 = TypeDTOProcessorRegistry::build();
$u7 = new TestTypeDTOCollectorProcessor();
$m7 = new TestMessageCollectorProcessor();
$r7->register(UpdateTypeDTO::class, $u7);
$r7->register(MessageTypeDTO::class, $m7);
$factory7 = TgBotSetupFactory::build();
$setup7 = $factory7->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r7,
);
$init7 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup7,
);
$r7->register(UpdateTypeDTO::class, $init7);
TgBotSetupFactory::webhook($r7)->parse(inlineQueryPayload(), $secret);
assert_test('1.7 inline update', $u7->count() === 1);
assert_test('1.7 no message for inline', $m7->count() === 0);

$r8 = TypeDTOProcessorRegistry::build();
$m8 = new TestMessageCollectorProcessor();
$r8->register(MessageTypeDTO::class, $m8);
$factory8 = TgBotSetupFactory::build();
$setup8 = $factory8->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r8,
);
$init8 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup8,
);
$r8->register(UpdateTypeDTO::class, $init8);
TgBotSetupFactory::webhook($r8)->parse(stickerMessagePayload(), $secret);
assert_test('1.8 sticker collected', $m8->count() === 1);

$r9 = TypeDTOProcessorRegistry::build();
$m9 = new TestMessageCollectorProcessor();
$r9->register(MessageTypeDTO::class, $m9);
$factory9 = TgBotSetupFactory::build();
$setup9 = $factory9->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r9,
);
$init9 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup9,
);
$r9->register(UpdateTypeDTO::class, $init9);
TgBotSetupFactory::webhook($r9)->parse(photoMessagePayload(), $secret);
assert_test('1.9 photo caption', $m9->last()['dto']->caption === 'A nice photo');

$r10 = TypeDTOProcessorRegistry::build();
$m10 = new TestMessageCollectorProcessor();
$r10->register(MessageTypeDTO::class, $m10);
$factory10 = TgBotSetupFactory::build();
$setup10 = $factory10->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r10,
);
$init10 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup10,
);
$r10->register(UpdateTypeDTO::class, $init10);
TgBotSetupFactory::webhook($r10)->parse(forwardMessagePayload(), $secret);
assert_test('1.10 forward text', $m10->last()['dto']->text === 'Forwarded message');

$r11 = TypeDTOProcessorRegistry::build();
$m11 = new TestMessageCollectorProcessor();
$r11->register(MessageTypeDTO::class, $m11);
$factory11 = TgBotSetupFactory::build();
$setup11 = $factory11->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r11,
);
$init11 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup11,
);
$r11->register(UpdateTypeDTO::class, $init11);
$w11 = TgBotSetupFactory::webhook($r11);
$w11->parse(messagePayload(), $secret);
$w11->parse(editedMessagePayload(), $secret);
$w11->parse(replyMessagePayload(), $secret);
$w11->parse(channelPostPayload(), $secret);
assert_test('1.11 4 messages accumulated', $m11->count() === 4);
$m11->reset();
assert_test('1.12 reset clears', $m11->count() === 0);

assert_test(
    '1.13 invalid secret',
    TgBotSetupFactory::webhook(TypeDTOProcessorRegistry::build())->parse(messagePayload(), 'wrong:secret') === false
);
assert_test(
    '1.14 null secret',
    TgBotSetupFactory::webhook(TypeDTOProcessorRegistry::build())->parse(messagePayload(), null) === false
);
assert_test(
    '1.15 empty secret',
    TgBotSetupFactory::webhook(TypeDTOProcessorRegistry::build())->parse(messagePayload(), '') === false
);

// --- Section 2: public/index-sync.php ---

echo "\nSection 2: public/index-sync.php\n";

$ip = new TelegramIpValidator();
assert_test('2.1 ip allowed', $ip->validate('149.154.160.1') === true);
assert_test('2.1 ip rejected', $ip->validate('8.8.8.8') === false);
assert_test('2.2 secret format', preg_match('/^\d+:[a-f0-9]{64}$/', $secret) === 1);
assert_test('2.2 secret deterministic', $secret === $secretService->secret(TOKEN));
assert_test('2.2 botId', $secretService->botId($secret) === '123456789');

foreach (['no-colon', ':abc', 'abc:', ''] as $bad) {
    try {
        $secretService->secret($bad);
        assert_test("2.3 token '$bad'", false);
    } catch (TgBotInvalidSecretException) {
        assert_test("2.3 token '$bad' throws", true);
    }
}

$r25 = TypeDTOProcessorRegistry::build();
$m25 = new TestMessageCollectorProcessor();
$r25->register(MessageTypeDTO::class, $m25);
$w25 = TgBotSetupFactory::webhook($r25);
foreach ([null, '', 'bad'] as $bad) {
    assert_test("2.5 secret '".var_export($bad, true)."'", $w25->parse(messagePayload(), $bad) === false);
}
assert_test('2.5 no messages', $m25->count() === 0);
assert_test('2.6 valid after invalid', $w25->parse(messagePayload(), $secret) === true);

// --- Section 3: commands/poller-sync.php ---

echo "\nSection 3: commands/poller-sync.php\n";

$botDTO = new BotSecretDTO(token: TOKEN);
assert_test('3.1 botId', $botDTO->botId() === '123456789');
assert_test('3.1 token', $botDTO->token() === TOKEN);
assert_test('3.1 secret null', $botDTO->secret() === null);

$botReg = TgPureFactory::botSecretRegistry();
$botReg->register($botDTO);
assert_test('3.2 has bot', $botReg->has($botDTO->botId()));
assert_test('3.2 getBot', $botReg->getBot($botDTO->botId()) === $botDTO);

$r33 = TypeDTOProcessorRegistry::build();
$m33 = new TestMessageCollectorProcessor();
$r33->register(MessageTypeDTO::class, $m33);
$factory33 = TgBotSetupFactory::build();
$setup33 = $factory33->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r33,
);
$proc = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup33,
);
$upd33 = TgBotSetupFactory::webhook(TypeDTOProcessorRegistry::build())->makeDTO(messagePayload());
$proc->process($upd33);
assert_test('3.3 processor dispatches', $m33->count() === 1);

$r34 = TypeDTOProcessorRegistry::build();
$m34 = new TestMessageCollectorProcessor();
$r34->register(MessageTypeDTO::class, $m34);
$w34 = TgBotSetupFactory::webhook($r34);
foreach (
    [
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
    ] as $u
) {
    $w34->parse($u, $secret);
}
assert_test('3.4 3 updates', $m34->count() === 3);
assert_test('3.4 order', array_map(fn ($i) => $i['dto']->text, $m34->collected) === ['First', 'Second', 'Third']);

assert_test('3.5 different secrets', $secretService->secret('111:AAA') !== $secretService->secret('222:BBB'));

// --- Section 4: Edge Cases ---

echo "\nSection 4: Edge Cases\n";

$r41 = TypeDTOProcessorRegistry::build();
$m41 = new TestMessageCollectorProcessor();
$r41->register(MessageTypeDTO::class, $m41);
$factory41 = TgBotSetupFactory::build();
$setup41 = $factory41->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r41,
);
$init41 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup41,
);
$r41->register(UpdateTypeDTO::class, $init41);
$e41 = messagePayload();
$e41['message']['text'] = '';
TgBotSetupFactory::webhook($r41)->parse($e41, $secret);
assert_test('4.1 empty text', $m41->last()['dto']->text === '');

$r42 = TypeDTOProcessorRegistry::build();
$m42 = new TestMessageCollectorProcessor();
$r42->register(MessageTypeDTO::class, $m42);
$factory42 = TgBotSetupFactory::build();
$setup42 = $factory42->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r42,
);
$init42 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup42,
);
$r42->register(UpdateTypeDTO::class, $init42);
TgBotSetupFactory::webhook($r42)->parse(channelPostPayload(), $secret);
assert_test('4.2 channel from null', $m42->last()['dto']->from === null);

$r43 = TypeDTOProcessorRegistry::build();
$u43 = new TestTypeDTOCollectorProcessor();
$m43 = new TestMessageCollectorProcessor();
$r43->register(UpdateTypeDTO::class, $u43);
$r43->register(MessageTypeDTO::class, $m43);
$factory43 = TgBotSetupFactory::build();
$setup43 = $factory43->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r43,
);
$init43 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup43,
);
$r43->register(UpdateTypeDTO::class, $init43);
TgBotSetupFactory::webhook($r43)->parse(['update_id' => 999999999], $secret);
assert_test('4.3 empty update', $u43->count() === 1 && $m43->count() === 0);

$r44 = TypeDTOProcessorRegistry::build();
$m44 = new TestMessageCollectorProcessor();
$r44->register(MessageTypeDTO::class, $m44);
$factory44 = TgBotSetupFactory::build();
$setup44 = $factory44->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $r44,
);
$init44 = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup44,
);
$r44->register(UpdateTypeDTO::class, $init44);
$e44 = messagePayload();
$e44['message']['text'] = str_repeat('A', 4096);
TgBotSetupFactory::webhook($r44)->parse($e44, $secret);
assert_test('4.4 long text', strlen($m44->last()['dto']->text) === 4096);

$r45 = TypeDTOProcessorRegistry::build();
$m45 = new TestMessageCollectorProcessor();
$r45->register(MessageTypeDTO::class, $m45);
$e45 = messagePayload();
$e45['message']['text'] = "Hello <b>world</b> & \"quotes\" '123' 💩";
TgBotSetupFactory::webhook($r45)->parse($e45, $secret);
assert_test('4.5 special chars', $m45->last()['dto']->text === "Hello <b>world</b> & \"quotes\" '123' 💩");

$r46 = TypeDTOProcessorRegistry::build();
$m46 = new TestMessageCollectorProcessor();
$r46->register(MessageTypeDTO::class, $m46);
$e46 = messagePayload();
$e46['message']['text'] = 'Hello world! 你好世界';
TgBotSetupFactory::webhook($r46)->parse($e46, $secret);
assert_test('4.6 unicode', $m46->last()['dto']->text === 'Hello world! 你好世界');

assert_test('4.7 negative chat_id', $m6->last()['dto']->chat->id === '-1001234567891');

$r48 = TypeDTOProcessorRegistry::build();
$u48 = new TestTypeDTOCollectorProcessor();
$r48->register(UpdateTypeDTO::class, $u48);
$e48 = messagePayload();
$e48['update_id'] = 2147483647;
TgBotSetupFactory::webhook($r48)->parse($e48, $secret);
assert_test('4.8 large update_id', $u48->last()['dto']->updateId === 2147483647);

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
