<?php

/**
 * Return sample webhook payloads for testing.
 *
 * @return array<int, array<string, mixed>>  list of raw Telegram update arrays
 */
function getWebhookPayloads(): array
{
    return [
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
        // Spam: private message invitation + promotional text
        [
            'update_id' => 123456792,
            'message' => [
                'message_id' => 3,
                'from' => [
                    'id' => 111222333,
                    'is_bot' => false,
                    'first_name' => 'Spammer',
                    'username' => 'spammer123',
                ],
                'chat' => [
                    'id' => -1001234567890,
                    'type' => 'group',
                ],
                'date' => time(),
                'text' => 'Hello everyone! Write to me in private, I sell a course on making money online. 50% discount!',
            ],
        ],
    ];
}
