<?php

use App\Http\Controllers\Cutting\RollController;
use App\Http\Controllers\Cutting\SpreadingController;
use App\Http\Controllers\Cutting\CompletedFormController;
use App\Http\Controllers\Cutting\CuttingFormController;
use App\Http\Controllers\Cutting\CuttingFormManualController;
use App\Http\Controllers\Cutting\CuttingFormPieceController;
use App\Http\Controllers\Cutting\CuttingFormPilotController;
use App\Http\Controllers\Cutting\CuttingFormRejectController;
use App\Http\Controllers\Cutting\CuttingPlanController;
use App\Http\Controllers\Cutting\CuttingToolsController;
use App\Http\Controllers\Cutting\GantiRejectController;
use App\Http\Controllers\Cutting\MasterPipingController;
use App\Http\Controllers\Cutting\PenerimaanCuttingController;
use App\Http\Controllers\Cutting\PipingController;
use App\Http\Controllers\Cutting\PipingLoadingController;
use App\Http\Controllers\Cutting\PipingProcessController;
use App\Http\Controllers\Cutting\PipingStockController;
use App\Http\Controllers\Cutting\ReportCuttingController;

Route::middleware('auth')->group(function () {
    // Spreading
    Route::controller(SpreadingController::class)->prefix("spreading")->middleware('role:cutting')->group(function () {
        Route::get('/', 'index')->name('spreading');
        Route::get('/create', 'create')->name('create-spreading');
        Route::post('/getno_marker', 'getno_marker')->name('getno_marker');
        Route::get('/getdata_marker', 'getdata_marker')->name('getdata_marker');
        Route::get('/getdata_ratio', 'getdata_ratio')->name('getdata_ratio');
        Route::post('/store', 'store')->name('store-spreading');
        Route::put('/update', 'update')->name('update-spreading');
        Route::get('/get-order-info', 'getOrderInfo')->name('get-spreading-data');
        Route::get('/get-cut-qty', 'getCutQty')->name('get-cut-qty-data');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-spreading');
        Route::post('/export', 'exportExcel')->name('export-cutting-form');
        Route::post('/export-pdf', 'exportPdf')->name('export-cutting-form-pdf');
        // export excel
        // Route::get('/export_excel', 'export_excel')->name('export_excel');
        // Route::get('/export', 'export')->name('export');
    });

    Route::controller(SpreadingController::class)->prefix("spreading")->middleware('role:superadmin')->group(function () {
        Route::put('/update-status', 'updateStatus')->name('update-status');
        Route::put('/update-status-redirect', 'updateStatusRedirect')->name('update-status-redirect');
    });

    // Penerimaan Cutting
    Route::controller(PenerimaanCuttingController::class)->prefix("penerimaan-cutting")->middleware("role:cutting")->group(function () {
        Route::get('/', 'index')->name('penerimaan-cutting');
        Route::get('/create', 'create')->name('create-penerimaan-cutting');
        Route::post('/store', 'store')->name('store-penerimaan-cutting');
        Route::get('/edit/{id?}', 'edit')->name('edit-penerimaan-cutting');
        Route::post('/update', 'update')->name('update-penerimaan-cutting');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-penerimaan-cutting');
        Route::post('/export', 'exportPenerimaanCutting')->name('export-penerimaan-cutting');
        Route::get('/get-scanned-item/{id?}', 'getBarcodeFabric')->name('get-scanned-penerimaan-cutting');
    });

    // CUTTING :
        // Form Cut Input
        Route::controller(CuttingFormController::class)->prefix("form-cut-input")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('form-cut-input');
            Route::get('/process/{id?}', 'process')->name('process-form-cut-input');
            Route::get('/get-number-data', 'getNumberData')->name('get-number-form-cut-input');
            Route::put('/start-process/{id?}', 'startProcess')->name('start-process-form-cut-input');
            Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-form-cut-input');
            Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-form-cut-input');
            Route::get('/get-time-record/{id?}/{noForm?}', 'getTimeRecord')->name('get-time-form-cut-input');
            Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-form-cut-input');
            Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-form-cut-input');
            Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-form-cut-input');
            Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-form-cut-input');
            Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-form-cut-input');
            Route::get('/check-spreading-form/{id?}/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-form-cut-input');
            Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-form-cut-input');
            Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-form-cut-input');
            Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-form-cut-input');
            Route::get('/get-form-cut-ratio', 'getRatio')->name('get-form-cut-ratio');

            Route::get('/check-sambungan/{id?}', 'checkSambungan')->name('check-sambungan');

            Route::get('/store-sambungan', 'storeSambungan')->name('store-sambungan');

            // get order
            Route::get('/get-order', 'getOrderInfo')->name('form-cut-get-marker-order');
            // get colors
            Route::get('/get-colors', 'getColorList')->name('form-cut-get-marker-colors');
            // get panels
            Route::get('/get-panels', 'getPanelList')->name('form-cut-get-general-panels');
            // get sizes
            Route::get('/get-sizes', 'getSizeList')->name('form-cut-get-marker-sizes');
            // get count
            Route::get('/get-count', 'getCount')->name('form-cut-get-marker-count');
            // get number
            Route::get('/get-number', 'getNumber')->name('form-cut-get-marker-number');

            // no cut update
            Route::put('/update-no-cut', 'updateNoCut')->name('form-cut-update-no-cut');

            // lock form
            Route::post('/form-cut-lock', 'formCutLock')->name('form-cut-lock');
            // unlock form
            Route::post('/form-cut-unlock', 'formCutUnlock')->name('form-cut-unlock');
        });

    // MANUAL :
        // Manual Form Cut Input
        Route::controller(CuttingFormManualController::class)->prefix("manual-form-cut")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('manual-form-cut');
            Route::get('/create', 'create')->name('create-manual-form-cut');
            Route::get('/create-new', 'createNew')->name('create-new-manual-form-cut');
            Route::get('/process/{id?}', 'process')->name('process-manual-form-cut');
            Route::get('/get-number-data', 'getNumberData')->name('get-number-manual-form-cut');
            Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-manual-form-cut');
            Route::get('/get-item', 'getItem')->name('get-item-manual-form-cut');
            Route::put('/start-process/{id?}', 'startProcess')->name('start-process-manual-form-cut');
            Route::post('/jump-to-detail/{id?}', 'jumpToDetail')->name('jump-to-detail-manual-form-cut');
            Route::post('/store-marker/{id?}', 'storeMarker')->name('store-marker-manual-form-cut');
            Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-manual-form-cut');
            Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-manual-form-cut');
            Route::get('/get-time-record/{id?}/{noForm?}', 'getTimeRecord')->name('get-time-manual-form-cut');
            Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-manual-form-cut');
            Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-manual-form-cut');
            Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-manual-form-cut');
            Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-manual-form-cut');
            Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-manual-form-cut');
            Route::get('/check-spreading-form/{id?}/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-manual-form-cut');
            Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-manual-form-cut');
            Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-manual-form-cut');
            Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-manual-form-cut');
            Route::get('/get-form-cut-ratio', 'getRatio')->name('get-manual-form-cut-ratio');

            // get order
            Route::get('/get-order', 'getOrderInfo')->name('manual-form-cut-get-order');
            // get colors
            Route::get('/get-colors', 'getColorList')->name('manual-form-cut-get-colors');
            // get panels
            Route::get('/get-panels', 'getPanelList')->name('manual-form-cut-get-panels');
            // get sizes
            Route::get('/get-sizes', 'getSizeList')->name('manual-form-cut-get-sizes');
            // get count
            Route::get('/get-count', 'getCount')->name('manual-form-cut-get-count');
            // get number
            Route::get('/get-number', 'getNumber')->name('manual-form-cut-get-number');
        });

    // PILOT :
        // Pilot Form Cut Input
        Route::controller(CuttingFormPilotController::class)->prefix("pilot-form-cut")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('pilot-form-cut');
            Route::get('/create', 'create')->name('create-pilot-form-cut');
            Route::get('/create-new', 'createNew')->name('create-new-pilot-form-cut');
            Route::get('/process/{id?}', 'process')->name('process-pilot-form-cut');
            Route::get('/get-number-data', 'getNumberData')->name('get-number-pilot-form-cut');
            Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-pilot-form-cut');
            Route::get('/get-item', 'getItem')->name('get-item-pilot-form-cut');
            Route::put('/start-process', 'startProcess')->name('start-process-pilot-form-cut');
            Route::post('/store-marker/{id?}', 'storeMarker')->name('store-marker-pilot-form-cut');
            Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-pilot-form-cut');
            Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-pilot-form-cut');
            Route::get('/get-time-record/{id?}/{noForm?}', 'getTimeRecord')->name('get-time-pilot-form-cut');
            Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-pilot-form-cut');
            Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-pilot-form-cut');
            Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-pilot-form-cut');
            Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-pilot-form-cut');
            Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-pilot-form-cut');
            Route::get('/check-spreading-form/{id?}/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-pilot-form-cut');
            Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-pilot-form-cut');
            Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-pilot-form-cut');
            Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-pilot-form-cut');
            Route::get('/get-form-cut-ratio', 'getRatio')->name('get-pilot-form-cut-ratio');

            // get order
            Route::get('/get-order', 'getOrderInfo')->name('pilot-form-cut-get-order');
            // get colors
            Route::get('/get-colors', 'getColorList')->name('pilot-form-cut-get-colors');
            // get panels
            Route::get('/get-panels', 'getPanelList')->name('pilot-form-cut-get-panels');
            // get sizes
            Route::get('/get-sizes', 'getSizeList')->name('pilot-form-cut-get-sizes');
            // get count
            Route::get('/get-count', 'getCount')->name('pilot-form-cut-get-count');
            // get number
            Route::get('/get-number', 'getNumber')->name('pilot-form-cut-get-number');
        });

    // PIPING :
        // Piping  Controller
        Route::controller(PipingController::class)->prefix("form-cut-piping")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('form-cut-piping');
            Route::get('/create', 'create')->name('create-piping');
            Route::post('/store', 'store')->name('store-piping');
            Route::get('/edit/{id?}', 'edit')->name('edit-piping');
            Route::post('/update', 'update')->name('update-piping');
            Route::delete('/destroy/{id?}', 'destroy')->name('destroy-piping');

            Route::get('/get-marker-piping', 'getMarkerPiping')->name('get-marker-piping');
        });

        // Master Piping
        Route::controller(MasterPipingController::class)->prefix("master-piping")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('master-piping');
            Route::get('/create', 'create')->name('create-master-piping');
            Route::post('/store', 'store')->name('store-master-piping');
            Route::put('/update', 'update')->name('update-master-piping');

            // List Master Piping
            Route::get('/list', 'list')->name('list-master-piping');

            // Take Master Piping
            Route::get('/take/{id?}', 'take')->name('take-master-piping');
        });

        // Piping Process
        Route::controller(PipingProcessController::class)->prefix("piping-process")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('piping-process');
            Route::get('/create', 'create')->name('create-piping-process');
            Route::get('/create/new', 'createNew')->name('create-new-piping-process');
            Route::get('/process/{id?}', 'process')->name('process-piping-process');
            Route::post('/store', 'store')->name('store-piping-process');
            Route::delete('/destroy/{id?}', 'destroy')->name('destroy-piping-process');
            Route::get('/take-piping/{id?}', 'takePiping')->name('take-piping-process');
            Route::get('/pdf/{id?}', 'pdf')->name('pdf-piping-process');

            // Generate
            Route::get('/generate', 'generate')->name('generate-piping-process');

            // Item Forms
            Route::get('/item/{id?}', 'item')->name('item-piping');
            Route::get('/item/forms/{id?}', 'itemForms')->name('item-forms-piping');
            Route::get('/item/piping/{id?}/{idForm?}/{type?}', 'itemPiping')->name('item-piping-piping');
        });

        // Piping Loading
        Route::controller(PipingLoadingController::class)->prefix("piping-loading")->middleware("role:cutting")->group(function () {
            Route::get("/", "index")->name("piping-loading");
            Route::get("/create", "create")->name("create-piping-loading");
            Route::post("/store", "store")->name("store-piping-loading");
            Route::get("/total", "total")->name("total-piping-loading");

            Route::get("/pipingProcess/{id?}", "getPipingProcess")->name("get-piping-process");
        });

        // Cutting Reject
        Route::controller(CuttingFormRejectController::class)->prefix("form-cut-input-reject")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('cutting-reject');
            Route::get('/show/{id?}', 'show')->name('show-cutting-reject');
            Route::get('/create', 'create')->name('create-cutting-reject');
            Route::get('/process', 'process')->name('process-cutting-reject');
            Route::post('/store', 'store')->name('store-cutting-reject');
            Route::get('/edit/{id?}', 'edit')->name('edit-cutting-reject');
            Route::put('/update', 'update')->name('update-cutting-reject');
            Route::delete('/destroy/{id?}', 'destroy')->name('destroy-cutting-reject');

            // add-on
            Route::get('/stock', 'stock')->name('stock-cutting-reject');
            Route::get('/generate-code', 'generateCode')->name('generate-code-cutting-reject');

            // get sizes
            Route::get('/get-sizes', 'getSizeList')->name('get-form-reject-sizes');

            // export reject
            Route::post('/export-excel', 'exportExcel')->name('export-form-reject');

            Route::post('/save_fabric_form_reject', 'save_fabric_form_reject')->name('save_fabric_form_reject');
            Route::get('/show_fabric_form_reject', 'show_fabric_form_reject')->name('show_fabric_form_reject');
        });

    // PIECE :
        // Cutting Piece
        Route::controller(CuttingFormPieceController::class)->prefix("form-cut-input-piece")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('cutting-piece');
            Route::get('/show/{id?}', 'show')->name('show-cutting-piece');
            Route::get('/create', 'create')->name('create-cutting-piece');
            Route::get('/create-new', 'createNew')->name('create-new-cutting-piece');
            Route::get('/process/{id?}', 'process')->name('process-cutting-piece');
            Route::get('/incomplete-item/{id?}', 'incompleteItem')->name('incomplete-item-cutting-piece');
            Route::post('/store', 'store')->name('store-cutting-piece');
            Route::get('/edit/{id?}', 'edit')->name('edit-cutting-piece');
            Route::put('/update', 'update')->name('update-cutting-piece');
            Route::put('/update/detail', 'updateDetail')->name('update-cutting-piece-detail');
            Route::delete('/destroy/{id?}', 'destroy')->name('destroy-cutting-piece');

            // add-on
            Route::get('/stock', 'stock')->name('stock-cutting-piece');
            Route::get('/generate-code', 'generateCode')->name('generate-code-cutting-piece');

            // get sizes
            Route::get('/get-sizes', 'getSizeList')->name('get-form-piece-sizes');

            // export piece
            Route::post('/export-excel', 'exportExcel')->name('export-form-piece');
        });

        // Piping Stock
        Route::controller(PipingStockController::class)->prefix("piping-stock")->middleware("role:cutting")->group(function () {
            Route::get("/", "index")->name("piping-stock");
            Route::get("/total", "total")->name("total-piping-stock");
            Route::get("/show/{id?}/{color?}", "show")->name("show-piping-stock");
        });

        // Cutting Plan
        Route::controller(CuttingPlanController::class)->prefix("cut-plan")->middleware("role:cutting")->group(function () {
            Route::get('/', 'index')->name('cut-plan');
            Route::get('/create', 'create')->name('create-cut-plan');
            Route::post('/store', 'store')->name('store-cut-plan');
            Route::put('/update/{id?}', 'update')->name('update-cut-plan');
            Route::delete('/destroy', 'destroy')->name('destroy-cut-plan');
            Route::get('/get-selected-form/{noCutPlan?}', 'getSelectedForm')->name('get-selected-form');
            Route::get('/get-cut-plan-form', 'getCutPlanForm')->name('get-cut-plan-form');

            Route::post('/check-all-form', 'checkAllForm')->name('check-all-form-cut-plan');
            Route::post('/check-all-form-selected', 'checkAllFormSelected')->name('check-all-form-selected-cut-plan');

            Route::get('/cut-plan-output', 'cuttingPlanOutput')->name('cut-plan-output');
            Route::get('/cut-plan-output/show/{id?}', 'showCuttingPlanOutput')->name('detail-cut-plan-output');
            Route::get('/cut-plan-output/show-form', 'showCutPlanOutputForm')->name('cut-plan-output-form');
            Route::get('/cut-plan-output/show-available-form', 'showCutPlanOutputAvailableForm')->name('available-cut-plan-output-form');

            Route::get('/cut-plan-output/create', 'createCuttingPlanOutput')->name('create-cut-plan-output');
            Route::post('/cut-plan-output/store', 'storeCuttingPlanOutput')->name('store-cut-plan-output');
            Route::put('/cut-plan-output/update', 'updateCuttingPlanOutput')->name('edit-cut-plan-output');
            Route::delete('/cut-plan-output/destroy', 'destroyCuttinPlanOutputForm')->name('destroy-cut-plan-output');
            Route::get('/cut-plan-output/check-form', 'checkAllForms')->name('cut-plan-output-check-all-form');
            Route::post('/cut-plan-output/add-form', 'addCuttingPlanOutputForm')->name('add-cut-plan-output-form');
            Route::delete('/cut-plan-output/remove-form', 'removeCuttinPlanOutputForm')->name('remove-cut-plan-output-form');

            Route::post('/export', 'exportCuttingPlan')->name('export-cutting-plan');
        });

        // CompletedForm
        Route::controller(CompletedFormController::class)->prefix("manager")->middleware("role:cutting")->group(function () {
            Route::get('/cutting', 'cutting')->name('manage-cutting');
            Route::get('/cutting/detail/{id?}', 'detailCutting')->name('detail-cutting');
            Route::put('/cutting/generate/{id?}', 'generateStocker')->name('generate-stocker');
            Route::post('/cutting/update-form', 'updateCutting')->name('update-spreading-form');
            Route::post('/cutting/update-detail', 'updateDetail')->name('update-detail-form');
            Route::post('/cutting/update-header', 'updateHeader')->name('update-header-form');
            Route::put('/cutting/update-finish/{id?}', 'updateFinish')->name('finish-update-spreading-form');
            Route::delete('/cutting/destroy-roll/{id?}', 'destroySpreadingRoll')->name('destroy-spreading-roll');
            Route::post('/cutting/recalculate-form/{id?}', 'recalculateForm')->name('recalculate-spreading-form');
            Route::get('/check-stocker-form', 'checkStockerForm')->name('check-stocker-form');
        });

        // ReportCutting
        Route::controller(ReportCuttingController::class)->prefix("report-cutting")->middleware('role:cutting')->group(function () {
            Route::get('/cutting', 'cutting')->name('report-cutting');
            Route::get('/total-cutting', 'totalCutting')->name('total-cutting');
            Route::get('/cutting-daily', 'cuttingDaily')->name('report-cutting-daily');
            Route::get('/total-cutting-daily', 'totalCuttingDaily')->name('total-cutting-daily');
            Route::get('/track-cutting-output', 'trackCuttingOutput')->name('track-cutting-output');
            Route::get('/track-cutting-output/export', 'cuttingOrderOutputExport')->name('track-cutting-output-export');
            Route::get('/pemakaian-roll', 'pemakaianRoll')->name('pemakaian-roll');
            Route::get('/detail-pemakaian-roll', 'detailPemakaianRoll')->name('detail-pemakaian-roll');
            Route::get('/total-pemakaian-roll', 'totalPemakaianRoll')->name('total-pemakaian-roll');
            Route::get('/report_cutting_mutasi_fabric', 'report_cutting_mutasi_fabric')->name('report_cutting_mutasi_fabric');
            Route::get('/report_cutting_mutasi_fabric_proporsional', 'report_cutting_mutasi_fabric_proporsional')->name('report_cutting_mutasi_fabric_proporsional');
            Route::get('/report_gr_set', 'report_gr_set')->name('report_gr_set');
            Route::get('/report_gr_panel', 'report_gr_panel')->name('report_gr_panel');
            Route::get('/report_mutasi_wip_cutting', 'report_mutasi_wip_cutting')->name('report_mutasi_wip_cutting');
            Route::get('/report_pengeluaran_cutting', 'report_pengeluaran_cutting')->name('report_pengeluaran_cutting');
            Route::get('/report_pengeluaran_cutting_panel', 'report_pengeluaran_cutting_panel')->name('report_pengeluaran_cutting_panel');
            Route::get('/report_return_fabric_cutting', 'report_return_fabric_cutting')->name('report_return_fabric_cutting');

            // export excel
            Route::post('/cutting/export', 'export')->name('report-cutting-export');
            Route::post('/cutting-daily/export', 'cuttingDailyExport')->name('report-cutting-daily-export');
            Route::post('/pemakaian-roll/export', 'pemakaianRollExport')->name('pemakaian-roll-export');
            Route::post('/detail-pemakaian-roll/export', 'detailPemakaianRollExport')->name('detail-pemakaian-roll-export');
            Route::get('/export_excel_report_cutting_mutasi_fabric', 'export_excel_report_cutting_mutasi_fabric')->name('export_excel_report_cutting_mutasi_fabric');
            Route::get('/export_excel_report_cutting_mutasi_fabric_proporsional', 'export_excel_report_cutting_mutasi_fabric_proporsional')->name('export_excel_report_cutting_mutasi_fabric_proporsional');
            Route::get('/export_excel_report_gr_set', 'export_excel_report_gr_set')->name('export_excel_report_gr_set');
            Route::get('/export_excel_report_gr_panel', 'export_excel_report_gr_panel')->name('export_excel_report_gr_panel');
            Route::get('/export_excel_report_mut_wip_cutting', 'export_excel_report_mut_wip_cutting')->name('export_excel_report_mut_wip_cutting');
            Route::get('/export_excel_report_pengeluaran_cutting', 'export_excel_report_pengeluaran_cutting')->name('export_excel_report_pengeluaran_cutting');
            Route::get('/export_excel_report_pengeluaran_cutting_panel', 'export_excel_report_pengeluaran_cutting_panel')->name('export_excel_report_pengeluaran_cutting_panel');
            Route::get('/export_excel_report_return_fabric_cutting', 'export_excel_report_return_fabric_cutting')->name('export_excel_report_return_fabric_cutting');
        });

        // Roll
        Route::controller(RollController::class)->prefix("lap_pemakaian")->middleware('role:cutting')->group(function () {
            Route::get('/', 'index')->name('lap_pemakaian');
            Route::post('/manajemen_roll', 'pemakaianRollData')->name('lap_pemakaian_data');
            Route::get('/sisa_kain_roll', 'sisaKainRoll')->name('sisa_kain_roll');
            Route::get('/sisa_kain_roll/get_scanned_item/{id?}', 'getScannedItem')->name('sisa_kain_scan_item');
            Route::get('/sisa_kain_roll/forms', 'getSisaKainForm')->name('sisa_kain_form');
            // supplier
            Route::get('/get-supplier', 'getSupplier')->name('roll-get-supplier');
            Route::get('/get-order', 'getOrder')->name('roll-get-order');
            // export excel
            Route::post('/export_excel', 'export_excel')->name('export_excel_manajemen_roll');
            Route::post('/export', 'export')->name('export');
            // print
            Route::post('/sisa_kain/print/{id?}', 'printSisaKain')->name('print_sisa_kain');
            Route::post('/mass_sisa_kain/print', 'massPrintSisaKain')->name('mass_print_sisa_kain');
            // alokasi fabric gr panel
            Route::get('/alokasi_fabric_gr_panel', 'alokasi_fabric_gr_panel')->name('alokasi_fabric_gr_panel');
            Route::get('/create_alokasi_fabric_gr_panel', 'create_alokasi_fabric_gr_panel')->name('create_alokasi_fabric_gr_panel');
            Route::post('/save_alokasi_fabric_gr_panel', 'save_alokasi_fabric_gr_panel')->name('save_alokasi_fabric_gr_panel');
            // Penerimaan Fabric Cutting
            Route::get('/roll_fabric_cutting_in', 'roll_fabric_cutting_in')->name('roll_fabric_cutting_in');
            Route::get('/export_roll_fabric_cutting_in', 'export_roll_fabric_cutting_in')->name('export_roll_fabric_cutting_in');
        });

        // Ganti Reject GR
        Route::controller(GantiRejectController::class)->prefix("ganti_reject")->middleware('role:cutting')->group(function () {
            // form ganti reject panel gr
            Route::get('/form_gr_panel', 'form_gr_panel')->name('form_gr_panel');
            Route::get('/create_form_gr_panel', 'create_form_gr_panel')->name('create_form_gr_panel');
            Route::get('/get_barcode_form_gr_panel/{id?}', 'get_barcode_form_gr_panel')->name('get_barcode_form_gr_panel');
            Route::get('/get_ws_all_form_gr_panel', 'get_ws_all_form_gr_panel')->name('get_ws_all_form_gr_panel');
            Route::post('/save_form_gr_panel', 'save_form_gr_panel')->name('save_form_gr_panel');
            Route::get('/export_excel_form_gr_panel_det', 'export_excel_form_gr_panel_det')->name('export_excel_form_gr_panel_det');
        });

    // TOOLS :
        // Cutting Tools
        Route::controller(CuttingToolsController::class)->prefix("cutting")->middleware('role:superadmin')->group(function () {
            // form
            Route::get('/index', 'index')->name('cutting-tools');

            // fix roll qty
            Route::get('/get-roll-qty', 'getRollQty')->name('get-roll-qty');
            Route::post('/fix-roll-qty', 'fixRollQty')->name('fix-roll-qty');

            // fix form ratio
            Route::get('/get-form-ratio', 'getFormRatio')->name('get-form-ratio');
            Route::post('/update-form-ratio', 'updateFormRatio')->name('update-form-ratio');

            // fix form marker
            Route::get('/get-form-marker', 'getFormMarker')->name('get-form-marker');
            Route::post('/update-form-marker', 'updateFormMarker')->name('update-form-marker');

            // fix form swap size
            Route::post('/update-form-swap', 'updateFormSwap')->name('update-form-swap');

            // modify group
            Route::post('/update-form-group', 'updateFormGroup')->name('update-form-group');

            // Delete redundant roll
            Route::post('/delete-redundant-roll', 'deleteRedundantRoll')->name('delete-redundant-roll');

            // Import Cutting Manual
            Route::post('/import-cutting-manual', 'importCuttingManual')->name('import-cutting-manual');

            // Import Saldo Awal Cutting Manual
            Route::post('/import-saldo-awal-cutting', 'importSaldoAwalCutting')->name('import-saldo-awal-cutting');

            // Get Saldo Awal Cutting Temporary
            Route::get('/get-saldo-awal-cutting-tmp', 'getSaldoAwalCuttingTmp')->name('get-saldo-awal-cutting-tmp');

            // Save Saldo Awal Cutting
            Route::post('/save-saldo-awal-cutting', 'saveSaldoAwalCutting')->name('save-saldo-awal-cutting');

            // Empty Saldo Awal Cutting
            Route::post('/empty-saldo-awal-cutting', 'emptySaldoAwalCutting')->name('empty-saldo-awal-cutting');
        });
});
