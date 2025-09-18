<?php

use App\Http\Controllers\Auth\TenantPasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('tenant')->group(function () {
    // Show the reset form
    Route::get('reset-password/{token}', [TenantPasswordResetController::class, 'showResetForm'])
        ->name('tenant.password.reset');

    // Handle the reset POST
    Route::post('reset-password', [TenantPasswordResetController::class, 'reset'])
        ->name('tenant.password.update');
});
