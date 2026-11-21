<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Middleware\EnsureTenantToken;
use App\Http\Middleware\InitializeTenantRequest;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware(['web', PreventAccessFromCentralDomains::class, InitializeTenantRequest::class])
    ->get('/', fn () => 'This is your multi-tenant application. The id of the current tenant is '.tenant('id'));

Route::prefix('api')
    ->middleware(['api', PreventAccessFromCentralDomains::class, InitializeTenantRequest::class])
    ->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

        Route::middleware([EnsureTenantToken::class, 'auth:api'])->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::apiResource('users', UserController::class)
                ->middleware('can:manage users')
                ->where(['user' => '[0-9]+']);
            Route::apiResource('tasks', TaskController::class)->where(['task' => '[0-9]+']);
        });
    });
