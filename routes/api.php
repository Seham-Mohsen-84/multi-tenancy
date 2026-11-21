<?php

use App\Http\Controllers\Central\AdminAuthController;
use App\Http\Controllers\Central\DomainController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->prefix('admin')->group(function (): void {
        Route::post('login', [AdminAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AdminAuthController::class, 'me']);
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::apiResource('tenants', TenantController::class);
            Route::apiResource('tenants.domains', DomainController::class)->scoped();
        });
    });
}
