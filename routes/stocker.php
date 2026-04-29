<?php

use App\Http\Controllers\Stocker\StockerController;
use App\Http\Controllers\Stocker\StockerRejectController;
use App\Http\Controllers\Stocker\StockerToolsController;
use App\Http\Controllers\Stocker\YearSequenceController;

Route::middleware('auth')->group(function () {
    // Stocker
    Route::controller(StockerController::class)->prefix("stocker")->middleware('role:cutting')->group(function () {
        Route::get('/', 'index')->name('stocker');
        Route::get('/show/{formCutId?}', 'show')->name('show-stocker');
        Route::post('/print-stocker/{index?}', 'printStocker')->name('print-stocker');
        Route::post('/print-stocker-all-size/{partDetailId?}', 'printStockerAllSize')->name('print-stocker-all-size');
        Route::post('/print-stocker-checked', 'printStockerChecked')->name('print-stocker-checked');
        Route::post('/print-stocker-checked-add', 'printStockerCheckedAdd')->name('print-stocker-checked-add');
        Route::post('/print-numbering/{index?}', 'printNumbering')->name('print-numbering');
        Route::post('/print-numbering-checked', 'printNumberingChecked')->name('print-numbering-checked');
        Route::post('/full-generate-numbering', 'fullGenerateNumbering')->name('full-generate-numbering');
        Route::post('/fix-redundant-stocker', 'fixRedundantStocker')->name('fix-redundant-stocker');
        Route::post('/fix-redundant-numbering', 'fixRedundantNumbering')->name('fix-redundant-numbering');
        Route::put('/count-stocker-update', 'countStockerUpdate')->name('count-stocker-update');

        Route::get('/stocker-part', 'part')->name('stocker-part');

        Route::get('/show-pcs/{formCutId?}', 'showPcs')->name('show-stocker-pcs');
        Route::post('/print-stocker-pcs/{index?}', 'printStockerPcs')->name('print-stocker-pcs');
        Route::post('/print-stocker-all-size-pcs/{partDetailId?}', 'printStockerAllSizePcs')->name('print-stocker-all-size-pcs');
        Route::post('/print-stocker-checked-pcs', 'printStockerCheckedPcs')->name('print-stocker-checked-pcs');

        // adjust
        Route::post('/rearrange-group', 'rearrangeGroup')->name('rearrange-group');
        Route::post('/reorder-stocker-numbering', 'reorderStockerNumbering')->name('reorder-stocker-numbering');
        Route::post('/modify-size-qty', 'modifySizeQty')->name('modify-size-qty');

        // part form
        Route::get('/manage-part-form/{id?}', 'managePartForm')->name('stocker-manage-part-form');
        Route::get('/get-form-cut/{id?}', 'getFormCut')->name('stocker-get-part-form-cut');
        Route::post('/store-part-form', 'storePartForm')->name('stocker-store-part-form');
        Route::delete('/destroy-part-form', 'destroyPartForm')->name('stocker-destroy-part-form');
        Route::get('/show-part-form', 'showPartForm')->name('stocker-show-part-form');

        // part secondary
        Route::get('/manage-part-secondary/{id?}', 'managePartSecondary')->name('stocker-manage-part-secondary');
        Route::get('/datatable_list_part/{id?}', 'datatable_list_part')->name('stocker-datatable_list_part');
        Route::get('/get_proses', 'get_proses')->name('stocker-get_proses');
        Route::post('/store_part_secondary', 'store_part_secondary')->name('stocker-store_part_secondary');

        // get stocker
        Route::get('/get-stocker', 'getStocker')->name('get-stocker');

        // add
        Route::post('/print-stocker-all-size-add', 'printStockerAllSizeAdd')->name('print-stocker-all-size-add');
        Route::post('/submit-stocker-add', 'submitStockerAdd')->name('submit-stocker-add');

        // stocker reject
        Route::post('/print-stocker-reject-all-size/{partDetailId?}', 'printStockerRejectAllSize')->name('print-stocker-reject-all-size');
        Route::post('/print-stocker-reject-checked', 'printStockerRejectChecked')->name('print-stocker-reject-checked');
        Route::post('/print-stocker-reject/{id?}', 'printStockerReject')->name('print-stocker-reject');

        // separate stocker
        Route::post('/separate-stocker', 'separateStocker')->name('separate-stocker');
    });

    // Year Sequence Label
    Route::controller(YearSequenceController::class)->prefix("stocker")->middleware("role:cutting,stocker,dc")->group(function () {
        // stocker list
        Route::get('/stocker-list', 'stockerList')->name('stocker-list');
        Route::get('/stocker-list-total', 'stockerListTotal')->name('stocker-list-total');
        Route::get('/stocker-list/export', 'stockerListExport')->name('stocker-list-export');
        Route::get('/stocker-list/detail/{form_cut_id?}/{group_stocker?}/{ratio?}/{so_det_id?}/{normal?}', 'stockerListDetail')->name('stocker-list-detail');
        Route::get('/stocker-list/detail/export/{form_cut_id?}/{group_stocker?}/{ratio?}/{so_det_id?}/{normal?}', 'stockerListDetailExport')->name('stocker-list-detail-export');
        Route::get('/stocker-list/check-year-sequence', 'checkYearSequenceNumber')->name('check-year-sequence-number');
        Route::post('/stocker-list/set-month-count', 'setMonthCountNumber')->name('set-month-count-number');
        Route::post('/stocker-list/set-year-sequence', 'setYearSequenceNumber')->name('set-year-sequence-number');
        Route::post('/stocker-list/check-all-stock-number', 'checkAllStockNumber')->name('check-all-stock-number');
        Route::post('/stocker-list/print-stock-number', 'printStockNumber')->name('print-stock-number');

        // month count
        Route::get('/month-count', 'customMonthCount')->name('month-count');
        Route::get('/month-count/get-range', 'getRangeMonthCount')->name('get-range-month-count');
        Route::post('/month-count/print', 'printMonthCount')->name('print-month-count');
        Route::post('/month-count/print-checked', 'printMonthCountChecked')->name('print-month-count-checked');

        // year sequence
        Route::get('/year-sequence', 'yearSequence')->name('year-sequence');
        Route::get('/year-sequence/get-sequence', 'getSequenceYearSequence')->name('get-sequence-year-sequence');
        Route::get('/year-sequence/get-range', 'getRangeYearSequence')->name('get-range-year-sequence');
        Route::post('/year-sequence/print', 'printYearSequence')->name('print-year-sequence');
        Route::post('/year-sequence/print-new', 'printYearSequenceNew')->name('print-year-sequence-new');
        Route::post('/year-sequence/print-new-format', 'printYearSequenceNewFormat')->name('print-year-sequence-new-format');
        // Route::post('/year-sequence/print-checked', 'printYearSequenceChecked')->name('print-year-sequence-checked');

        // modify year sequence
        Route::get('/modify-year-sequence', 'modifyYearSequence')->name('modify-year-sequence');
        Route::post('/modify-year-sequence-list', 'modifyYearSequenceList')->name('modify-year-sequence-list');
        Route::post('/modify-year-sequence-update', 'modifyYearSequenceUpdate')->name('update-year-sequence');
        Route::post('/modify-year-sequence-delete', 'modifyYearSequenceDelete')->name('delete-year-sequence');

        // get stocker
        Route::get('/get-stocker-month-count', 'getStockerMonthCount')->name('get-stocker-month-count');
        Route::get('/get-stocker-year-sequence', 'getStockerYearSequence')->name('get-stocker-year-sequence');
    });

    // Stocker Reject
    Route::controller(StockerRejectController::class)->prefix('stocker-reject')->middleware('role:stocker,dc')->group(function () {
        Route::get('/index', 'index')->name('stocker-reject');
        Route::get('/get-stocker-reject', 'getStockerReject')->name('get-stocker-reject');
        Route::get('/get-stocker-reject-process', 'getStockerRejectProcess')->name('get-stocker-reject-process');
        Route::get('/show/{id?}/{process?}', 'show')->name('show-stocker-reject');
        Route::get('/create', 'create')->name('create-stocker-reject');
        Route::post('/print-stocker-process-reject/{id?}', 'printStocker')->name('print-stocker-process-reject');
        Route::post('/store', 'storeStockerProcessReject')->name('store-stocker-reject');

        Route::post('/export', 'exportStockerReject')->name('export-stocker-reject');
    });

    // Stocker Tools
    Route::controller(StockerToolsController::class)->prefix("stocker")->middleware('role:superadmin')->group(function () {
        // form
        Route::get('/index', 'index')->name('stocker-tools');

        // reset stocker
        Route::post('/reset-stocker-id', 'resetStockerId')->name('reset-stocker-id');
        Route::post('/reset-stocker-form', 'resetStockerForm')->name('reset-stocker-form');
        Route::post('/reset-redundant-stocker', 'resetRedundantStocker')->name('reset-redundant-stocker');
        Route::post('/restore-stocker-log', 'restoreStockerLog')->name('restore-stocker-log');

        Route::post('/import-stocker-manual', 'importStockerManual')->name('import-stocker-manual');

        Route::post('/rearrange-groups', 'rearrangeGroups')->name('rearrange-groups');
        Route::post('/recalculate-stocker-transaction', 'recalculateStockerTransaction')->name('recalculate-stocker-transaction');

        Route::delete('/undo-stocker-additional', 'undoStockerAdditional')->name('undo-stocker-additional');
    });
});
