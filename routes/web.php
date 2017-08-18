<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::any("/collect/jike", "JiKeController@content");
Route::any("/collect/sc", "ShouCeController@collect");
Route::any("/collect/ky", "KanYunController@collect");
Route::any("/collect/wk", "WuKongController@collect");


//w3c
Route::any("/w3c-list", "W3cSchoolController@get_list");
Route::any("/collect/w3c", "W3cSchoolController@collect");

Route::any("/test", "JiKeController@test");
Route::any("/sc", "ShouCeController@index");

Route::any("/sc_t", "ShouCeController@collect");
Route::any("/ky", "KanYunController@index");
Route::any("/w3c", "W3cSchoolController@index");
Route::any("/w3c_c", "W3cSchoolController@collect");


//Auth::routes();
Route::group([
    'middleware' => ['web'],
], function (Router $router) {
    $router->any('/', 'HomeController@index');
    $router->any('/login', 'Doc\UserController@login')->name('login');
    $router->post('logout', 'Auth\LoginController@logout')->name('logout');
    $router->any('/check_login', 'Doc\UserController@check_login')->name('check_login');
});

