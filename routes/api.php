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
        $api->get("index", "DocController@index");
        $api->get("class-list", "DocController@class_list");
        $api->get("list", "DocController@doc_class_list");
        $api->get("doc-page", "DocController@doc_page");
        $api->get("page", "DocController@page");
        $api->post("get-my-doc", "DocController@get_my_doc");
        $api->any("search", "DocController@search");
        $api->any("search-index", "DocController@search_index");
        $api->any("title-tip", "DocController@title_tip");
    });
    //V3版本
    $api->group([
        'namespace' => '\App\Http\Controllers\Api\V3',
        'middleware' => [],
        'prefix' => 'v3'
    ], function (Dingo\Api\Routing\Router $api) {
        $api->get("index", "DocController@index");
        $api->get("article-index", "ArticleController@index");
        $api->get("article-page", "ArticleController@page");
        $api->post("article-collect", "ArticleController@collect");
        $api->get("class-list", "DocController@doc_class_list");

        $api->get("info", "DocController@info");

        $api->any('login', 'UserController@login');
        $api->any('scan-login', 'UserController@scan_login');

        $api->group(['middleware' => ['before' => 'jwt.auth']], function (Dingo\Api\Routing\Router $api) {
            $api->get('user-index','UserController@index');
            $api->any('user-follow','UserController@user_follow');
            $api->any('user-follow-cancel','UserController@user_follow_cancel');

            //问答
            $api->post('wenda-upload-image','QuestionController@upload_img');
        });
    });

});
