<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => "App\Admin\Controllers",
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource("doc-class","DocClassController");
    $router->resource("doc-menu","DocMenuController");
    $router->resource("doc-page","DocPageController");
    $router->resource("doc","DocController");

    $router->any("api/collect-ky","DocPageController@collect_ky")->name("collect_ky");

});
