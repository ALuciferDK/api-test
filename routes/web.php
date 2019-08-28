<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::any('test','TestController@test');
Route::any('excel','TestController@excel');
Route::any('upd','TestController@upd');
Route::any('one','TestController@get_all_user_list');
Route::any('any','TestController@any');
Route::any('excurl','TestController@excurl');

Route::any('jsapi','JsapiController@index');
