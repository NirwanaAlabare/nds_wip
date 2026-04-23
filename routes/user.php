<?php

use App\Http\Controllers\User\ManageAccessController;
use App\Http\Controllers\User\ManageRoleController;
use App\Http\Controllers\User\ManageUserController;
use App\Http\Controllers\User\ManageUserLineController;
use App\Http\Controllers\User\UserController;

Route::middleware('auth')->group(function () {

    // User
    Route::controller(UserController::class)->prefix("user")->group(function () {
        Route::put('/update/{id?}', 'update')->name('update-user');
    });

    // Manage User
    Route::controller(ManageUserController::class)->prefix("manage-user")->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('manage-user');
        Route::post('/store', 'store')->name('store-user');
        Route::put('/update', 'update')->name('update-user-detail');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-user');

        Route::get('/get-user-role', 'getUserRole')->name('get-user-role');
        Route::delete('/destroy-user-role/{id?}', 'destroyUserRole')->name('destroy-user-role');
    });

    // Manage Role
    Route::controller(ManageRoleController::class)->prefix("manage-role")->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('manage-role');
        Route::post('/store', 'store')->name('store-role');
        Route::put('/update', 'update')->name('update-role');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-role');

        Route::get('/get-role-access', 'getRoleAccess')->name('get-role-access');
        Route::delete('/destroy-role-access/{id?}', 'destroyRoleAccess')->name('destroy-role-access');
    });

    // Manage Access
    Route::controller(ManageAccessController::class)->prefix("manage-access")->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('manage-access');
        Route::post('/store', 'store')->name('store-access');
        Route::put('/update', 'update')->name('update-access');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-access');
    });

    // Manage
    Route::controller(ManageUserLineController::class)->prefix("manage-user-line")->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('manage-user-line');
        Route::post('/store', 'store')->name('store-user-line');
        Route::put('/update', 'update')->name('update-user-line');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-user-line');

        Route::get('/get-user-line-sub', 'getUserLineSub')->name('get-user-line-sub');
        Route::delete('/destroy-user-line-sub/{id?}', 'destroyUserLineSub')->name('destroy-user-line-sub');
    });

});
