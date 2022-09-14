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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth', [App\Http\Controllers\API\AuthController::class, 'auth']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function(Request $request) {
        return auth()->user();
    });

    // API route for logout user
    Route::post('/qontak/getCustName', [App\Http\Controllers\API\QontakController::class, 'getCustName']);
    Route::post('/qontak/getBillInfo', [App\Http\Controllers\API\QontakController::class, 'getBillInfo']);
    Route::post('/qontak/postComplain', [App\Http\Controllers\API\QontakController::class, 'postComplain']);

    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);
});
