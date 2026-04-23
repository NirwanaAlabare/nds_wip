<?php

use App\Http\Controllers\Sewing\LineDashboardController;
use App\Http\Controllers\Sewing\LineWipController;
use App\Http\Controllers\Sewing\MasterDefectController;
use App\Http\Controllers\Sewing\MasterLineController;
use App\Http\Controllers\Sewing\MasterPlanController;
use App\Http\Controllers\Sewing\OrderDefectController;
use App\Http\Controllers\Sewing\ReportController;
use App\Http\Controllers\Sewing\ReportDefectController;
use App\Http\Controllers\Sewing\ReportDefectRejectController;
use App\Http\Controllers\Sewing\ReportDetailOutputController;
use App\Http\Controllers\Sewing\ReportEfficiencyController;
use App\Http\Controllers\Sewing\ReportEfficiencyNewController;
use App\Http\Controllers\Sewing\ReportFinishingProsesController;
use App\Http\Controllers\Sewing\ReportMutasiOutputController;
use App\Http\Controllers\Sewing\ReportOutputController;
use App\Http\Controllers\Sewing\ReportProductionController;
use App\Http\Controllers\Sewing\ReportRejectController;
use App\Http\Controllers\Sewing\SewingSecondaryMasterController;
use App\Http\Controllers\Sewing\SewingToolsController;
use App\Http\Controllers\Sewing\TrackOrderOutputController;
use App\Http\Controllers\Sewing\TransferOutputController;
use App\Http\Controllers\Sewing\UndoOutputController;
use App\Http\Controllers\Sewing\ReportHourlyController;

Route::middleware('auth')->group(function () {
    // Master Line
    Route::controller(MasterLineController::class)->prefix("master-line")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('master-line');
        Route::get('show/{line?}/{date?}', 'show')->name('master-line-detail');
        Route::get('create', 'create')->name('create-master-line');
        Route::post('store', 'store')->name('store-master-line');
        Route::put('update', 'update')->name('update-master-line');
        Route::delete('destroy/{id?}', 'destroy')->name('destroy-master-line');
        Route::post('export', 'exportExcel')->name('export-master-line');

        Route::post('update-image', 'updateImage')->name('update-master-line-image');

        Route::post('import-master-line', 'importMasterLine')->name('import-master-line');
        Route::get("tmp-master-line", 'tmpMasterLine')->name("tmp-master-line");
        Route::delete("destroy-tmp-master-line/{id?}", 'destroyTmpMasterLine')->name("destroy-tmp-master-line");
        Route::post('submit-imported-master-line', "submitImportedMasterLine")->name("submit-imported-master-line");
    });

    // Master Plan
    Route::controller(MasterPlanController::class)->prefix("master-plan")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('master-plan');
        Route::get('show/{line?}/{date?}', 'show')->name('master-plan-detail');
        Route::put('update', 'update')->name('update-master-plan');
        Route::post('store', 'store')->name('store-master-plan');
        Route::delete('destroy/{id?}', 'destroy')->name('destroy-master-plan');
        Route::post('/import-master-plan', 'importMasterPlan')->name('import-master-plan');
    });

    // Master Defect
    Route::controller(MasterDefectController::class)->prefix("master-defect")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('master-defect');

        Route::put('update-defect-type', 'updateDefectType')->name('update-defect-type');
        Route::post('store-defect-type', 'storeDefectType')->name('store-defect-type');
        Route::delete('destroy-defect-type/{id?}', 'destroyDefectType')->name('destroy-defect-type');

        Route::put('update-defect-area', 'updateDefectArea')->name('update-defect-area');
        Route::post('store-defect-area', 'storeDefectArea')->name('store-defect-area');
        Route::delete('destroy-defect-area/{id?}', 'destroyDefectArea')->name('destroy-defect-area');

        Route::post('merge-defect-type', 'mergeDefectType')->name('merge-defect-type');
        Route::post('merge-defect-area', 'mergeDefectArea')->name('merge-defect-area');
    });

    //  Master Secondary
    Route::controller(SewingSecondaryMasterController::class)->prefix("sewing-secondary-master")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-secondary-master');

        Route::put('update-secondary-master', 'updateSecondaryMaster')->name('update-sewing-secondary-master');
        Route::post('store-secondary-master', 'storeSecondaryMaster')->name('store-sewing-secondary-master');
        Route::delete('destroy-secondary-master/{id?}', 'destroySecondaryMaster')->name('destroy-sewing-secondary-master');
    });

    // Report Daily Sewing
    Route::controller(ReportController::class)->prefix('report')->middleware('role:sewing')->group(function () {
        Route::get('/index/{type}', 'index')->name("daily-sewing");
        Route::get('/defect-in-out', 'defectInOut')->name("report-defect-in-out");
        Route::post('/output/export', 'exportOutput');
        Route::post('/production/export', 'exportProduction');
        Route::post('/production/defect/export', 'exportProductionDefect');
        Route::post('/production-all/export', 'exportProductionAll');
        Route::post('/track-order-output/export', 'exportOrderOutput');
        Route::post('/defect-in-out/export', 'exportDefectInOut');
    });

    // Pareto Chart Defect Sewing
    Route::controller(OrderDefectController::class)->prefix('order-defects')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('order-defects');
        Route::get('/{buyerId?}/{dateFrom?}/{dateTo?}/{type?}', 'getOrderDefects')->name('get-order-defects');
    });

    // Track Order Output Sewing
    Route::controller(TrackOrderOutputController::class)->prefix('track-order-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-track-order-output');
    });

    // Transfer Output Sewing
    Route::controller(TransferOutputController::class)->prefix('transfer-output')->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('sewing-transfer-output');
    });

    // Dashboard List Sewing
    Route::controller(LineDashboardController::class)->prefix('line-dashboards')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-dashboard');
    });

    // Line WIP
    Route::controller(LineWipController::class)->prefix("line-wip")->middleware('role:sewing')->group(function () {
        Route::get('/index', 'index')->name('line-wip');
        Route::get('/total', 'total')->name('total-line-wip');
        Route::get('/export-excel', 'exportExcel')->name('export-excel-line-wip');
    });

    // Undo History
    Route::controller(UndoOutputController::class)->prefix("undo-output")->middleware("sewing")->group(function () {
        Route::get('/', 'history')->name("undo-output-history");
    });

    // Sewing Tools
    Route::controller(SewingToolsController::class)->prefix("sewing-tools")->middleware("role:superadmin")->group(function () {
        Route::get('/', 'index')->name("sewing-tools");

        // General synchronize (usually miss on update)
        Route::post('/miss-user', 'missUser')->name("sewing-miss-user");
        Route::post('/miss-masterplan', 'missMasterPlan')->name("sewing-miss-masterplan");
        Route::post('/miss-rework', 'missRework')->name("sewing-miss-rework");
        Route::post('/miss-reject', 'missReject')->name("sewing-miss-reject");
        Route::post('/miss-packing-po', 'missPackingPo')->name("sewing-miss-packing-po");

        // Check Output Detail
        Route::get('/check-output-detail', 'checkOutputDetail')->name("check-output-detail");
        Route::post('/check-output-detail-list', 'checkOutputDetailList')->name("check-output-detail-list");
        Route::post('/check-output-detail-export', 'checkOutputDetailExport')->name("check-output-detail-export");

        // Output Line Migration
        Route::get('/line-migration', 'lineMigration')->name("line-migration");
        Route::post('/line-migration-submit', 'lineMigrationSubmit')->name("line-migration-submit");

        // Modify Output Manual
        Route::get('/modify-output', 'modifyOutput')->name("modify-output");
        Route::post('/modify-output/action', 'modifyOutputAction')->name("modify-output-action");

        // Undo & Restore
        Route::get('/undo-output', 'undoOutput')->name("undo-output");
        Route::post('/undo-output-submit', 'undoOutputSubmit')->name("undo-output-submit");
        Route::get('/restore-undo', 'restoreUndo')->name("restore-undo");
        Route::post('/restore-undo-submit', 'restoreUndoSubmit')->name("restore-undo-submit");

        // Reject IN OUT
        Route::get('/undo-reject', 'undoReject')->name("undo-reject");
        Route::post('/undo-reject-submit', 'undoRejectSubmit')->name("undo-reject-submit");

        // Defect IN OUT
        Route::get('/undo-defect-in-out', 'undoDefectInOut')->name("undo-defect-in-out");
        Route::post('/undo-defect-in-out-submit', 'undoDefectInOutSubmit')->name("undo-defect-in-out-submit");

        // Print Line
        Route::post('/print-line-label', 'printLineLabel')->name("print-line-label");

        // Sewing Secondary
        Route::get('/modify-secondary-type', 'modifySecondaryType')->name("modify-secondary-type");
        Route::post('/get-output-secondary', 'getOutputSecondary')->name('get-output-secondary');
        Route::put('/update-secondary', 'updateSecondary')->name('update-secondary');
        Route::post('/undo-secondary', 'undoSecondary')->name('undo-secondary');

        // Modify Packing PO
        Route::get('/get-po', 'getPo')->name('get-po-qr');
        Route::post('/get-packing-po', 'getPackingPo')->name('get-packing-po');
        Route::get('/modify-packing-po', 'modifyPackingPo')->name('modify-packing-po');
        Route::put('/modify-packing-po/update', 'modifyPackingPoUpdate')->name('modify-packing-po-update');
        Route::post('/modify-packing-po/delete', 'modifyPackingPoDelete')->name('modify-packing-po-delete');
    });

    // Reporting
    Route::controller(ReportOutputController::class)->prefix('report-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportOutput');
        Route::get('/get-data', 'getData')->name('reportOutput.getData');
        Route::post('/export-data', 'exportData')->name('reportOutput.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportOutput.transferData');
    });

    Route::controller(ReportProductionController::class)->prefix('report-production')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportProduction');
        Route::get('/get-data', 'getData')->name('reportProduction.getData');
        Route::post('/export-data', 'exportData')->name('reportProduction.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportProduction.transferData');
    });

    Route::controller(ReportEfficiencyController::class)->prefix('report-efficiency')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportEfficiency');
        Route::get('/get-data', 'getData')->name('reportEfficiency.getData');
        Route::post('/export-data', 'exportData')->name('reportEfficiency.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportEfficiency.transferData');
    });

    // Report Defect
    Route::controller(ReportDefectController::class)->prefix('report-defect')->middleware('role:sewing')->group(function () {
        Route::get('/index', 'index')->name("report-defect");
        Route::get('/filter', 'filter')->name("filter-defect");
        Route::get('/total', 'total')->name("total-defect");
        Route::get('/update-date-from', 'updateDateFrom')->name("update-date-from-defect");

        Route::get('/defect-map', 'defectMap')->name("defect-map");
        Route::get('/defect-map/data', 'defectMapData')->name("defect-map-data");

        Route::post('/report-defect-export', 'reportDefectExport')->name("report-defect-export");
    });

    // Report Reject
    Route::controller(ReportRejectController::class)->prefix('report-reject')->middleware('role:sewing')->group(function () {
        Route::get('/index', 'index')->name("report-reject");
        Route::get('/filter', 'filter')->name("filter-reject");
        Route::get('/total', 'total')->name("total-reject");
        Route::get('/update-date-from', 'updateDateFrom')->name("update-date-from-reject");

        Route::get('/top', 'top')->name("top-reject");

        Route::post('/report-reject-export', 'reportRejectExport')->name("report-reject-export");
    });

    // Report Efficiency New
    Route::controller(ReportEfficiencyNewController::class)->prefix("report-efficiency-new")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportEfficiencynew');
        Route::get('/export_excel_rep_eff_new', 'export_excel_rep_eff_new')->name('export_excel_rep_eff_new');
    });

    // Report Defect Reject
    Route::controller(ReportDefectRejectController::class)->prefix("report-defect-reject")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportDefectReject');
        Route::get('/export_excel_report_defect_reject', 'export_excel_report_defect_reject')->name('export_excel_report_defect_reject');
    });

    // Report Finishing Proses
    Route::controller(ReportFinishingProsesController::class)->prefix("report-finishing-proses")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('report-finishing-proses');
        Route::get('/export_excel_report_finishing_proses', 'export_excel_report_finishing_proses')->name('export_excel_report_finishing_proses');
    });


    // Report Mutasi Output
    Route::controller(ReportMutasiOutputController::class)->prefix("report-mut-output")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('report_mut_output');
        Route::get('/show_mut_output', 'show_mut_output')->name('show_mut_output');
        Route::get('/export_excel_mut_output', 'export_excel_mut_output')->name('export_excel_mut_output');
    });

    Route::controller(ReportDetailOutputController::class)->prefix('report-detail-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportDetailOutput');
        Route::get('/packing', 'packing')->name('reportDetailOutputPacking');
        Route::get('/get-data', 'getData')->name('reportDetailOutput.getData');
        Route::post('/export-data', 'exportData')->name('reportDetailOutput.exportData');
        Route::post('/export-data-packing', 'exportDataPacking')->name('reportDetailOutput.exportDataPacking');
        Route::post('/transfer-data', 'transfer')->name('reportDetailOutput.transferData');
    });

    // Report Hourly Output
    Route::controller(ReportHourlyController::class)->prefix("laporan-report-hourly")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('report-hourly');
        Route::get('/export-excel-hourly', 'exportExcelHourly')->name('export-excel-hourly');
        Route::get('/export-excel-hourly-monthly', 'exportExcelHourlyMonthly')->name('export-excel-hourly-monthly');
        // Route::get('/show_lap_tracking_ppic', 'show_lap_tracking_ppic')->name('show_lap_tracking_ppic');
        // Route::get('/export_excel_tracking', 'export_excel_tracking')->name('export_excel_tracking');
    });
});
