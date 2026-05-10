<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi;

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/../includes/examples/webhook-payloads.php';

$options = parseCommandOptions([
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/offline/update-mapper.php         # map webhook payloads to DTOs

Options:
  --help
  --log-level=debug|info|warning|error         # minimum log level (default: info)
";
    exit(0);
}

echo "\n=== Example: Webhook Mode (with DTO) ===\n";

$tgApiDTOMapper = TgPureFactory::dtoMapper();

foreach (getWebhookPayloads() as $updateRaw) {
    $update = $tgApiDTOMapper->fromArray(
        TgApi\Types\DTO\UpdateTypeDTO::class,
        $updateRaw,
    );
    assert($update instanceof TgApi\Types\DTO\UpdateTypeDTO);

    var_dump([
        'class' => $update::class,
        '$update' => $tgApiDTOMapper->toArray($update),
    ]);
}

echo "\nDone!\n";
