<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\API\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//----------------- AUTH
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('updateUser/{username}', [AuthController::class, 'updateUser']);

//----------------- USERS
Route::post('getUserData', [AuthController::class, 'getUserData']);
Route::post('followUser', [AuthController::class, 'followUser']);
Route::post('unfollowUser', [AuthController::class, 'unfollowUser']);
Route::post('resetPassword', [AuthController::class, 'resetPassword']);

//----------------- IMAGES
Route::post('uploadImage', [ImageController::class, 'upload'])->middleware('auth:api');
Route::get('search/getLastImages', [SearchController::class, 'getLastImages']);
Route::get('search/getCategories', [SearchController::class, 'getCategories']);
