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
