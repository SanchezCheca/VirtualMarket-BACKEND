<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PublicImageController;

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

Route::get('/test', function() {
    return view('test');
});

Route::post('/test', [ImageController::class, 'store']);
Route::get('/test/{image}', 'ImageController@show');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//--------PUBLIC IMAGES
Route::get('/thumbnail/{filename}', [PublicImageController::class, 'getThumbnail']);
Route::get('/sample/{filename}', [PublicImageController::class, 'getSample']);
Route::get('profileImage/{filename}', [PublicImageController::class, 'getProfileImage']);
Route::get('download/{filename}', [PublicImageController::class, 'download']);
