<?php


Route::get('/', function () {
    return "微信搜索 “云档” 进入小程序";
});

Route::any("/collect/jike","JiKeController@content");
Route::any("/collect/sc","ShouCeController@collect");
Route::any("/collect/ky","KanYunController@collect");
Route::any("/test","JiKeController@test");
Route::any("/sc","ShouCeController@index");

Route::any("/sc_t","ShouCeController@collect");
Route::any("/ky","KanYunController@index");
Route::any("/w3c","W3cSchoolController@index");
Route::any("/w3c_c","W3cSchoolController@collect");



//Auth::routes();


