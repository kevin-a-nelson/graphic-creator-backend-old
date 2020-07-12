<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// example event ids: 109495, 145229
Route::get("/test", "API\ExposureController@test");
// 13U = 446804
// 14U = 446803
// 15U = 446802
// 16U = 446801
// 17U = 424377
Route::get("/exposure/events/{event_id}", "API\ExposureController@getEvent");
