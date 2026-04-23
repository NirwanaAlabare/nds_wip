<?php

use App\Http\Controllers\Marker\MarkerController;


Route::middleware('auth')->group(function () {
    // Marker
    Route::controller(MarkerController::class)->prefix("marker")->middleware('role:marker')->group(function () {
        Route::get('/', 'index')->name('marker');
        Route::get('/create', 'create')->name('create-marker');
        Route::post('/store', 'store')->name('store-marker');
        Route::get('/edit/{id?}', 'edit')->name('edit-marker');
        Route::put('/update/{id?}', 'update')->name('update-marker');
        Route::post('/show', 'show')->name('show-marker');
        Route::post('/show_gramasi', 'show_gramasi')->name('show_gramasi');
        Route::post('/update_status', 'update_status')->name('update_status');
        Route::put('/update_marker', 'update_marker')->name('update_marker');
        Route::post('/print-marker/{kodeMarker?}', 'printMarker')->name('print-marker');
        Route::post('/fix-marker-balance-qty', 'fixMarkerBalanceQty')->name('fix-marker-balance-qty');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('get-marker-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('get-marker-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('get-marker-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('get-marker-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('get-marker-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('get-marker-number');
    });
});
