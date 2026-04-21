<?php

use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;

test('it can generate a client id', function () {
    $generator = new ClientIdGenerator;
    $clientId = $generator->generate('127.0.0.1', 'test-ua');

    expect($clientId)->toBe(sha1('127.0.0.1test-ua'));
});

test('it uses a salt for client id if configured', function () {
    $generator = new ClientIdGenerator(null, 'my-salt');
    $clientId = $generator->generate('127.0.0.1', 'test-ua');

    expect($clientId)->toBe(sha1('127.0.0.1test-uamy-salt'));
});
