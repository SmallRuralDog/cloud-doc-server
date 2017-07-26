<?php


Route::get('/', function () {
    return "微信搜索 “云档” 进入小程序";
});

Route::any("/collect/jike","JiKeController@content");
Route::any("/collect/sc","ShouCeController@collect");
Route::any("/test","JiKeController@test");
Route::any("/sc","ShouCeController@index");

Route::any("/sc_t","ShouCeController@collect");


//Auth::routes();


