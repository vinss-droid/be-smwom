<?php

use App\Http\Controllers\API\auth\AuthController;
use App\Http\Controllers\API\WorkOrderController;
use Illuminate\Support\Facades\Route;

//    Auth Route
Route::controller(AuthController::class)
    ->prefix('/auth')
    ->group(function () {
    Route::post('login', 'login')->name('auth.login');
    Route::get('logout', 'logout')->name('auth.logout')->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])
    ->group(function () {

//        work order route
        Route::prefix('/work-order')
            ->group(function () {
                Route::get('/', [WorkOrderController::class, 'index'])
                    ->name('work-order.index');

                Route::patch('{workOrder}', [WorkOrderController::class, 'update'])
                    ->name('work-order.update');

                Route::middleware('production_manager')
                    ->group(function () {
                        Route::post('/', [WorkOrderController::class, 'store'])
                            ->middleware('production_manager')
                            ->name('work-order.store');
                    });
            });

    });
