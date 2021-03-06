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


//Site General Routes
Route::get('/','SiteController@index');

//User auth routes
Route::get('/login','UserController@getLogin')->name('getLogin');
Route::post('/login','UserController@postLogin');
Route::get('/logout','UserController@logout');

//Dashboard Routes
Route::get('/dashboard','DashboardController@index')->name('dashboard');

//Deactivate Routes
Route::post('/deactivate','DeactivationController@deactivate');

//Reload Route
Route::get('/reloadScraper','ScraperController@reloadRecords');

//Search Routes
Route::get('/search','DashboardController@search');

//Doesn't Work
// Route::get('/stopScraper','ScraperController@stopScraper');