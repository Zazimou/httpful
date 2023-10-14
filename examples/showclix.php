<?php

require(__DIR__ . '/../bootstrap.php');

use Httpful\Exception\ConnectionErrorException;
use Httpful\Handlers\JsonHandler;
use Httpful\Httpful;
use Httpful\Mime;
use Httpful\Request;

// Get event details for a public event
$uri = "https://api.showclix.com/Event/8175";
try {
    $response = Request::get($uri)
        ->expectsType('json')
        ->send();

    // Print out the event details
    echo "The event {$response->body->event} will take place on {$response->body->event_start}\n";

} catch (ConnectionErrorException $e) {
    echo 'Error: ' . $e->getCurlErrorString() . ' (' . $e->getCurlErrorNumber() . ")\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


// Example overriding the default JSON handler with one that encodes the response as an array
Httpful::register(Mime::JSON, new JsonHandler(array('decode_as_array' => true)));

try {
    $response = Request::get($uri)
        ->expectsType('json')
        ->send();

    // Print out the event details
    echo "The event {$response->body['event']} will take place on {$response->body['event_start']}\n";

} catch (ConnectionErrorException $e) {
    echo 'Error: ' . $e->getCurlErrorString() . ' (' . $e->getCurlErrorNumber() . ")\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

