<?php

use Illuminate\Http\Request;

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function (Dingo\Api\Routing\Router $api) {
    $api->group(['namespace' => '\App\Http\Controllers\Api', 'middleware' => []], function (Dingo\Api\Routing\Router $api) {
        $api->get("list", "DocController@lists");
        $api->get("info", "DocController@info");
        $api->get("menu", "DocController@menu");
        $api->get("page", "DocController@page");
    });
});
