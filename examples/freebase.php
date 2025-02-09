<?php declare(strict_types=1);
/**
 * Grab some The Dead Weather albums from Freebase
 */

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;

require(__DIR__ . '/../bootstrap.php');

$uri = "https://www.googleapis.com/freebase/v1/mqlread?query=%7B%22type%22:%22/music/artist%22%2C%22name%22:%22The%20Dead%20Weather%22%2C%22album%22:%5B%5D%7D";
try {
    $response = Request::get($uri)
        ->expectsJson()
        ->sendIt();

    echo 'The Dead Weather has ' . count($response->body->result->album) . " albums.\n";

} catch (ConnectionErrorException $e) {
    echo 'Error: ' . $e->getCurlErrorString() . ' (' . $e->getCurlErrorNumber() . ")\n";
}