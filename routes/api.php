<?php

use Illuminate\Http\Request;
header('Access-Control-Allow-Origin:*');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::any('jsapi','JsapiController@index');//js_sdk

//Route::get('getAccessToken','ApiController@getAccessToken');//获取access_token
Route::post('redirectPost','ApiController@redirectPost');//post签名验证回调接口
//Route::post('getTokenTow','ApiController@getTokenTow');//获取机构token
Route::get('redirectGet','ApiController@redirectGet');//get回调接口
Route::get('clearCache','ApiController@clearCache');//清除缓存方法
//Route::get('getAlwaysPass','ApiController@getAlwaysPass');//获取永久通行证

Route::get('getUserInfo','ApiController@getUserInfo');//获取用户信息
Route::get('getUserSensitiveInfo','ApiController@getUserSensitiveInfo');//获取用户敏感信息
Route::get('getGroupTree','ApiController@getGroupTree');//获取组织树
Route::get('getGroupList','ApiController@getGroupList');//获取组织列表
Route::get('getGroupDown','ApiController@getGroupDown');//获取党组织下属机构
Route::get('getCommitteeList','ApiController@getCommitteeList');//获取委员会成员
Route::get('getListPeople','ApiController@getListPeople');//获取支部党员
Route::get('getGroupSingeInfo','ApiController@getGroupSingeInfo');//获取单个用户个人信息
Route::get('getUserInOrOut','ApiController@getUserInOrOut');//获取用户迁入迁出部记录
