<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client(['timeout' => 5]);

try {
    $promise = Promise\Create::promiseFor(null)
        ->then(function () use ($client) {
            echo "Step 1: making request\n";
            return $client->request('POST', 'https://api.telegram.org/bot00000000:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/getMe', ['form_params' => []]);
        })
        ->then(function ($response) {
            echo "Step 2: got response\n";
            return json_decode((string)$response->getBody(), true);
        })
        ->then(null, function ($e) {
            echo "Error handler: " . get_class($e) . ' - ' . $e->getMessage() . "\n";
            throw $e;
        });

    $result = $promise->wait();
    echo "Result: " . json_encode($result) . "\n";
} catch (Throwable $e) {
    echo "Caught: " . get_class($e) . ' - ' . $e->getMessage() . "\n";
}
