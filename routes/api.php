<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElementoController;
use App\Http\Controllers\MovimientoController;

Route::middleware(['auth:sanctum', 'active_user'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('role:asset_admin,app_admin')->name('api.')->group(function () {
        Route::apiResource('elementos', ElementoController::class);
        Route::apiResource('movimientos', MovimientoController::class)->only(['index', 'store']);
    });
});
