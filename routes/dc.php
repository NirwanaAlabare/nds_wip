<?php

use App\Http\Controllers\DC\BonLoadingController;
use App\Http\Controllers\DC\DCInController;
use App\Http\Controllers\DC\DcReportController;
use App\Http\Controllers\DC\DcToolsController;
use App\Http\Controllers\DC\LoadingLineController;
use App\Http\Controllers\DC\LoadingOutController;
use App\Http\Controllers\DC\RackController;
use App\Http\Controllers\DC\RackStockerController;
use App\Http\Controllers\DC\SecondaryInController;
use App\Http\Controllers\DC\SecondaryInhouseInController;
use App\Http\Controllers\DC\SecondaryInhouseOutController;
use App\Http\Controllers\DC\StockDcCompleteController;
use App\Http\Controllers\DC\StockDcIncompleteController;
use App\Http\Controllers\DC\StockDcWipController;
use App\Http\Controllers\DC\TrolleyController;
use App\Http\Controllers\DC\TrolleyStockerController;

Route::middleware('auth')->group(function () {
    // // DC IN BACKUP
    // Route::controller(DCInController::class)->prefix("dc-in")->middleware('dc')->group(function () {
    //     Route::get('/', 'index')->name('dc-in');
    //     Route::get('/create/{no_form?}', 'create')->name('create-dc-in');
    //     Route::get('/getdata_stocker_info', 'getdata_stocker_info')->name('getdata_stocker_info');
    //     Route::get('/getdata_stocker_input', 'getdata_stocker_input')->name('getdata_stocker_input');
    //     Route::get('/getdata_dc_in', 'getdata_dc_in')->name('getdata_dc_in');
    //     Route::post('/show_tmp_dc_in', 'show_tmp_dc_in')->name('show_tmp_dc_in');
    //     Route::post('/get_alokasi', 'get_alokasi')->name('get_alokasi');
    //     Route::post('/get_det_alokasi', 'get_det_alokasi')->name('get_det_alokasi');
    //     Route::put('/update_tmp_dc_in', 'update_tmp_dc_in')->name('update_tmp_dc_in');
    //     Route::post('/store', 'store')->name('store_dc_in');
    //     Route::post('/simpan_final_dc_in', 'simpan_final_dc_in')->name('simpan_final_dc_in');
    //     Route::get('/getdata_stocker_history', 'getdata_stocker_history')->name('getdata_stocker_history');
    // });

    // DC IN
    Route::controller(DCInController::class)->prefix("dc-in")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('dc-in');
        Route::get('/detail_dc_in', 'detail_dc_in')->name('detail_dc_in');
        Route::get('/total_dc_in', 'total_dc_in')->name('total_dc_in');
        Route::get('/show_data_header', 'show_data_header')->name('show_data_header');
        Route::get('/create', 'create')->name('create-dc-in');
        Route::post('/store', 'store')->name('store-dc-in');
        Route::delete('/destroy', 'destroy')->name('destroy');

        Route::get('/filter_dc_in', 'filter_dc_in')->name('filter-dc-in');
        Route::get('/filter_detail_dc_in', 'filter_detail_dc_in')->name('filter-detail-dc-in');

        Route::get('/get_proses', 'get_proses')->name('get_proses_dc_in');
        Route::get('/get_tempat', 'get_tempat')->name('get_tempat');
        Route::get('/get_lokasi', 'get_lokasi')->name('get_lokasi');

        Route::get('/get_tmp_dc_in', 'get_tmp_dc_in')->name('get_tmp_dc_in');
        Route::get('/show_tmp_dc_in', 'show_tmp_dc_in')->name('show_tmp_dc_in');
        Route::post('/insert_tmp_dc_in', 'insert_tmp_dc_in')->name('insert_tmp_dc_in');
        Route::post('/mass_insert_tmp_dc_in', 'mass_insert_tmp_dc_in')->name('mass_insert_tmp_dc_in');
        Route::put('/update_tmp_dc_in', 'update_tmp_dc_in')->name('update_tmp_dc_in');
        Route::put('/update_mass_tmp_dc_in', 'update_mass_tmp_dc_in')->name('update_mass_tmp_dc_in');
        Route::delete('/delete_mass_tmp_dc_in', 'delete_mass_tmp_dc_in')->name('delete_mass_tmp_dc_in');

        Route::post('/export-excel', 'exportExcel')->name('dc-in-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('dc-in-detail-export-excel');

        Route::get('/dc-in-list', 'dcInList')->name('dc-in-list');
    });

    // Secondary INHOUSE IN
    Route::controller(SecondaryInhouseInController::class)->prefix("secondary-inhouse-in")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('secondary-inhouse-in');
        Route::get('/total_secondary_inhouse', 'total_secondary_inhouse_in')->name('total_secondary_inhouse_in');
        Route::get('/cek_data_stocker_inhouse', 'cek_data_stocker_inhouse')->name('cek_data_stocker_inhouse_in');
        Route::post('/store', 'storeSecondaryInhouseIn')->name('store-secondary-inhouse-in');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-inhouse-in');
        Route::get('/detail_stocker_inhouse', 'detail_stocker_inhouse')->name('detail_stocker_inhouse_in');

        // Temp
        Route::get('/cek_data_stocker_inhouse_temp', 'cek_data_stocker_inhouse_temp')->name('cek_data_stocker_inhouse_in_temp');
        Route::delete('/destroy-secondary-inhouse-in-temp/{id?}', 'destroySecondaryInhouseInTemp')->name('destroy-secondary-inhouse-in-temp');

        Route::get('/filter-sec-inhouse', 'filterSecondaryInhouse')->name('filter-sec-inhouse-in');
        Route::get('/filter-detail-sec-inhouse', 'filterDetailSecondaryInhouse')->name('filter-detail-sec-inhouse-in');

        Route::get('/export-excel', 'exportExcel')->name('secondary-inhouse-in-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('secondary-inhouse-in-detail-export-excel');
    });

    // Secondary INHOUSE
    Route::controller(SecondaryInhouseOutController::class)->prefix("secondary-inhouse")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('secondary-inhouse');
        Route::get('/total_secondary_inhouse_out', 'total_secondary_inhouse_out')->name('total_secondary_inhouse_out');
        Route::get('/cek_data_stocker_inhouse', 'cek_data_stocker_inhouse')->name('cek_data_stocker_inhouse');
        Route::post('/store', 'store')->name('store-secondary-inhouse');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-inhouse');
        Route::get('/detail_stocker_inhouse', 'detail_stocker_inhouse')->name('detail_stocker_inhouse');

        Route::get('/filter-sec-inhouse', 'filterSecondaryInhouse')->name('filter-sec-inhouse');
        Route::get('/filter-detail-sec-inhouse', 'filterDetailSecondaryInhouse')->name('filter-detail-sec-inhouse');

        Route::get('/export-excel', 'exportExcel')->name('secondary-inhouse-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('secondary-inhouse-detail-export-excel');
    });

    // Route::controller(SecondaryInhouseOutController::class)->prefix("secondary-inhouse")->middleware('role:dc')->group(function () {
    //     Route::get('/', 'index')->name('secondary-inhouse');
    //     Route::get('/total_stocker_inhouse', 'totalStockerInhouse')->name('total-stocker-inhouse');
    //     Route::get('/total_secondary_inhouse_out', 'total_secondary_inhouse_out')->name('total_secondary_inhouse_out');
    //     Route::get('/cek_data_stocker_inhouse', 'cek_data_stocker_inhouse')->name('cek_data_stocker_inhouse');
    //     Route::post('/store', 'store')->name('store-secondary-inhouse');
    //     Route::post('/mass-store', 'massStore')->name('mass-store-secondary-inhouse');
    //     Route::get('/detail_stocker_inhouse', 'detail_stocker_inhouse')->name('detail_stocker_inhouse');

    //     Route::get('/filter-sec-inhouse', 'filterSecondaryInhouse')->name('filter-sec-inhouse');
    //     Route::get('/filter-detail-sec-inhouse', 'filterDetailSecondaryInhouse')->name('filter-detail-sec-inhouse');

    //     Route::get('/export-excel', 'exportExcel')->name('secondary-inhouse-export-excel');
    //     Route::get('/export-excel-detail', 'exportExcelDetail')->name('secondary-inhouse-detail-export-excel');
    // });

    // Secondary IN
    Route::controller(SecondaryInController::class)->prefix("secondary-in")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('secondary-in');
        Route::get('/total_secondary_in', 'total_secondary_in')->name('total_secondary_in');
        Route::get('/cek_data_stocker_in', 'cek_data_stocker_in')->name('cek_data_stocker_in');
        Route::get('/cek_data_stocker_in_edit', 'cek_data_stocker_in_edit')->name('cek_data_stocker_in_edit');
        Route::post('/store', 'store')->name('store-secondary-in');
        Route::post('/update', 'update')->name('update-secondary-in');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-in');
        Route::get('/detail_stocker_in', 'detail_stocker_in')->name('detail_stocker_in');

        Route::get('/filter-sec-in', 'filterSecondaryIn')->name('filter-sec-in');
        Route::get('/filter-detail-sec-in', 'filterDetailSecondaryIn')->name('filter-detail-sec-in');

        Route::get('/export-excel', 'exportExcel')->name('secondary-in-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('secondary-in-detail-export-excel');
    });

    // Rack
    Route::controller(RackController::class)->prefix("rack")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('rack');
        Route::get('/create', 'create')->name('create-rack');
        Route::post('/store', 'store')->name('store-rack');
        Route::put('/update', 'update')->name('update-rack');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-rack');
        Route::post('/print-rack/{id?}', 'printRack')->name('print-rack');

        Route::get('/get-scanned-rack-detail/{id?}', 'getScannedRackDetail')->name('get-scanned-rack-detail');
    });

    // Rack Stocker
    Route::controller(RackStockerController::class)->prefix("stock-rack")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('stock-rack');
        Route::get('/allocate', 'allocate')->name('allocate-rack');
        Route::get('/current-rack-stock', 'currentRackStock')->name('current-rack-stock');
        Route::get('/stock-rack-visual', 'stockRackVisual')->name('stock-rack-visual');
        Route::get('/stock-rack-visual-detail', 'stockRackVisualDetail')->name('stock-rack-visual-detail');
        Route::post('/store', 'store')->name('store-rack-stock');
        Route::put('/update', 'update')->name('update-rack-stock');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-rack-stock');
        Route::post('/print-bon-mutasi/{id?}', 'printBonMutasi')->name('print-rack-stock');
    });

    // Trolley
    Route::controller(TrolleyController::class)->prefix("trolley")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('trolley');
        Route::get('/create', 'create')->name('create-trolley');
        Route::post('/store', 'store')->name('store-trolley');
        Route::put('/update', 'update')->name('update-trolley');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-trolley');
        Route::post('/print-trolley/{id?}', 'printTrolley')->name('print-trolley');
    });

    // Trolley Stocker
    Route::controller(TrolleyStockerController::class)->prefix("stock-trolley")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('stock-trolley');
        Route::post('/store', 'store')->name('store-trolley-stock');
        Route::put('/update', 'update')->name('update-trolley-stock');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-trolley-stock');
        Route::post('/print-bon-mutasi/{id?}', 'printBonMutasi')->name('print-trolley-stock');

        // allocate
        Route::get('/allocate', 'allocate')->name('allocate-trolley');
        Route::post('/store-allocate', 'storeAllocate')->name('store-allocate-trolley');
        Route::get('/allocate-this/{id?}', 'allocateThis')->name('allocate-this-trolley');
        Route::post('/store-allocate-this', 'storeAllocateThis')->name('store-allocate-this-trolley');

        // send
        Route::get('/send-trolley-stock/{id?}', 'send')->name('send-trolley-stock');
        Route::post('/submit-send-trolley-stock', 'submitSend')->name('submit-send-trolley-stock');

        // get data
        Route::get('/get-stocker-data/{id?}', 'getStockerData')->name('get-stocker-data-trolley-stock');
    });

    // Loading Stock
    Route::controller(LoadingLineController::class)->prefix("loading-line")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('loading-line');
        Route::get('/total-loading', 'totalLoading')->name('total-loading-line');
        Route::get('/detail/{id?}/{dateFrom?}/{dateTo?}', 'show')->name('detail-loading-plan');
        Route::get('/create', 'create')->name('create-loading-plan');
        Route::post('/store', 'store')->name('store-loading-plan');
        Route::get('/edit/{id?}', 'edit')->name('edit-loading-plan');
        Route::put('/update/{id?}', 'update')->name('update-loading-plan');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-loading-plan');
        Route::post('/export-loading-line', 'exportLoadingLine')->name('export-loading-line');
        Route::get('/summary', 'summary')->name('summary-loading');
        Route::get('/get-total-summary', 'getTotalSummary')->name('total-summary-loading');
        Route::post('/export-excel', 'exportExcel')->name('export-excel-loading');

        Route::get('/filter-summary-loading', 'filterSummary')->name('filter-summary-loading');

        Route::get('/modify-loading-line', 'modifyLoadingLine')->name('modify-loading-line');
        Route::post('/modify-loading-line/update', 'modifyLoadingLineUpdate')->name('modify-loading-line-update');
        Route::delete('/modify-loading-line/delete', 'modifyLoadingLineDelete')->name('modify-loading-line-delete');
    });

    // Loading Out
    Route::controller(LoadingOutController::class)->prefix("loading-out")->middleware('role:dc')->group(function () {
        Route::get('/loading_out', 'loading_out')->name('loading_out');
        Route::get('/loading_out_det', 'loading_out_det')->name('loading_out_det');
        Route::get('/input_loading_out', 'input_loading_out')->name('input_loading_out');
        Route::get('/getpo_loading_out', 'getpo_loading_out')->name('getpo_loading_out');
        Route::get('/get_list_po_loading_out', 'get_list_po_loading_out')->name('get_list_po_loading_out');
        Route::post('/get_loading_out_stocker_info', 'get_loading_out_stocker_info')->name('get_loading_out_stocker_info');
        Route::post('/save_tmp_stocker_loading_out', 'save_tmp_stocker_loading_out')->name('save_tmp_stocker_loading_out');
        Route::get('/get_list_tmp_scan_loading_out', 'get_list_tmp_scan_loading_out')->name('get_list_tmp_scan_loading_out');
        Route::post('/loading_out_delete_tmp', 'loading_out_delete_tmp')->name('loading_out_delete_tmp');
        Route::post('/save_loading_out', 'save_loading_out')->name('save_loading_out');
        Route::get('/get_info_modal_det_loading_out', 'get_info_modal_det_loading_out')->name('get_info_modal_det_loading_out');
        Route::get('/get_table_modal_det_loading_out', 'get_table_modal_det_loading_out')->name('get_table_modal_det_loading_out');
        Route::get('/get_table_modal_stocker_loading_out', 'get_table_modal_stocker_loading_out')->name('get_table_modal_stocker_loading_out');
        Route::post('/loading_out_konfirmasi', 'loading_out_konfirmasi')->name('loading_out_konfirmasi');
        Route::get('/get_det_summary_loading_out', 'get_det_summary_loading_out')->name('get_det_summary_loading_out');
        Route::get('/export_excel_loading_out', 'export_excel_loading_out')->name('export_excel_loading_out');
    });

    // Bon Loading
    Route::controller(BonLoadingController::class)->prefix("bon-loading")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('bon-loading-line');
        Route::post('/store', 'store')->name('store-bon-loading-line');
        Route::get('/history', 'history')->name('bon-loading-line-history');
    });

    // Stock DC Complete
    Route::controller(StockDcCompleteController::class)->prefix("stock-dc-complete")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('stock-dc-complete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-complete-detail');
    });

    // Stock DC Incomplete
    Route::controller(StockDcIncompleteController::class)->prefix("stock-dc-incomplete")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('stock-dc-incomplete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-incomplete-detail');
    });

    // Stock DC WIP
    Route::controller(StockDcWipController::class)->prefix("stock-dc-wip")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('stock-dc-wip');
        Route::get('/show/{partId?}', 'show')->name('stock-dc-wip-detail');
    });

    // DC Tools
    Route::controller(DcToolsController::class)->prefix("dc-tools")->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('dc-tools');
        Route::post('/empty-order-loading', 'emptyOrderLoading')->name('empty-order-loading');
        Route::post('/redundant-loading-plan', 'redundantLoadingPlan')->name('redundant-loading-plan');
        Route::get('/modify-dc-qty', 'modifyDcQty')->middleware('role:superadmin')->name('modify-dc-qty');
        Route::get('/get-dc-qty', 'getDcQty')->middleware('role:superadmin')->name('get-dc-qty');
        Route::post('/update-dc-qty', 'updateDcQty')->middleware('role:superadmin')->name('update-dc-qty');

        Route::put('/update-dc-in', 'updateDcIn')->name('update-dc-in');
        Route::delete('/delete-dc-in', 'deleteDcIn')->name('delete-dc-in');
    });

    // DC report
    Route::controller(DcReportController::class)->prefix("dc-report")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('dc-report');
        Route::post('/export-report-dc', 'exportReportDc')->name('export-report-dc');
    });
});
