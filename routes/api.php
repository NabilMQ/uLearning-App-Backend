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
  Route::post('/auth/loginAdmin', 'UserController@loginAdmin');

  // Authentication Middleware
  Route::group(['middleware'=>['auth:sanctum']], function() {
    Route::any('/checkout/stripe', 'PayController@checkoutStripe');
    Route::any('/checkout/xendit', 'PayController@createInvoiceXendit');
    Route::any('/courseList', 'CourseController@courseList');
    Route::any('/courseListAdmin', 'CourseController@courseListAdmin');
    Route::any('/courseTypeListAdmin', 'CourseTypeController@courseTypeListAdmin');
    Route::any('/courseDetail', 'CourseController@courseDetail');
    Route::any('/userListAdmin', 'UserController@userListAdmin');
  });
});