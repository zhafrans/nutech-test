<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ServiceProductController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('register', 'register')->withoutMiddleware('auth:jwt');
        Route::post('login', 'login')->withoutMiddleware('auth:jwt');
        Route::post('logout', 'logout');
        Route::get('profile', 'getProfile');
        Route::put('profile/update', 'updateProfile');
        Route::post('profile/image', 'updateProfileImage');
        Route::get('balance', 'getBalance');
    });

Route::controller(BannerController::class)
    ->prefix('banners')
    ->group(function () {
        Route::get('list', 'list');
    });

Route::controller(ServiceProductController::class)
    ->prefix('service-products')
    ->group(function () {
        Route::get('list', 'list');
    });

Route::controller(TransactionController::class)
    ->prefix('transactions')
    ->group(function () {
        Route::post('topup', 'topup');
        Route::post('', 'store');
        Route::get('history', 'getHistory');
    });
