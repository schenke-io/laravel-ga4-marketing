<?php

use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;

test('it can generate a client id in the new format', function () {
    $generator = new ClientIdGenerator;
    $clientId = $generator->generate();

    expect($clientId)->toMatch('/^\d+\.\d+$/');
});

test('it can cache the client id', function () {
    $generator = new ClientIdGenerator;
    $clientId1 = $generator->getClientId();
    $clientId2 = $generator->getClientId();

    expect($clientId1)->toBe($clientId2);
});
