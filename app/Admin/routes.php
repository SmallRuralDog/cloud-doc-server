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


    $router->get("book-edit","BookController@edit")->name("book_edit");
    $router->any("book-get-tree","BookController@get_tree")->name("book_get_tree");
    $router->any("book-set-order","BookController@set_order")->name("book_set_order");
    $router->any("book-add-page","BookController@add_page")->name("book_add_page");

});
