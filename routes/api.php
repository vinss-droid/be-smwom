<?php

use App\Http\Controllers\API\auth\AuthController;
use App\Http\Controllers\API\WorkOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//    Auth Route
Route::controller(AuthController::class)
    ->prefix('/auth')
    ->group(function () {
    Route::post('login', 'login')->name('auth.login');
    Route::get('logout', 'logout')->name('auth.logout')->middleware(['auth:sanctum']);
});

// Work Order Routes
Route::prefix('/work-order')
    ->middleware(['auth:sanctum'])
    ->controller(WorkOrderController::class)
    ->group(function () {

        Route::post('/update/{id}', 'updateWorkOrder')->name('work-order.update');

        Route::middleware(['pm_role'])->group(function () {
            Route::post('/create', 'createWorkOrder')->name('work-order.create');
        });

    });
