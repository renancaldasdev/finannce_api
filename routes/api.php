<?php

use App\Http\Controllers\Account\DestroyAccountController;
use App\Http\Controllers\Account\IndexAccountController;
use App\Http\Controllers\Account\ShowAccountController;
use App\Http\Controllers\Account\StoreAccountController;
use App\Http\Controllers\Account\UpdateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogOutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'pong',
    ]);
});

Route::post('/login', LoginController::class);
Route::post('/register', RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update', UpdateController::class);
    Route::post('/logout', LogOutController::class);

    Route::prefix('accounts')->group(function () {
        Route::get('/', IndexAccountController::class);
        Route::get('/{account}', ShowAccountController::class);
        Route::put('/{account}', UpdateAccountController::class);
        Route::post('/', StoreAccountController::class);
        Route::delete('/{account}', DestroyAccountController::class);
    });
});
