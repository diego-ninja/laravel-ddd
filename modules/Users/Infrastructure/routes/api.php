<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Infrastructure\Http\Endpoints\CreateUserEndpoint;
use Modules\Users\Infrastructure\Http\Endpoints\GetUsersEndpoint;

/*
|--------------------------------------------------------------------------
| Users Module API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Users bounded context.
| These routes are automatically loaded by the UsersServiceProvider.
|
*/

Route::prefix('users')->group(function () {
    // Public user routes
    Route::post('/', CreateUserEndpoint::class)->name('api.users.create');
    
    // Protected user routes
    Route::middleware('auth:api')->group(function () {
        Route::get('/', GetUsersEndpoint::class)->name('api.users.index');
    });
});