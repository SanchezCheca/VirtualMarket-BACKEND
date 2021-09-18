<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CRUDController;
use App\Http\Controllers\API\PurchasesController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\StatsController;

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
Route::get('search/{search}', [SearchController::class, 'search']);
Route::get('getImage/{filename}', [ImageController::class, 'getImageByFilename']);

//----------------- PURCHASES
Route::post('purchase', [PurchasesController::class, 'buyProduct']);
Route::post('download', [PurchasesController::class, 'download']);

//----------------- CRUD
Route::post('getAllUsersData', [CRUDController::class, 'getAllUsersData']);
Route::post('updateUserCRUD', [CRUDController::class, 'updateUser']);
Route::post('removeUser', [CRUDController::class, 'removeUser']);
Route::post('getAdminStats', [StatsController::class, 'getStats']);