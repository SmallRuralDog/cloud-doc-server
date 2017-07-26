<?php


Route::get('/', function () {
    return "微信搜索 “云档” 进入小程序";
});

Route::any("/collect/jike","JiKeController@content");

//Auth::routes();


