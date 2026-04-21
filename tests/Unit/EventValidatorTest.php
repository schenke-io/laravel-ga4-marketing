<?php

use SchenkeIo\LaravelGa4Marketing\Services\EventValidator;

test('it robustly validates event names', function ($input, $expected) {
    $validator = new EventValidator;
    expect($validator->validateName($input))->toBe($expected);
})->with([
    'lowercase' => ['myevent', 'myevent'],
    'uppercase' => ['MYEVENT', 'myevent'],
    'ga_ prefix removal' => ['ga_session_start', 'session_start'],
    'GA_ prefix removal case-insensitive' => ['GA_SESSION_START', 'session_start'],
    'CamelCase to snake_case' => ['CamelCaseEvent', 'camel_case_event'],
    'special characters removal' => ['event@name!', 'eventname'],
    'multiple underscores reduction' => ['event__name', 'event_name'],
    'trimming underscores' => ['_event_name_', 'event_name'],
    'long name truncation' => [str_repeat('a', 50), str_repeat('a', 40)],
    'empty after validation' => ['@#!', ''],
    'google_ prefix removal' => ['google_event', 'event'],
    'firebase_ prefix removal' => ['firebase_event', 'event'],
    'starts with number' => ['123event', 'e_123event'],
    'spaces to nothing' => ['event name', 'eventname'],
    'mixed special chars' => ['!@#event$%^name&*()', 'eventname'],
]);
