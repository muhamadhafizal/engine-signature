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

//user
Route::get('/user', 'UserController@index');
Route::get('/user/all', 'UserController@all');
Route::post('/user/register', 'UserController@add');
Route::get('/user/profile', 'UserController@profile');
Route::post('/user/edit', 'UserController@update');
Route::delete('/user/delete', 'UserController@destroy');

//Login
Route::get('/login', 'LoginController@index');
Route::post('/login', 'LoginController@main');

Route::get('unauthorized', ['as' => 'unauthorized', 'uses' => 'LoginController@unauthorized']);

Route::group(['middleware' => ['auth:api','token']], function(){
    
    //signature
    Route::get('/signature', 'SignatureController@index');
    
    Route::get('/signature/details', 'SignatureController@details');
    Route::get('/signature/user', 'SignatureController@usersig');
    Route::delete('/signature/delete', 'SignatureController@destroy');
    Route::post('/signature/add', 'SignatureController@store');


    //document
    Route::get('/document/index', 'DocumentController@index');
    Route::post('/document/add', 'DocumentController@store');
    Route::get('/document/details', 'DocumentController@details');
    Route::post('/document/edit', 'DocumentController@update');
    Route::delete('/document/delete', 'DocumentController@destroy');
    Route::get('/document/all', 'DocumentController@all');
    Route::get('/document/user', 'DocumentController@userdoc');
    Route::get('/document/detailstosign', 'DocumentController@detailstosign');
    Route::post('/document/successsign', 'DocumentController@successsign');
    Route::get('/document/listtosign', 'DocumentController@listtosign');

    //receive document
    Route::get('/receivedocument', 'ReceivedocumentController@index');
    Route::get('/receivedocument/user', 'ReceivedocumentController@userrecdoc');
    Route::post('/receivedocument/userupdate', 'ReceivedocumentController@userupdate');

    //mydocument
    Route::get('/mydocument', 'MydocumentController@index');
    Route::get('/mydocument/personal', 'MydocumentController@personal');
    Route::get('/mydocument/group', 'MydocumentController@group');

    //verify
    Route::get('/verify', 'VerifyController@index');
    Route::post('/verify/requesttac', 'VerifyController@requesttac');
    Route::post('/verify/sendtac', 'VerifyController@sendtac');

    //Category
    Route::get('/category', 'CategoryController@index');
    Route::get('/category/all', 'CategoryController@all');
    
});
