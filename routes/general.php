<?php

use App\Http\Controllers\General\GeneralController;
use App\Http\Controllers\General\TrackController;
use App\Http\Controllers\General\WorksheetController;

Route::middleware('auth')->group(function () {
    // General
    Route::controller(GeneralController::class)->prefix("general")->group(function () {
        // generate unlock token
        Route::post('/generate-unlock-token', 'generateUnlockToken')->name('generate-unlock-token');
        // get order
        Route::get('/get-order', 'getOrderInfo')->name('get-general-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('get-general-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('get-general-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('get-general-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('get-general-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('get-general-number');
        // get no form
        Route::get('/get-no-form-cut', 'getNoFormCut')->name('get-no-form-cut');
        // get group
        Route::get('/get-form-group', 'getFormGroup')->name('get-form-group');
        // get stocker
        Route::get('/get-form-stocker', 'getFormStocker')->name('get-form-stocker');

        // new general
        // get buyers
        Route::get('/get-buyers-new', 'getBuyers')->name('get-buyers');
        // get orders
        Route::get('/get-orders-new', 'getOrders')->name('get-orders');
        // get colors
        Route::get('/get-colors-new', 'getColors')->name('get-colors');
        // get sizes
        Route::get('/get-sizes-new', 'getSizes')->name('get-sizes');
        // get po
        Route::get('/get-pos', 'getPos')->name('get-pos');
        // get panels new
        Route::get('/get-panels-new', 'getPanelListNew')->name('get-panels');

        // General Tools
        Route::get('/general-tools', 'generalTools')->middleware('role:superadmin')->name('general-tools');
        Route::post('/update-master-sb-ws', 'updateMasterSbWs')->middleware('role:superadmin')->name('update-master-sb-ws');
        Route::post('/update-general-order', 'updateGeneralOrder')->middleware('role:superadmin')->name('update-general-order');

        Route::post('/get-general-order-color-from', 'getGeneralOrderColorFrom')->middleware('role:superadmin')->name('get-general-order-color-from');
        Route::post('/get-general-order-color-to', 'getGeneralOrderColorTo')->middleware('role:superadmin')->name('get-general-order-color-to');
        Route::post('/update-general-order-color', 'updateGeneralOrderColor')->middleware('role:superadmin')->name('update-general-order-color');

        // get scanned employee
        Route::get('/get-scanned-employee/{id?}', 'getScannedEmployee')->name('get-scanned-employee');

        // cutting items
        Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-form-cut-input');
        Route::get('/get-item', 'getItem')->name('get-item-form-cut-input');

        // output
        Route::get('/get-output', 'getOutput')->name('get-output');
        Route::post('/get-output-post', 'getOutput')->name('get-output-post');

        // master plan
        Route::get('/get-master-plan', 'getMasterPlan')->name('get-master-plan');
        Route::get('/get-master-plan-detail/{id?}', 'getMasterPlanDetail')->name('get-master-plan-detail');
        Route::get('/get-master-plan-output', 'getMasterPlanOutput')->name('get-master-plan-output');
        Route::get('/get-master-plan-output-size', 'getMasterPlanOutputSize')->name('get-master-plan-output-size');

        // reject in out
        Route::get('/get-reject-in', 'getRejectIn')->name('get-reject-in');
        // defect in out
        Route::get('/get-defect-in-out', 'getDefectInOut')->name('get-defect-in-out');

        // Part Item
        Route::get('/get-part-item', 'getPartItemList')->name('get-part-item');

        // Item by WS, Color, Panel
        Route::get('/get-item-by-ws-color-panel', 'getItemByWsColorPanel')->name('get-item-by-ws-color-panel');
    });

    // Track
    Route::controller(TrackController::class)->prefix("track")->middleware('role:sewing,dc,cutting,stocker')->group(function () {
        // worksheet
        Route::get('/worksheet', 'worksheet')->name('track-ws');
        Route::post('/worksheet/export', 'worksheetExport')->name('track-ws-export');
        Route::get('/worksheet/show/{actCostingId?}', 'showWorksheet')->name('track-ws-detail');
        Route::get('/worksheet/show-part', 'wsPart')->name('track-ws-part');
        Route::get('/worksheet/show-part-id', 'wsPartId')->name('track-ws-part-id');
        Route::get('/worksheet/show-marker', 'wsMarker')->name('track-ws-marker');
        Route::get('/worksheet/show-marker-total', 'wsMarkerTotal')->name('track-ws-marker-total');
        Route::get('/worksheet/show-form', 'wsForm')->name('track-ws-form');
        Route::get('/worksheet/show-form-total', 'wsFormTotal')->name('track-ws-form-total');
        Route::get('/worksheet/show-roll', 'wsRoll')->name('track-ws-roll');
        Route::get('/worksheet/show-roll-total', 'wsRollTotal')->name('track-ws-roll-total');
        Route::get('/worksheet/show-stocker', 'wsStocker')->name('track-ws-stocker');
        Route::get('/worksheet/show-stocker-total', 'wsStockerTotal')->name('track-ws-stocker-total');
        Route::get('/worksheet/ws-sewing-output', 'wsSewingOutput')->name('track-ws-sewing-output');

        // stocker
        Route::get('/stocker', 'stocker')->name('track-stocker');
        Route::get('/stocker/show/{actCostingId?}', 'showStocker')->name('track-stocker-detail');
        Route::post('/stocker/export', 'stockerExport')->name('track-stocker-export');
    });

    // Worksheet
    Route::controller(WorksheetController::class)->prefix("worksheet")->group(function () {
        // get worksheet
        Route::get('/', 'index')->name('worksheet');
        Route::post('/print-qr', 'printQr')->name('worksheet-print-qr');
    });
});
