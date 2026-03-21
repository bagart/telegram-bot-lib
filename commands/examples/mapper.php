<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi;

require_once __DIR__.'/../../../../../vendor/autoload.php';

echo "\n=== Example: Webhook Mode (with DTO) ===\n";

$webhookPayloads = [
    [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
            ],
            'chat' => [
                'id' => 123456789,
                'type' => 'private',
            ],
            'date' => time(),
            'text' => '/start',
        ],
    ],
    [
        'update_id' => 123456790,
        'message' => [
            'message_id' => 2,
            'chat' => ['id' => 987654321, 'type' => 'private'],
            'date' => time(),
            'text' => 'Hello bot!',
        ],
    ],
    [
        'update_id' => 123456791,
        'callback_query' => [
            'id' => '123456789',
            'from' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'John',
            ],
            'chat_instance' => '123456789',
            'data' => 'button_click_1',
        ],
    ],
];

$tgApiDTOMapper = TgPureFactory::dtoMapper();
foreach ($webhookPayloads as $updateRaw) {
    $update = $tgApiDTOMapper->fromArray(
        TgApi\Types\DTO\UpdateTypeDTO::class,
        $updateRaw
    );
    assert($update instanceof TgApi\Types\DTO\UpdateTypeDTO);

    var_dump([
        'class' => $update::class,
        '$update' => $tgApiDTOMapper->toArray($update),
    ]);
}

echo "\nDone!\n";
