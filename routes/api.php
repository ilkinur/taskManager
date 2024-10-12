<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

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


Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('refresh', [ApiController::class, 'refreshToken']);
    Route::get('logout', [ApiController::class, 'logout']);

    Route::get('task', [ApiController::class,'getTasks']);
    Route::post('task', [ApiController::class,'createTask']);
    Route::post('taskDelete', [ApiController::class,'deleteTask']);
    Route::post('taskUpdate', [ApiController::class,'updateTask']);

});
