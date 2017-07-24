<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(['v1', 'v2'], function (Dingo\Api\Routing\Router $api) {
    //V1版本
    $api->group(['namespace' => '\App\Http\Controllers\Api', 'middleware' => []], function (Dingo\Api\Routing\Router $api) {
        $api->get("list", "DocController@lists");
        $api->get("info", "DocController@info");
        $api->get("menu", "DocController@menu");
        $api->get("page", "DocController@page");
    });
    //V2版本
    $api->group([
        'namespace' => '\App\Http\Controllers\Api\V2',
        'middleware' => [],
        'prefix' => 'v2'
    ], function (Dingo\Api\Routing\Router $api) {
        $api->get("class-list", "DocController@class_list");
        $api->get("list", "DocController@class_list");
    });
});
