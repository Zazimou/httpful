<?php declare(strict_types=1);
// XML Example from GitHub
require(__DIR__ . '/../bootstrap.php');

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;

$uri = 'https://github.com/api/v2/xml/user/show/nategood';
try {
    $request = Request::get($uri)->send();

    echo "{$request->body->name} joined GitHub on " . date('M jS', strtotime($request->body->{'created-at'})) ."\n";

} catch (ConnectionErrorException $e) {
    echo 'Error: ' . $e->getCurlErrorString() . ' (' . $e->getCurlErrorNumber() . ")\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

