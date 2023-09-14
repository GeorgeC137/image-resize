<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AlbumController;
use App\Http\Controllers\V1\ImageManipulationController;

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::apiResource('/album', AlbumController::class);
        Route::get('/image', [ImageManipulationController::class, 'index']);
        Route::get('/image/{image}', [ImageManipulationController::class, 'show']);
        Route::get('/image/by-album/{album}', [ImageManipulationController::class, 'byAlbum']);
        Route::post('/image/resize', [ImageManipulationController::class, 'resize']);
        Route::delete('/image/{id}', [ImageManipulationController::class, 'destroy']);
    });
});

