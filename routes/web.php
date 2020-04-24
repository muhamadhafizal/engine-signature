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

//mydocument
Route::get('/mydocument', 'MydocumentController@index');
Route::get('/mydocument/personal', 'MydocumentController@personal');
Route::get('/mydocument/group', 'MydocumentController@group');

Route::group(['middleware' => ['auth:api','token']], function(){
    
    //signature
    Route::get('/signature', 'SignatureController@index');
    Route::post('/signature/add', 'SignatureController@store');
    Route::get('/signature/details', 'SignatureController@details');
    Route::get('/signature/user', 'SignatureController@usersig');
    Route::delete('/signature/delete', 'SignatureController@destroy');


    //document
    Route::get('/document', 'DocumentController@index');
    Route::post('/document/add', 'DocumentController@store');
    Route::get('/document/details', 'DocumentController@details');
    Route::post('/document/edit', 'DocumentController@update');
    Route::delete('/document/delete', 'DocumentController@destroy');
    Route::get('/document/all', 'DocumentController@all');
    Route::get('/document/user', 'DocumentController@userdoc');

    //receive document
    Route::get('/receivedocument', 'ReceivedocumentController@index');
    Route::get('/receivedocument/user', 'ReceivedocumentController@userrecdoc');
    Route::post('/receivedocument/userupdate', 'ReceivedocumentController@userupdate');

});
