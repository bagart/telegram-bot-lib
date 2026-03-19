<?php

require '/home/bagart/code/telegram-bot-platform/vendor/autoload.php';

$pending = new \GuzzleHttp\Promise\Promise(function () {
});
echo "Pending state: " . $pending->getState() . "\n";

$pending->resolve(['ok' => true]);
echo "After resolve state: " . $pending->getState() . "\n";
