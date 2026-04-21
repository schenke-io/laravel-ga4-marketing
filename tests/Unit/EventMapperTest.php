<?php

use SchenkeIo\LaravelGa4Marketing\Services\EventMapper;

test('it maps arguments to parameters correctly', function ($method, $args, $expected) {
    $mapper = new EventMapper;
    expect($mapper->mapArgumentsToParams($method, $args))->toEqual($expected);
})->with([
    'pageView' => ['pageView', ['http://loc', 'Title', 'http://ref', 'en'], [
        'page_location' => 'http://loc',
        'page_title' => 'Title',
        'page_referrer' => 'http://ref',
        'language' => 'en',
    ]],
    'click' => ['click', ['http://loc', 'Text', 'ID', 'class', 'domain', true], [
        'link_url' => 'http://loc',
        'link_text' => 'Text',
        'link_id' => 'ID',
        'link_classes' => 'class',
        'link_domain' => 'domain',
        'outbound' => true,
    ]],
    'login' => ['login', ['password'], ['method' => 'password']],
    'signUp' => ['signUp', ['email'], ['method' => 'email']],
    'share' => ['share', ['link', 'article', '123'], [
        'method' => 'link',
        'content_type' => 'article',
        'item_id' => '123',
    ]],
    'search' => ['search', ['query'], ['search_term' => 'query']],
    'viewItem' => ['viewItem', [[['item_id' => 'I1']], 'USD', 10.0], [
        'items' => [['item_id' => 'I1']],
        'currency' => 'USD',
        'value' => 10.0,
    ]],
    'addToCart' => ['addToCart', [[['item_id' => 'I2']], 'EUR', 20.0], [
        'items' => [['item_id' => 'I2']],
        'currency' => 'EUR',
        'value' => 20.0,
    ]],
    'beginCheckout' => ['beginCheckout', [[['item_id' => 'I3']], 'USD', 30.0, 'COUPON1'], [
        'items' => [['item_id' => 'I3']],
        'currency' => 'USD',
        'value' => 30.0,
        'coupon' => 'COUPON1',
    ]],
    'purchase' => ['purchase', ['T123', [['item_id' => 'I4']], 40.0, 'USD', 2.0, 5.0, 'COUPON2'], [
        'transaction_id' => 'T123',
        'items' => [['item_id' => 'I4']],
        'value' => 40.0,
        'currency' => 'USD',
        'tax' => 2.0,
        'shipping' => 5.0,
        'coupon' => 'COUPON2',
    ]],
    'scroll' => ['scroll', [90], ['percent_scrolled' => 90]],
    'fileDownload' => ['fileDownload', ['file.pdf', 'pdf', 'url', 'text', 'id', 'class', 'domain'], [
        'file_name' => 'file.pdf',
        'file_extension' => 'pdf',
        'link_url' => 'url',
        'link_text' => 'text',
        'link_id' => 'id',
        'link_classes' => 'class',
        'link_domain' => 'domain',
    ]],
    'calculatorUsed' => ['calculatorUsed', [['p' => 'v']], ['p' => 'v']],
    'unknown' => ['unknown', [], []],
    'missing args' => ['purchase', [], [
        'transaction_id' => null,
        'items' => [],
        'value' => null,
        'currency' => null,
        'tax' => null,
        'shipping' => null,
        'coupon' => null,
    ]],
]);
