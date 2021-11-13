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

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/pending', 'HomeController@pending');
Route::get('/review', 'HomeController@review');
Route::get('/history', 'HomeController@history');
Route::get('/config', 'HomeController@config');
Route::get('/fb/parse/groups', 'HomeController@fbGroupParser');
Route::get('/fb/parse/pages', 'HomeController@fbPageParser');



Auth::routes();


