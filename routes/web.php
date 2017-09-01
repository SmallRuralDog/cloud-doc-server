<?php


Route::any("/collect/jike", "JiKeController@content");
Route::any("/collect/sc", "ShouCeController@collect");
Route::any("/collect/ky", "KanYunController@collect");
Route::any("/collect/wk", "WuKongController@collect");


//Route::any("/collect/doc", "DocCollectController@collect_doc");



//w3c
Route::any("/w3c-list", "W3cSchoolController@get_list");
Route::any("/collect/w3c", "W3cSchoolController@collect");

Route::any("/test", "JiKeController@test");
Route::any("/sc", "ShouCeController@index");

Route::any("/sc_t", "ShouCeController@collect");
Route::any("/ky", "KanYunController@index");
Route::any("/w3c", "W3cSchoolController@index");
Route::any("/w3c_c", "W3cSchoolController@collect");
Route::any("/git_book", "GitBookController@index");


//Auth::routes();
Route::group([
    'middleware' => ['web'],
], function () {

    Route::any('/login', 'Doc\UserController@login')->name('login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');
    Route::any('/check_login', 'Doc\UserController@check_login')->name('check_login');

    Route::group(['middleware' => ['auth']], function () {
        Route::any('/', 'Doc\HomeController@home')->name('index');
        //Route::get('home','Doc\HomeController@home')->name("home");
    });
});

