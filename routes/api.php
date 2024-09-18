<?php

use App\Http\Controllers\ProductsController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'products'], function () {
    Route::get('/show', [ProductsController::class, 'getByNameAndCategory']);
    Route::get('/show-id/{id}', [ProductsController::class, 'getById']);
    Route::get('/show-category/{category}', [ProductsController::class, 'getByCategory']);
    Route::get('/show-images/{images}', [ProductsController::class, 'getByImage']);


    Route::post('/save', [ProductsController::class, 'post']);
    Route::put('/alter/{id}', [ProductsController::class, 'put']);
    Route::delete('/delete/{id}', [ProductsController::class, 'delete']);
});
