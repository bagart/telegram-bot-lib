<?php

require '/home/bagart/code/telegram-bot-platform/vendor/autoload.php';

// Check Guzzle Promise class
$ref = new ReflectionClass(\GuzzleHttp\Promise\Promise::class);
echo "Promise constructor params:\n";
$ctor = $ref->getConstructor();
if ($ctor) {
    foreach ($ctor->getParameters() as $p) {
        echo "  - " . $p->getName() . "\n";
    }
}

// Check if we can create a pending promise manually
echo "\nCreating pending promise...\n";
$pending = new \GuzzleHttp\Promise\Promise(function () {
});
var_dump($pending instanceof \GuzzleHttp\Promise\PromiseInterface);
var_dump(method_exists($pending, 'resolve'));
var_dump(method_exists($pending, 'reject'));
