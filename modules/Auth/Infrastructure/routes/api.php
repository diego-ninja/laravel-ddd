<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Infrastructure\Http\Endpoints\LoginEndpoint;
use Modules\Auth\Infrastructure\Http\Endpoints\LogoutEndpoint;
use Modules\Auth\Infrastructure\Http\Endpoints\RefreshTokenEndpoint;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Auth bounded context.
| These routes are automatically loaded by the AuthServiceProvider.
|
*/

Route::prefix('auth')->group(function () {
    // Public auth routes
    Route::post('/login', LoginEndpoint::class)->name('api.auth.login');
    
    // Protected auth routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', LogoutEndpoint::class)->name('api.auth.logout');
        Route::post('/refresh', RefreshTokenEndpoint::class)->name('api.auth.refresh');
    });
});