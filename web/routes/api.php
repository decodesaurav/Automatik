<?php

use App\Http\Controllers\API\CollectionController;
use App\Http\Controllers\API\TaskController;
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

Route::get('/', function () {
    return "Hello API";
});

Route::get("/collections",[CollectionController::class,"index"])->middleware('shopify.auth');
Route::post('/tasks', [TaskController::class, 'store'])->middleware('shopify.auth');
