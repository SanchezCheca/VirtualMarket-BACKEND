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

//----------------- IMAGES
Route::post('uploadImage', [ImageController::class, 'upload'])->middleware('auth:api');

Route::get('search/getLasts', [SearchController::class, 'getLasts']);

//----------------- AUTH
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);