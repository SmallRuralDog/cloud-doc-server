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
    $router->get("book-del-all","BookController@del_all")->name("book_del_all");

    $router->any("book-get-tree","BookController@get_tree")->name("book_get_tree");
    $router->any("book-set-order","BookController@set_order")->name("book_set_order");
    $router->any("book-add-page","BookController@add_page")->name("book_add_page");
    $router->any("book-del-page","BookController@del_page")->name("book_del_page");
    $router->any("book-add-edit-title","BookController@edit_title")->name("book_edit_title");
    $router->any("book-add-edit-content","BookController@edit_content")->name("book_edit_content");
    $router->any("book-add-save-content","BookController@save_content")->name("book_save_content");

    $router->any("book-collect-ky","BookController@collect_ky")->name("book_collect_ky");

    $router->any("collect-jk","CollectController@jk")->name("collect_jk");
    $router->any("collect-sc","CollectController@sc")->name("collect_sc");
    $router->any("collect-ky","CollectController@ky")->name("collect_ky");

    //广告
    $router->resource('ad_loca','AdLocaController');
    $router->resource('ad','AdController');

});
