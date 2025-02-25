<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['namespace' => 'Api'], function() {
  Route::post('/auth/register', 'UserController@createUser');
  Route::post('/auth/login', 'UserController@loginUser');

  // Authentication Middleware
  Route::group(['middleware'=>['auth:sanctum']], function() {
    Route::any('/checkout', 'PayController@checkout');
    Route::any('/courseList', 'CourseController@courseList');
    Route::any('/courseDetail', 'CourseController@courseDetail');
  });
});