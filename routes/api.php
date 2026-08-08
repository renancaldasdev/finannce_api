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
use App\Http\Controllers\Category\DestroyCategoryController;
use App\Http\Controllers\Category\IndexCategoryController;
use App\Http\Controllers\Category\ShowCategoryController;
use App\Http\Controllers\Category\StoreCategoryController;
use App\Http\Controllers\Category\UpdateCategoryController;
use App\Http\Controllers\Transaction\DestroyTransactionController;
use App\Http\Controllers\Transaction\IndexTransactionController;
use App\Http\Controllers\Transaction\ShowTransactionController;
use App\Http\Controllers\Transaction\StoreTransactionController;
use App\Http\Controllers\Transaction\UpdateTransactionController;
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

    Route::prefix('categories')->group(function () {
        Route::get('/', IndexCategoryController::class);
        Route::get('/{category}', ShowCategoryController::class);
        Route::post('/', StoreCategoryController::class);
        Route::put('/{category}', UpdateCategoryController::class);
        Route::delete('/{category}', DestroyCategoryController::class);
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', IndexTransactionController::class);
        Route::get('/{transaction}', ShowTransactionController::class);
        Route::post('/', StoreTransactionController::class);
        Route::put('/{transaction}', UpdateTransactionController::class);
        Route::delete('/{transaction}', DestroyTransactionController::class);
    });
});
