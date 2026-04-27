<?php

use App\Http\Controllers\Part\MasterPartController;
use App\Http\Controllers\Part\MasterSecondaryController;
use App\Http\Controllers\Part\PartController;

Route::middleware('auth')->group(function () {
    // Master Part
    Route::controller(MasterPartController::class)->prefix("master-part")->middleware('role:marker')->group(function () {
        Route::get('/', 'index')->name('master-part');
        Route::post('/store', 'store')->name('store-master-part');
        Route::put('/update/{id?}', 'update')->name('update-master-part');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-part');
    });

    // Master Secondary
    Route::controller(MasterSecondaryController::class)->prefix("master-secondary")->middleware('role:marker')->group(function () {
        Route::get('/', 'index')->name('master-secondary');
        Route::post('/store', 'store')->name('store-master-secondary');
        Route::get('/show_master_secondary', 'show_master_secondary')->name('show_master_secondary');
        Route::put('/update_master_secondary', 'update_master_secondary')->name('update_master_secondary');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-secondary');
    });

    // Part
    Route::controller(PartController::class)->prefix("part")->middleware('role:marker,cutting,stocker')->group(function () {
        Route::get('/', 'index')->name('part');
        Route::get('/create', 'create')->name('create-part');
        Route::post('/store', 'store')->name('store-part');
        Route::get('/edit', 'edit')->name('edit-part');
        Route::put('/update/{id?}', 'update')->name('update-part');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-part');

        // part form
        Route::get('/manage-part-form/{id?}', 'managePartForm')->name('manage-part-form');
        Route::get('/get-form-cut/{id?}', 'getFormCut')->name('get-part-form-cut');
        Route::post('/store-part-form', 'storePartForm')->name('store-part-form');
        Route::delete('/destroy-part-form', 'destroyPartForm')->name('destroy-part-form');
        Route::get('/show-part-form', 'showPartForm')->name('show-part-form');

        // part secondary
        Route::get('/manage-part-secondary/{id?}', 'managePartSecondary')->name('manage-part-secondary');
        Route::get('/datatable_list_part/{id?}', 'datatable_list_part')->name('datatable_list_part');
        Route::get('/datatable_list_part_complement/{id?}', 'datatable_list_part_complement')->name('datatable_list_part_complement');
        Route::get('/get_proses', 'get_proses')->name('get_proses');
        Route::post('/store_part_secondary', 'store_part_secondary')->name('store_part_secondary');
        Route::put('/update-part-secondary', 'updatePartSecondary')->name('update-part-secondary');
        Route::put('/update-part-secondary-complement', 'updatePartSecondaryComplement')->name('update-part-secondary-complement');

        // part detail
        Route::delete('/destroy-part-detail/{id?}', 'destroyPartDetail')->name('destroy-part-detail');
        Route::delete('/cancel-part-detail/{id?}', 'cancelPartDetail')->name('cancel-part-detail');
        Route::put('/uncancel-part-detail/{id?}', 'uncancelPartDetail')->name('uncancel-part-detail');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('get-part-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('get-part-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('get-part-panels');
        // get master part
        Route::get('/get-master-parts', 'getMasterParts')->name('get-master-parts');
        // get master tujuan
        Route::get('/get-master-tujuan', 'getMasterTujuan')->name('get-master-tujuan');
        // get master secondary
        Route::get('/get-master-secondary', 'getMasterSecondary')->name('get-master-secondary');
        // get complement panels
        Route::get('/get-complement-panels', 'getComplementPanelList')->name('get-part-complement-panels');
        // get complement panel parts
        Route::get('/get-complement-panel-parts', 'getComplementPanelPartList')->name('get-part-complement-panel-parts');

        // get part detail process
        Route::get('/get-edit-part-detail-process', 'getEditPartDetailProcess')->name('get-edit-part-detail-process');

        // get part detail items
        Route::get('/get-edit-part-detail-items', 'getEditPartDetailItems')->name('get-edit-part-detail-items');
    });
});
