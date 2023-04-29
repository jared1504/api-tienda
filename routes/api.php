<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;

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


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    //proteger rutas -> solo autenticados
    Route::get('logout', [AuthController::class, 'logout']);

    //rutas productos
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products/store', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);


    //rutas Clientes
    Route::get('clients', [ClientController::class, 'index']);
    Route::post('clients/store', [ClientController::class, 'store']);
    Route::get('clients/{client}', [ClientController::class, 'show']);
    Route::put('clients/{client}', [ClientController::class, 'update']);
    Route::delete('clients/{client}', [ClientController::class, 'destroy']);


    //rutas Sales
    Route::get('sales', [SaleController::class, 'index']);
    Route::post('sales/store', [SaleController::class, 'store']);

    //rutas sales por fecha
    Route::get('sales/date', [SaleController::class, 'salesdate']);

    Route::get('sales/{sale}', [SaleController::class, 'show']);
    Route::put('sales/{sale}', [SaleController::class, 'update']);
    Route::delete('sales/{sale}', [SaleController::class, 'destroy']);


    //rutas Orders
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders/store', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::put('orders/{order}', [OrderController::class, 'update']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);

    //rutas sales por usuarios
    Route::get('sales/user/{user}', [SaleController::class, 'salesuser']);

    //rutas orders por fecha
    Route::get('orders/date', [OrderController::class, 'ordersdate']);





    //rutas orders por usuarios
    Route::get('orders/user/{user}', [OrderController::class, 'ordersuser']);
});
