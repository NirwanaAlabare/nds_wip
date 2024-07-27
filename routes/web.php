<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// User
use App\Http\Controllers\UserController;

// General
use App\Http\Controllers\GeneralController;

// Part
use App\Http\Controllers\Part\MasterPartController;
use App\Http\Controllers\Part\MasterSecondaryController;
use App\Http\Controllers\Part\PartController;

// Marker
use App\Http\Controllers\Marker\MarkerController;

// Cutting
use App\Http\Controllers\Cutting\SpreadingController;
use App\Http\Controllers\Cutting\CuttingFormController;
use App\Http\Controllers\Cutting\CuttingFormManualController;
use App\Http\Controllers\Cutting\CuttingFormPilotController;
use App\Http\Controllers\Cutting\CuttingPlanController;
use App\Http\Controllers\Cutting\ReportCuttingController;
use App\Http\Controllers\Cutting\CompletedFormController;
use App\Http\Controllers\Cutting\RollController;

// Stocker
use App\Http\Controllers\Stocker\StockerController;

// DC
use App\Http\Controllers\DC\DCInController;
use App\Http\Controllers\DC\SecondaryInController;
use App\Http\Controllers\DC\SecondaryInhouseController;
use App\Http\Controllers\DC\StockDcCompleteController;
use App\Http\Controllers\DC\StockDcIncompleteController;
use App\Http\Controllers\DC\StockDcWipController;
use App\Http\Controllers\DC\RackController;
use App\Http\Controllers\DC\RackStockerController;
use App\Http\Controllers\DC\TrolleyController;
use App\Http\Controllers\DC\TrolleyStockerController;
use App\Http\Controllers\DC\LoadingLineController;

// Sewing
use App\Http\Controllers\Sewing\MasterPlanController;
use App\Http\Controllers\Sewing\MasterDefectController;
use App\Http\Controllers\Sewing\ReportController;
use App\Http\Controllers\Sewing\OrderDefectController;
use App\Http\Controllers\Sewing\TrackOrderOutputController;
use App\Http\Controllers\Sewing\TransferOutputController;
use App\Http\Controllers\Sewing\LineDashboardController;

// Production
use App\Http\Controllers\Sewing\MasterKursBiController;
use App\Http\Controllers\Sewing\MasterBuyerController;
use App\Http\Controllers\Sewing\MasterJabatanController;
use App\Http\Controllers\Sewing\MasterKaryawanController;
use App\Http\Controllers\Sewing\DataProduksiController;
use App\Http\Controllers\Sewing\DataDetailProduksiController;
use App\Http\Controllers\Sewing\DataDetailProduksiDayController;
use App\Http\Controllers\Sewing\ReportOutputController;
use App\Http\Controllers\Sewing\ReportProductionController;
use App\Http\Controllers\Sewing\ReportEfficiencyController;
use App\Http\Controllers\Sewing\ReportDetailOutputController;

// Track
use App\Http\Controllers\TrackController;

use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\MasterLokasiController;
use App\Http\Controllers\InMaterialController;
use App\Http\Controllers\OutMaterialController;
use App\Http\Controllers\MutLokasiController;
use App\Http\Controllers\QcPassController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MutasiMesinController;
use App\Http\Controllers\MutasiMesinMasterController;
use App\Http\Controllers\ReqMaterialController;
use App\Http\Controllers\ReturMaterialController;
use App\Http\Controllers\ReturInMaterialController;
use App\Http\Controllers\LapDetPemasukanController;
use App\Http\Controllers\LapDetPengeluaranController;
use App\Http\Controllers\LapMutasiGlobalController;
use App\Http\Controllers\LapDetPengeluaranRollController;
use App\Http\Controllers\LapDetPemasukanRollController;
use App\Http\Controllers\LapMutasiDetailController;
use App\Http\Controllers\DashboardFabricController;
use App\Http\Controllers\FGStokMasterController;
use App\Http\Controllers\FGStokBPBController;
use App\Http\Controllers\FGStokBPPBController;
use App\Http\Controllers\FGStokLaporanController;
use App\Http\Controllers\FGStokMutasiController;
use App\Http\Controllers\KonfPemasukanController;
use App\Http\Controllers\KonfPengeluaranController;
use App\Http\Controllers\PPIC_MasterSOController;
use App\Http\Controllers\PPIC_LaporanTrackingController;
use App\Http\Controllers\TransferBpbController;

use App\Http\Controllers\PackingTransferGarmentController;
use App\Http\Controllers\PackingPackingInController;
use App\Http\Controllers\PackingPackingOutController;
use App\Http\Controllers\PackingMasterKartonController;
use App\Http\Controllers\GAPengajuanBahanBakarController;
use App\Http\Controllers\GAApprovalBahanBakarController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    // User
    Route::controller(UserController::class)->prefix("user")->group(function () {
        Route::put('/update/{id?}', 'update')->name('update-user');
    });

    // General
    Route::controller(GeneralController::class)->prefix("general")->group(function () {
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
    });

    // Part :
    // Master Part
    Route::controller(MasterPartController::class)->prefix("master-part")->middleware('stocker')->group(function () {
        Route::get('/', 'index')->name('master-part');
        Route::post('/store', 'store')->name('store-master-part');
        Route::put('/update/{id?}', 'update')->name('update-master-part');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-part');
    });

    // Master Secondary
    Route::controller(MasterSecondaryController::class)->prefix("master-secondary")->middleware('marker')->group(function () {
        Route::get('/', 'index')->name('master-secondary');
        Route::post('/store', 'store')->name('store-master-secondary');
        Route::get('/show_master_secondary', 'show_master_secondary')->name('show_master_secondary');
        Route::put('/update_master_secondary', 'update_master_secondary')->name('update_master_secondary');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-secondary');
    });

    // Part
    Route::controller(PartController::class)->prefix("part")->middleware('stocker')->group(function () {
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
        Route::get('/get_proses', 'get_proses')->name('get_proses');
        Route::post('/store_part_secondary', 'store_part_secondary')->name('store_part_secondary');

        // part detail
        Route::delete('/destroy-part-detail/{id?}', 'destroyPartDetail')->name('destroy-part-detail');

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
    });

    // Marker :
    // Marker
    Route::controller(MarkerController::class)->prefix("marker")->middleware('marker')->group(function () {
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

    // Cutting :
    // Spreading
    Route::controller(SpreadingController::class)->prefix("spreading")->middleware('spreading')->group(function () {
        Route::get('/', 'index')->name('spreading');
        Route::get('/create', 'create')->name('create-spreading');
        Route::post('/getno_marker', 'getno_marker')->name('getno_marker');
        Route::get('/getdata_marker', 'getdata_marker')->name('getdata_marker');
        Route::get('/getdata_ratio', 'getdata_ratio')->name('getdata_ratio');
        Route::post('/store', 'store')->name('store-spreading');
        Route::put('/update', 'update')->name('update-spreading');
        Route::put('/update-status', 'updateStatus')->name('update-status');
        Route::get('/get-order-info', 'getOrderInfo')->name('get-spreading-data');
        Route::get('/get-cut-qty', 'getCutQty')->name('get-cut-qty-data');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-spreading');
        // export excel
        // Route::get('/export_excel', 'export_excel')->name('export_excel');
        // Route::get('/export', 'export')->name('export');
    });

    // Form Cut Input
    Route::controller(CuttingFormController::class)->prefix("form-cut-input")->middleware("meja")->group(function () {
        Route::get('/', 'index')->name('form-cut-input');
        Route::get('/process/{id?}', 'process')->name('process-form-cut-input');
        Route::get('/get-number-data', 'getNumberData')->name('get-number-form-cut-input');
        Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-form-cut-input');
        Route::get('/get-item', 'getItem')->name('get-item-form-cut-input');
        Route::put('/start-process/{id?}', 'startProcess')->name('start-process-form-cut-input');
        Route::put('/next-process-one/{id?}', 'nextProcessOne')->name('next-process-one-form-cut-input');
        Route::put('/next-process-two/{id?}', 'nextProcessTwo')->name('next-process-two-form-cut-input');
        Route::get('/get-time-record/{noForm?}', 'getTimeRecord')->name('get-time-form-cut-input');
        Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-form-cut-input');
        Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-form-cut-input');
        Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-form-cut-input');
        Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-form-cut-input');
        Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-form-cut-input');
        Route::get('/check-spreading-form/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-form-cut-input');
        Route::get('/check-time-record/{detailId?}', 'checkTimeRecordLap')->name('check-time-record-form-cut-input');
        Route::post('/store-lost-time/{id?}', 'storeLostTime')->name('store-lost-form-cut-input');
        Route::get('/check-lost-time/{id?}', 'checkLostTime')->name('check-lost-form-cut-input');
        Route::get('/get-form-cut-ratio', 'getRatio')->name('get-form-cut-ratio');

        // get order
        Route::get('/get-order', 'getOrderInfo')->name('form-cut-get-marker-order');
        // get colors
        Route::get('/get-colors', 'getColorList')->name('form-cut-get-marker-colors');
        // get panels
        Route::get('/get-panels', 'getPanelList')->name('form-cut-get-marker-panels');
        // get sizes
        Route::get('/get-sizes', 'getSizeList')->name('form-cut-get-marker-sizes');
        // get count
        Route::get('/get-count', 'getCount')->name('form-cut-get-marker-count');
        // get number
        Route::get('/get-number', 'getNumber')->name('form-cut-get-marker-number');

        // no cut update
        Route::put('/update-no-cut', 'updateNoCut')->name('form-cut-update-no-cut');
    });

    // Manual Form Cut Input
    Route::controller(CuttingFormManualController::class)->prefix("manual-form-cut")->middleware("meja")->group(function () {
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
        Route::get('/get-time-record/{noForm?}', 'getTimeRecord')->name('get-time-manual-form-cut');
        Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-manual-form-cut');
        Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-manual-form-cut');
        Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-manual-form-cut');
        Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-manual-form-cut');
        Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-manual-form-cut');
        Route::get('/check-spreading-form/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-manual-form-cut');
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

    // Pilot Form Cut Input
    Route::controller(CuttingFormPilotController::class)->prefix("pilot-form-cut")->middleware("meja")->group(function () {
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
        Route::get('/get-time-record/{noForm?}', 'getTimeRecord')->name('get-time-pilot-form-cut');
        Route::post('/store-scanned-item', 'storeScannedItem')->name('store-scanned-pilot-form-cut');
        Route::post('/store-time-record', 'storeTimeRecord')->name('store-time-pilot-form-cut');
        Route::post('/store-time-record-extension', 'storeTimeRecordExtension')->name('store-time-ext-pilot-form-cut');
        Route::post('/store-this-time-record', 'storeThisTimeRecord')->name('store-this-time-pilot-form-cut');
        Route::put('/finish-process/{id?}', 'finishProcess')->name('finish-process-pilot-form-cut');
        Route::get('/check-spreading-form/{noForm?}/{noMeja?}', 'checkSpreadingForm')->name('check-spreading-pilot-form-cut');
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

    // Cutting Plan
    Route::controller(CuttingPlanController::class)->prefix("cut-plan")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('cut-plan');
        Route::get('/create', 'create')->name('create-cut-plan');
        Route::post('/store', 'store')->name('store-cut-plan');
        Route::put('/update/{id?}', 'update')->name('update-cut-plan');
        Route::delete('/destroy', 'destroy')->name('destroy-cut-plan');
        Route::get('/get-selected-form/{noCutPlan?}', 'getSelectedForm')->name('get-selected-form');
        Route::get('/get-cut-plan-form', 'getCutPlanForm')->name('get-cut-plan-form');

        Route::get('/cut-plan-output', 'cuttingPlanOutput')->name('cut-plan-output');
        Route::get('/cut-plan-output/show/{id?}', 'showCuttingPlanOutput')->name('detail-cut-plan-output');
        Route::get('/cut-plan-output/show-available-form', 'showCutPlanOutputAvailableForm')->name('available-cut-plan-output-form');
        Route::get('/cut-plan-output/create', 'createCuttingPlanOutput')->name('create-cut-plan-output');
        Route::post('/cut-plan-output/store', 'storeCuttingPlanOutput')->name('store-cut-plan-output');
        Route::put('/cut-plan-output/update', 'updateCuttingPlanOutput')->name('edit-cut-plan-output');
        Route::delete('/cut-plan-output/destroy', 'destroyCuttinPlanOutputForm')->name('destroy-cut-plan-output');
        Route::post('/cut-plan-output/add-form', 'addCuttinPlanOutputForm')->name('add-cut-plan-output-form');
        Route::delete('/cut-plan-output/remove-form', 'removeCuttinPlanOutputForm')->name('remove-cut-plan-output-form');
    });

    // CompletedForm
    Route::controller(CompletedFormController::class)->prefix("manager")->middleware('manager')->group(function () {
        Route::get('/cutting', 'cutting')->name('manage-cutting');
        Route::get('/cutting/detail/{id?}', 'detailCutting')->name('detail-cutting');
        Route::put('/cutting/generate/{id?}', 'generateStocker')->name('generate-stocker');
        Route::post('/cutting/update-form', 'updateCutting')->name('update-spreading-form');
        Route::put('/cutting/update-finish/{id?}', 'updateFinish')->name('finish-update-spreading-form');
        Route::delete('/cutting/destroy-roll/{id?}', 'destroySpreadingRoll')->name('destroy-spreading-roll');
    });

    // ReportCutting
    Route::controller(ReportCuttingController::class)->prefix("report-cutting")->middleware('admin')->group(function () {
        Route::get('/cutting', 'cutting')->name('report-cutting');
        // export excel
        Route::post('/cutting/export', 'export')->name('report-cutting-export');
    });

    // Roll
    Route::controller(RollController::class)->prefix("lap_pemakaian")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('lap_pemakaian');
        // export excel
        Route::get('/export_excel', 'export_excel')->name('export_excel');
        Route::post('/export', 'export')->name('export');
    });

    // Stocker :
    Route::controller(StockerController::class)->prefix("stocker")->middleware('stocker')->group(function () {
        Route::get('/', 'index')->name('stocker');
        Route::get('/show/{partDetailId?}/{formCutId?}', 'show')->name('show-stocker');
        Route::post('/print-stocker/{index?}', 'printStocker')->name('print-stocker');
        Route::post('/print-stocker-all-size/{partDetailId?}', 'printStockerAllSize')->name('print-stocker-all-size');
        Route::post('/print-stocker-checked', 'printStockerChecked')->name('print-stocker-checked');
        Route::post('/print-numbering/{index?}', 'printNumbering')->name('print-numbering');
        Route::post('/print-numbering-checked', 'printNumberingChecked')->name('print-numbering-checked');
        Route::post('/full-generate-numbering', 'fullGenerateNumbering')->name('full-generate-numbering');
        Route::post('/fix-redundant-stocker', 'fixRedundantStocker')->name('fix-redundant-stocker');
        Route::post('/fix-redundant-numbering', 'fixRedundantNumbering')->name('fix-redundant-numbering');
        Route::put('/count-stocker-update', 'countStockerUpdate')->name('count-stocker-update');

        Route::post('/rearrange-group', 'rearrangeGroup')->name('rearrange-group');
        Route::post('/reorder-stocker-numbering', 'reorderStockerNumbering')->name('reorder-stocker-numbering');
        Route::post('/modify-size-qty', 'modifySizeQty')->name('modify-size-qty');

        Route::get('/stocker-part', 'part')->name('stocker-part');

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
    });

    // DC :
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
    Route::controller(DCInController::class)->prefix("dc-in")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('dc-in');
        Route::get('/show_data_header', 'show_data_header')->name('show_data_header');
        Route::get('/create', 'create')->name('create-dc-in');
        Route::post('/store', 'store')->name('store-dc-in');
        Route::delete('/destroy', 'destroy')->name('destroy');

        Route::get('/get_proses', 'get_proses')->name('get_proses_dc_in');
        Route::get('/get_tempat', 'get_tempat')->name('get_tempat');
        Route::get('/get_lokasi', 'get_lokasi')->name('get_lokasi');

        Route::get('/get_tmp_dc_in', 'get_tmp_dc_in')->name('get_tmp_dc_in');
        Route::post('/insert_tmp_dc_in', 'insert_tmp_dc_in')->name('insert_tmp_dc_in');
        Route::post('/mass_insert_tmp_dc_in', 'mass_insert_tmp_dc_in')->name('mass_insert_tmp_dc_in');
        Route::put('/update_tmp_dc_in', 'update_tmp_dc_in')->name('update_tmp_dc_in');
        Route::put('/update_mass_tmp_dc_in', 'update_mass_tmp_dc_in')->name('update_mass_tmp_dc_in');
        Route::get('/show_tmp_dc_in', 'show_tmp_dc_in')->name('show_tmp_dc_in');
    });

    // Secondary INHOUSE
    Route::controller(SecondaryInhouseController::class)->prefix("secondary-inhouse")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('secondary-inhouse');
        Route::get('/cek_data_stocker_inhouse', 'cek_data_stocker_inhouse')->name('cek_data_stocker_inhouse');
        Route::post('/store', 'store')->name('store-secondary-inhouse');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-inhouse');
        Route::get('/detail_stocker_inhouse', 'detail_stocker_inhouse')->name('detail_stocker_inhouse');
    });

    // Secondary IN
    Route::controller(SecondaryInController::class)->prefix("secondary-in")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('secondary-in');
        Route::get('/cek_data_stocker_in', 'cek_data_stocker_in')->name('cek_data_stocker_in');
        Route::post('/store', 'store')->name('store-secondary-in');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-in');
        Route::get('/detail_stocker_in', 'detail_stocker_in')->name('detail_stocker_in');
    });

    // Rack
    Route::controller(RackController::class)->prefix("rack")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('rack');
        Route::get('/create', 'create')->name('create-rack');
        Route::post('/store', 'store')->name('store-rack');
        Route::put('/update', 'update')->name('update-rack');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-rack');
        Route::post('/print-rack/{id?}', 'printRack')->name('print-rack');
    });

    // Rack Stocker
    Route::controller(RackStockerController::class)->prefix("stock-rack")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('stock-rack');
        Route::get('/allocate', 'allocate')->name('allocate-rack');
        Route::get('/stock-rack-visual', 'stockRackVisual')->name('stock-rack-visual');
        Route::get('/stock-rack-visual-detail', 'stockRackVisualDetail')->name('stock-rack-visual-detail');
        Route::post('/store', 'store')->name('store-rack-stock');
        Route::put('/update', 'update')->name('update-rack-stock');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-rack-stock');
        Route::post('/print-bon-mutasi/{id?}', 'printBonMutasi')->name('print-rack-stock');
    });

    // Trolley
    Route::controller(TrolleyController::class)->prefix("trolley")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('trolley');
        Route::get('/create', 'create')->name('create-trolley');
        Route::post('/store', 'store')->name('store-trolley');
        Route::put('/update', 'update')->name('update-trolley');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-trolley');
        Route::post('/print-trolley/{id?}', 'printTrolley')->name('print-trolley');
    });

    // Trolley Stocker
    Route::controller(TrolleyStockerController::class)->prefix("stock-trolley")->middleware('dc')->group(function () {
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
    Route::controller(LoadingLineController::class)->prefix("loading-line")->middleware('dc')->group(function () {
        Route::get('/', 'index')->name('loading-line');
        Route::get('/detail/{id?}', 'show')->name('detail-loading-plan');
        Route::get('/create', 'create')->name('create-loading-plan');
        Route::post('/store', 'store')->name('store-loading-plan');
        Route::get('/edit/{id?}', 'edit')->name('edit-loading-plan');
        Route::put('/update/{id?}', 'update')->name('update-loading-plan');
        Route::delete('/destroy/{id?}', 'destroy')->name('destroy-loading-plan');
        Route::get('/summary', 'summary')->name('summary-loading');
        Route::get('/get-total-summary', 'getTotalSummary')->name('total-summary-loading');
        Route::post('/export-excel', 'exportExcel')->name('export-excel-loading');
    });

    // Stock DC Complete
    Route::controller(StockDcCompleteController::class)->prefix("stock-dc-complete")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-complete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-complete-detail');
    });

    // Stock DC Incomplete
    Route::controller(StockDcIncompleteController::class)->prefix("stock-dc-incomplete")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-incomplete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-incomplete-detail');
    });

    // Stock DC WIP
    Route::controller(StockDcWipController::class)->prefix("stock-dc-wip")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-wip');
        Route::get('/show/{partId?}', 'show')->name('stock-dc-wip-detail');
    });

    // Sewing :
    // Master Plan
    Route::controller(MasterPlanController::class)->prefix("master-plan")->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('master-plan');
        Route::get('show/{line?}/{date?}', 'show')->name('master-plan-detail');
        Route::put('update', 'update')->name('update-master-plan');
        Route::post('store', 'store')->name('store-master-plan');
        Route::delete('destroy/{id?}', 'destroy')->name('destroy-master-plan');
    });

    Route::controller(MasterDefectController::class)->prefix("master-defect")->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('master-defect');

        Route::put('update-defect-type', 'updateDefectType')->name('update-defect-type');
        Route::post('store-defect-type', 'storeDefectType')->name('store-defect-type');
        Route::delete('destroy-defect-type/{id?}', 'destroyDefectType')->name('destroy-defect-type');

        Route::put('update-defect-area', 'updateDefectArea')->name('update-defect-area');
        Route::post('store-defect-area', 'storeDefectArea')->name('store-defect-area');
        Route::delete('destroy-defect-area/{id?}', 'destroyDefectArea')->name('destroy-defect-area');
    });

    // Report Daily
    Route::controller(ReportController::class)->prefix('report')->middleware('sewing')->group(function () {
        Route::get('/{type}', 'index')->name("daily-sewing");
        Route::post('/output/export', 'exportOutput');
        Route::post('/production/export', 'exportProduction');
        Route::post('/production-all/export', 'exportProductionAll');
        Route::post('/track-order-output/export', 'exportOrderOutput');
    });

    // Pareto Chart
    Route::controller(OrderDefectController::class)->prefix('order-defects')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('order-defects');
        Route::get('/{buyerId?}/{dateFrom?}/{dateTo?}', 'getOrderDefects')->name('get-order-defects');
    });

    // Track Order Output
    Route::controller(TrackOrderOutputController::class)->prefix('track-order-output')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-track-order-output');
    });

    // Transfer Output
    Route::controller(TransferOutputController::class)->prefix('transfer-output')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-transfer-output');
    });

    // Dashboard List
    Route::controller(LineDashboardController::class)->prefix('line-dashboards')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-dashboard');
    });

    // Report
    Route::controller(MasterKursBiController::class)->prefix('master-kurs-bi')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('kursBi');
        Route::get('/get-data', 'getData')->name('kursBi.getData');
        Route::post('/scrap-data', 'scrapData')->name('kursBi.scrapData');
    });

    Route::controller(MasterJabatanController::class)->prefix('master-jabatan')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('jabatan');
        Route::get('/get-data', 'getData')->name('jabatan.getData');
        Route::post('/store-data', 'store')->name('jabatan.storeData');
        Route::put('/update-data', 'update')->name('jabatan.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('jabatan.destroyData');
    });

    Route::controller(MasterKaryawanController::class)->prefix('master-karyawan')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('karyawan');
        Route::get('/get-data', 'getData')->name('karyawan.getData');
        Route::put('/update-data', 'update')->name('karyawan.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('karyawan.destroyData');

        Route::get('/other-source', 'otherSource')->name('karyawan.otherSource');
        Route::post('/transfer-data', 'transfer')->name('karyawan.transferData');
    });

    Route::controller(MasterBuyerController::class)->prefix('master-buyer')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('buyer');
        Route::get('/get-data', 'getData')->name('buyer.getData');
        Route::put('/update-data', 'update')->name('buyer.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('buyer.destroyData');

        Route::get('/other-source', 'otherSource')->name('buyer.otherSource');
        Route::post('/transfer-data', 'transfer')->name('buyer.transferData');
    });

    Route::controller(DataProduksiController::class)->prefix('data-produksi')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('dataProduksi');
        Route::get('/get-data', 'getData')->name('dataProduksi.getData');
        Route::put('/update-data', 'update')->name('dataProduksi.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataProduksi.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataProduksi.transferData');
    });

    Route::controller(DataDetailProduksiController::class)->prefix('data-detail-produksi')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('dataDetailProduksi');
        Route::get('/get-data', 'getData')->name('dataDetailProduksi.getData');
        Route::put('/update-data', 'update')->name('dataDetailProduksi.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataDetailProduksi.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataDetailProduksi.transferData');
    });

    Route::controller(DataDetailProduksiDayController::class)->prefix('data-detail-produksi-day')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('dataDetailProduksiDay');
        Route::get('/get-data', 'getData')->name('dataDetailProduksiDay.getData');
        Route::put('/update-data', 'update')->name('dataDetailProduksiDay.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataDetailProduksiDay.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataDetailProduksiDay.transferData');
    });

    Route::controller(ReportOutputController::class)->prefix('report-output')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('reportOutput');
        Route::get('/get-data', 'getData')->name('reportOutput.getData');
        Route::post('/export-data', 'exportData')->name('reportOutput.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportOutput.transferData');
    });

    Route::controller(ReportProductionController::class)->prefix('report-production')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('reportProduction');
        Route::get('/get-data', 'getData')->name('reportProduction.getData');
        Route::post('/export-data', 'exportData')->name('reportProduction.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportProduction.transferData');
    });

    Route::controller(ReportEfficiencyController::class)->prefix('report-efficiency')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('reportEfficiency');
        Route::get('/get-data', 'getData')->name('reportEfficiency.getData');
        Route::post('/export-data', 'exportData')->name('reportEfficiency.exportData');

        Route::post('/transfer-data', 'transfer')->name('reportEfficiency.transferData');
    });

    Route::controller(ReportDetailOutputController::class)->prefix('report-detail-output')->middleware('sewing')->group(function () {
        Route::get('/', 'index')->name('reportDetailOutput');
        Route::get('/packing', 'packing')->name('reportDetailOutputPacking');
        Route::get('/get-data', 'getData')->name('reportDetailOutput.getData');
        Route::post('/export-data', 'exportData')->name('reportDetailOutput.exportData');
        Route::post('/export-data-packing', 'exportDataPacking')->name('reportDetailOutput.exportDataPacking');

        Route::post('/transfer-data', 'transfer')->name('reportDetailOutput.transferData');
    });

    Route::get('/symlink', function () {
        Artisan::call('storage:link');
    });

    // Track
    Route::controller(TrackController::class)->prefix("track")->middleware('admin')->group(function () {
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
    });

    //Mutasi Karywawan
    // Route::controller(EmployeeController::class)->prefix("mut-karyawan")->middleware('hr')->group(function () {
    //     Route::get('/', 'index')->name('mut-karyawan');
    //     Route::get('/create', 'create')->name('create-mut-karyawan');
    //     Route::post('/store', 'store')->name('store-mut-karyawan');
    //     Route::put('/update', 'update')->name('update-mut-karyawan');
    //     Route::delete('/destroy', 'destroy')->name('destroy-mut-karyawan');
    //     Route::get('/getdataline', 'getdataline')->name('getdataline');
    //     Route::get('/gettotal', 'gettotal')->name('gettotal');
    //     Route::get('/getdatanik', 'getdatanik')->name('getdatanik');
    //     Route::get('/getdatalinekaryawan', 'getdatalinekaryawan')->name('getdatalinekaryawan');
    //     Route::get('/export_excel_mut_karyawan', 'export_excel_mut_karyawan')->name('export_excel_mut_karyawan');
    //     Route::get('/line-chart-data', 'lineChartData')->name('line-chart-data');
    // });

    // Mutasi Mesin
    Route::controller(MutasiMesinController::class)->prefix("mut-mesin")->middleware('hr')->group(function () {
        Route::get('/', 'index')->name('mut-mesin');
        Route::get('/create', 'create')->name('create-mut-mesin');
        Route::post('/store', 'store')->name('store-mut-mesin');
        // Route::put('/update', 'update')->name('update-mut-karyawan');
        // Route::delete('/destroy', 'destroy')->name('destroy-mut-karyawan');
        Route::get('/getdataline', 'getdataline')->name('getdataline');
        Route::get('/gettotal', 'gettotal')->name('gettotal');
        Route::get('/getdatamesin', 'getdatamesin')->name('getdatamesin');
        Route::get('/getdatalinemesin', 'getdatalinemesin')->name('getdatalinemesin');
        Route::get('/export_excel_mut_mesin', 'export_excel_mut_mesin')->name('export_excel_mut_mesin');
        Route::get('/line-chart-data', 'lineChartData')->name('line-chart-data');
    });
    // Mutasi Mesin Master
    Route::controller(MutasiMesinMasterController::class)->prefix("master-mut-mesin")->middleware('hr')->group(function () {
        Route::get('/', 'index')->name('master-mut-mesin');
        Route::post('/store', 'store')->name('store-master-mut-mesin');
        Route::get('/export_excel_master_mesin', 'export_excel_master_mesin')->name('export_excel_master_mesin');
        Route::post('/hapus_data_mesin', 'hapus_data_mesin')->name('hapus-data-mesin');
    });

    //warehouse
    Route::controller(WarehouseController::class)->prefix("warehouse")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('warehouse');
    });

    //master lokasi
    Route::controller(MasterLokasiController::class)->prefix("master-lokasi")->middleware('master-lokasi')->group(function () {
        Route::get('/', 'index')->name('master-lokasi');
        Route::get('/create', 'create')->name('create-lokasi');
        Route::post('/store', 'store')->name('store-lokasi');
        Route::get('/update/{id?}', 'update')->name('update-lokasi');
        Route::get('/updatestatus', 'updatestatus')->name('updatestatus');
        Route::get('/simpanedit', 'simpanedit')->name('simpan-edit');
        Route::post('/print-lokasi/{id?}', 'printlokasi')->name('print-lokasi');
    });

    //dashboard fabric
    Route::controller(DashboardFabricController::class)->group(function () {
        Route::get('/dashboard-warehouse', 'index')->name('dashboard-warehouse');
        Route::get('/get-data-rak', 'getdatarak')->name('get-data-rak');
        Route::get('/get-data-rak2', 'getdatarak2')->name('get-data-rak2');
        Route::get('/get-data-rak3', 'getdatarak3')->name('get-data-rak3');
    });

    //Penerimaan
    Route::controller(InMaterialController::class)->prefix("in-material")->middleware('in-material')->group(function () {
        Route::get('/', 'index')->name('in-material');
        Route::get('/create', 'create')->name('create-inmaterial');
        Route::get('/lokasi-material/{id?}', 'lokmaterial')->name('lokasi-inmaterial');
        Route::get('/edit-material/{id?}', 'editmaterial')->name('edit-inmaterial');
        Route::post('/store', 'store')->name('store-inmaterial-fabric');
        Route::get('/updatedet', 'updatedet')->name('update-inmaterial-fabric');
        Route::get('/get-po', 'getPOList')->name('get-po-list');
        Route::get('/get-ws', 'getWSList')->name('get-ws-list');
        Route::get('/get-detail', 'getDetailList')->name('get-detail-list');
        Route::get('/get-detail-lok', 'getdetaillok')->name('get-detail-addlok');
        Route::get('/show-detail-lok', 'showdetaillok')->name('get-detail-showlok');
        Route::post('/save-lokasi', 'savelokasi')->name('save-lokasi');
        Route::get('/approve-material', 'approvematerial')->name('approve-material');
        Route::post('/print-barcode-inmaterial/{id?}', 'barcodeinmaterial')->name('print-barcode-inmaterial');
        Route::post('/print-pdf-inmaterial/{id?}', 'pdfinmaterial')->name('print-pdf-inmaterial');
        Route::get('/upload-lokasi/{id?}', 'UploadLokasi')->name('upload-lokasi');
        Route::get('/data-upload-lokasi', 'DataUploadLokasi')->name('data-upload-lokasi');
        Route::get('/delete-upload', 'DeleteDataUpload')->name('delete-upload');
        Route::post('/import-excel-material', 'import_excel')->name('import-excel-material');
        Route::get('/get-qty-upload', 'getqtyupload')->name('get-qty-upload');
        Route::post('/save-upload-lokasi', 'saveuploadlokasi')->name('save-upload-lokasi');
    });

    //permintaan
    Route::controller(ReqMaterialController::class)->prefix("req-material")->middleware('req-material')->group(function () {
        Route::get('/', 'index')->name('req-material');
        Route::get('/create', 'create')->name('create-reqmaterial');
        Route::get('/get-ws-req', 'getWSReq')->name('get-ws-req');
        Route::get('/get-ws-act', 'getWSact')->name('get-ws-act');
        Route::get('/show-detail', 'showdetail')->name('get-detail-req');
        Route::get('/sum-detail', 'sumdetail')->name('get-sum-req');
        Route::post('/store', 'store')->name('store-reqmaterial-fabric');
        Route::post('/print-pdf-reqmaterial/{bppbno?}', 'pdfreqmaterial')->name('print-pdf-reqmaterial');
        Route::get('/edit-request/{id?}', 'editrequest')->name('edit-reqmaterial');
        Route::get('/update-req-fabric', 'updateReq')->name('update-reqmaterial-fabric');
    });

    //Pengeluaran
    Route::controller(OutMaterialController::class)->prefix("out-material")->middleware('out-material')->group(function () {
        Route::get('/', 'index')->name('out-material');
        Route::get('/create', 'create')->name('create-outmaterial');
        Route::get('/get-detail_req', 'getdetailreq')->name('get-detail_req');
        Route::get('/get-detail', 'getDetailList')->name('get-detail-item');
        Route::get('/show-detail-item', 'showdetailitem')->name('get-detail-showitem');
        Route::get('/get-list-barcode', 'getListbarcode')->name('get-list-barcode');
        Route::get('/get-data-barcode', 'showdetailbarcode')->name('get-data-barcode');
        Route::post('/save-out-manual', 'saveoutmanual')->name('save-out-manual');
        Route::post('/save-out-scan', 'saveoutscan')->name('save-out-scan');
        Route::post('/store', 'store')->name('store-outmaterial-fabric');
        Route::get('/approve-outmaterial', 'approveOutMaterial')->name('approve-outmaterial');
        Route::post('/print-pdf-outmaterial/{id?}', 'pdfoutmaterial')->name('print-pdf-outmaterial');
        Route::get('/delete-scan-temp', 'deletescantemp')->name('delete-scan-temp');
        Route::get('/delete-all-temp', 'deletealltemp')->name('delete-all-temp');
    });


    //mutasi-lokasi
    Route::controller(MutLokasiController::class)->prefix("mutasi-lokasi")->middleware('mutasi-lokasi')->group(function () {
        Route::get('/', 'index')->name('mutasi-lokasi');
        Route::get('/create', 'create')->name('create-mutlokasi');
        Route::get('/get-rak', 'getRakList')->name('get-rak-list');
        Route::get('/get-list-roll', 'getListroll')->name('get-list-roll');
        Route::get('/get-sum-roll', 'getSumroll')->name('get-sum-roll');
        Route::post('/store', 'store')->name('store-mutlokasi');
        Route::get('/approve-mutlok', 'approvemutlok')->name('approve-mutlok');
        Route::get('/edit-mutlok/{id?}', 'editmutlok')->name('edit-mutlok');
        Route::get('/update-mutlokasi', 'updatemutlok')->name('update-mutlokasi');
    });

    //Retur
    Route::controller(ReturMaterialController::class)->prefix("retur-material")->middleware('retur-material')->group(function () {
        Route::get('/', 'index')->name('retur-material');
        Route::get('/create', 'create')->name('create-returmaterial');
        Route::get('/get-no-bpb', 'getNobpb')->name('get-no-bpb');
        Route::get('/get-detail', 'getDetailBpb')->name('get-detail-bpb');
        Route::get('/show-detail-itemro', 'showdetailitemro')->name('get-detail-item-ro');
        Route::get('/get-list-barcode-ro', 'getListbarcodero')->name('get-list-barcode-ro');
        Route::get('/get-tujuan-pemasukan-ro', 'getTujuanRo')->name('get-tujuan-pemasukan-ro');
        Route::get('/get-data-barcode-ro', 'showdetailbarcodeRo')->name('get-data-barcode-ro');
        Route::post('/save-out-scan-ro', 'saveoutscanRo')->name('save-out-scan-ro');
        Route::post('/save-out-manual-ro', 'saveoutmanualRo')->name('save-out-manual-ro');
        Route::post('/store', 'store')->name('store-returmaterial-fabric');
        Route::get('/get-supplier-ro', 'getSuppro')->name('get-supplier-ro');
        Route::get('/approve-returmaterial', 'approveReturMaterial')->name('approve-returmaterial');
        Route::get('/barcode-ro/{id?}', 'barcodeRO')->name('barcode-ro');
        Route::get('/ro-list-barcode', 'ROListbarcode')->name('ro-list-barcode');
        Route::post('/save-ro-scan', 'saveroscan')->name('save-ro-scan');
    });

    //Retur Penerimaan
    Route::controller(ReturInMaterialController::class)->prefix("retur-inmaterial")->middleware('retur-inmaterial')->group(function () {
        Route::get('/', 'index')->name('retur-inmaterial');
        Route::get('/create', 'create')->name('create-retur-inmaterial');
        Route::get('/get-no-bppb', 'getNobppb')->name('get-no-bppb');
        Route::get('/get-tujuan-pemasukan', 'getTujuan')->name('get-tujuan-pemasukan');
        Route::get('/get-supplier-ri', 'getSuppri')->name('get-supplier-ri');
        Route::get('/get-list-bppb', 'getListBppb')->name('get-list-bppb');
        Route::post('/store', 'store')->name('store-retur-inmaterial-fabric');
        Route::get('/lokasi-retur-material/{id?}', 'lokreturmaterial')->name('lokasi-retur-inmaterial');
        Route::post('/save-lokasi-retur', 'savelokasiretur')->name('save-lokasi-retur');
        Route::get('/upload-lokasi-retur/{id?}', 'UploadLokasiRetur')->name('upload-lokasi-retur');
        Route::post('/save-upload-lokasi-retur', 'saveuploadlokasirtr')->name('save-upload-lokasi-retur');
        Route::get('/approve-material-retur', 'approvematerialretur')->name('approve-material-retur');
    });

    //qc pass
    Route::controller(QcPassController::class)->prefix("qc-pass")->middleware('qc-pass')->group(function () {
        Route::get('/', 'index')->name('qc-pass');
        Route::post('/store', 'store')->name('store-qcpass');
        Route::get('/get-data-item', 'getListItem')->name('get-data-item');
        Route::get('/get-data-item2', 'getListItem2')->name('get-data-item2');
        Route::get('/get-defect', 'getdefect')->name('get-defect');
        Route::get('/create-qcpass/{id?}', 'create')->name('create-qcpass');
        Route::post('/store-defect', 'storedefect')->name('store-defect');
        Route::post('/store-qcdet-temp', 'storeQcTemp')->name('store-qcdet-temp');
        Route::post('/store-qcdet-save', 'storeQcSave')->name('store-qcdet-save');
        Route::get('/get-detail-defect', 'getDetailList')->name('get-detail-defect');
        Route::get('/get-sum-data', 'getDataSum')->name('get-sum-data');
        Route::get('/get-avg-poin', 'getavgpoin')->name('get-avg-poin');
        Route::get('/get-poin', 'getpoin')->name('get-poin');
        Route::get('/finish-data', 'finishdata')->name('finish-data');
        Route::get('/finish-data-modal', 'finishdatamodal')->name('finish-data-modal');
        Route::get('/get_data_detailqc', 'getdatadetailqc')->name('get_data_detailqc');
        Route::get('/delete-qc-temp', 'deleteqctemp')->name('delete-qc-temp');
        Route::get('/show-qcpass/{id?}', 'showdata')->name('show-qcpass');
        Route::get('/export-qcpass/{id?}', 'exportdata')->name('export-qcpass');
        Route::get('/get-no-form', 'getnoform')->name('get-no-form');
        Route::get('/delete-qc-det', 'deleteqcdet')->name('delete-qc-det');
    });

    //laporan detail pemasukan
    Route::controller(LapDetPemasukanController::class)->prefix("lap_det_pemasukan")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-det-pemasukan');
        // export excel
        Route::get('/export_excel_pemasukan', 'export_excel_pemasukan')->name('export_excel_pemasukan');
        // Route::get('/export', 'export')->name('export');
    });

    //laporan detail pemasukan roll
    Route::controller(LapDetPemasukanRollController::class)->prefix("lap_det_pemasukanroll")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-det-pemasukanroll');
        Route::get('/export_excel_pemasukanroll', 'export_excel_roll')->name('export_excel_pemasukanroll');
    });

    //laporan detail pengeluaran
    Route::controller(LapDetPengeluaranController::class)->prefix("lap_det_pengeluaran")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-det-pengeluaran');
        Route::get('/export_excel_pengeluaran', 'export_excel_pengeluaran')->name('export_excel_pengeluaran');
    });

    //laporan detail pengeluaran roll
    Route::controller(LapDetPengeluaranRollController::class)->prefix("lap_det_pengeluaranroll")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-det-pengeluaranroll');
        Route::get('/export_excel_pengeluaranroll', 'export_excel_roll')->name('export_excel_pengeluaranroll');
    });

    //laporan mutasi global
    Route::controller(LapMutasiGlobalController::class)->prefix("lap-mutasi-global")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-mutasi-global');
        // export excel
        Route::get('/export_excel_mut_global', 'export_excel_mut_global')->name('export_excel_mut_global');
        // Route::get('/export', 'export')->name('export');
    });

    //laporan mutasi detail
    Route::controller(LapMutasiDetailController::class)->prefix("lap-mutasi-detail")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-mutasi-detail');
        // export excel
        Route::get('/export_excel_mut_detail', 'export_excel_mut_detail')->name('export_excel_mut_detail');
        // Route::get('/export', 'export')->name('export');
    });

    //konfirmasi penerimaan
    Route::controller(KonfPemasukanController::class)->prefix("konfirmasi-pemasukan")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('konfirmasi-pemasukan');
        Route::get('/approve-material-all', 'approvematerialall')->name('approve-material-all');
        Route::get('/get-data-penerimaan', 'getdatapenerimaan')->name('get-data-penerimaan');
    });

    //konfirmasi pengeluaran
    Route::controller(KonfPengeluaranController::class)->prefix("konfirmasi-pengeluaran")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('konfirmasi-pengeluaran');
        Route::get('/approve-pengeluaran-all', 'approvepengeluaranall')->name('approve-pengeluaran-all');
        Route::get('/get-data-pengeluaran', 'getdatapengeluaran')->name('get-data-pengeluaran');
    });

    //transfer bpb
    Route::controller(TransferBpbController::class)->prefix("transfer-bpb")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('transfer-bpb');
        Route::get('/create', 'create')->name('create-transfer-bpb');
        Route::post('/store', 'store')->name('save-transferbpb');
        Route::get('/cancel-transfer', 'canceltransfer')->name('cancel-transfer');
    });


    //FG Stock
    // Master Lokasi FG Stock
    Route::controller(FGStokMasterController::class)->prefix("master-lokasi-fg-stock")->middleware('fg-stock')->group(function () {
        Route::get('/', 'index')->name('master-lokasi-fg-stock');
        Route::post('/store', 'store')->name('store-lokasi-fg-stock');
        Route::get('/master_sumber_penerimaan', 'master_sumber_penerimaan')->name('master-sumber-penerimaan');
        Route::post('/store_master_sumber_penerimaan', 'store_master_sumber_penerimaan')->name('store-master-sumber-penerimaan');
        Route::get('/master_tujuan_pengeluaran', 'master_tujuan_pengeluaran')->name('master-tujuan-pengeluaran');
        Route::post('/store_master_tujuan_pengeluaran', 'store_master_tujuan_pengeluaran')->name('store-master-tujuan-pengeluaran');
        // Route::put('/update/{id?}', 'update')->name('update-master-part');
        // Route::delete('/destroy/{id?}', 'destroy')->name('destroy-master-part');
    });

    Route::controller(FGStokBPBController::class)->prefix("bpb-fg-stock")->middleware('fg-stock')->group(function () {
        Route::get('/', 'index')->name('bpb-fg-stock');
        Route::post('/store', 'store')->name('store-bpb-fg-stock');
        Route::get('/create', 'create')->name('create-bpb-fg-stock');
        Route::get('/getno_ws', 'getno_ws')->name('getno_ws');
        Route::get('/getcolor', 'getcolor')->name('getcolor');
        Route::get('/getsize', 'getsize')->name('getsize');
        Route::get('/getproduct', 'getproduct')->name('getproduct');
        Route::post('/store_tmp', 'store_tmp')->name('store_tmp');
        Route::get('/show_tmp', 'show_tmp')->name('show_tmp');
        Route::post('/undo', 'undo')->name('undo');
        Route::get('/show_lok', 'show_lok')->name('show_lok');
        Route::get('/getdet_carton', 'getdet_carton')->name('getdet_carton');
        Route::get('/export_excel_bpb_fg_stok', 'export_excel_bpb_fg_stok')->name('export_excel_bpb_fg_stok');
        Route::post('/hapus_data_temp_bpb_fg_stok', 'hapus_data_temp_bpb_fg_stok')->name('hapus-data-temp-bpb-fg-stok');
    });

    Route::controller(FGStokBPPBController::class)->prefix("bppb-fg-stock")->middleware('fg-stock')->group(function () {
        Route::get('/', 'index')->name('bppb-fg-stock');
        Route::post('/store', 'store')->name('store-bppb-fg-stock');
        Route::get('/create', 'create')->name('create-bppb-fg-stock');
        Route::get('/getws', 'getws')->name('getws');
        Route::get('/show_det', 'show_det')->name('show_det');
        Route::get('/getstok', 'getstok')->name('getstok-bppb-fg-stock');
        Route::get('/export_excel_bppb_fg_stok', 'export_excel_bppb_fg_stok')->name('export_excel_bppb_fg_stok');
    });

    Route::controller(FGStokLaporanController::class)->prefix("laporan-fg-stock")->middleware('fg-stock')->group(function () {
        Route::get('/', 'index')->name('laporan-fg-stock');
        Route::get('/export_excel_mutasi_fg_stok', 'export_excel_mutasi_fg_stok')->name('export_excel_mutasi_fg_stok');
    });

    Route::controller(FGStokMutasiController::class)->prefix("mutasi-fg-stock")->middleware('fg-stock')->group(function () {
        Route::get('/', 'index')->name('mutasi-fg-stock');
        Route::post('/store', 'store')->name('store-mutasi-fg-stock');
        Route::get('/create', 'create')->name('create-mutasi-fg-stock');
        Route::get('/getno_karton_asal', 'getno_karton_asal')->name('getno-karton-asal-fg-stock');
        Route::get('/show_det_mutasi', 'show_det_mutasi')->name('show_det-fg-stock');
        Route::get('/export_excel_mutasi_int_fg_stok', 'export_excel_mutasi_int_fg_stok')->name('export_excel_mutasi_int_fg_stok');
    });

    // Packing
    // Transfer Garment
    Route::controller(PackingTransferGarmentController::class)->prefix("transfer-garment-packing")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('transfer-garment');
        Route::get('/create', 'create')->name('create-transfer-garment');
        Route::get('/get_po', 'get_po')->name('get_po');
        Route::get('/get_garment', 'get_garment')->name('get_garment');
        Route::post('/store_tmp_trf_garment', 'store_tmp_trf_garment')->name('store_tmp_trf_garment');
        Route::get('/show_tmp_trf_garment', 'show_tmp_trf_garment')->name('show_tmp_trf_garment');
        Route::post('/hapus_tmp_trf_garment', 'hapus_tmp_trf_garment')->name('hapus_tmp_trf_garment');
        Route::post('/store', 'store')->name('store_trf_garment');
        Route::post('/undo', 'undo')->name('undo-trf-garment');
        Route::post('/reset', 'reset')->name('reset-trf-garment');
        Route::get('/create_transfer_garment_temporary', 'create_transfer_garment_temporary')->name('create-transfer-garment-temporary');
        Route::get('/get_garment_temporary', 'get_garment_temporary')->name('get_garment_temporary');
        Route::get('/export_excel_trf_garment', 'export_excel_trf_garment')->name('export_excel_trf_garment');
        Route::post('/store_tmp_trf_garment_temporary', 'store_tmp_trf_garment_temporary')->name('store_tmp_trf_garment_temporary');
        Route::get('/show_tmp_trf_garment_temporary', 'show_tmp_trf_garment_temporary')->name('show_tmp_trf_garment_temporary');
        Route::post('/hapus_tmp_trf_garment_temporary', 'hapus_tmp_trf_garment_temporary')->name('hapus_tmp_trf_garment_temporary');
        Route::post('/store_trf_garment_temporary', 'store_trf_garment_temporary')->name('store_trf_garment_temporary');
        Route::post('/undo_trf_garment_temporary', 'undo_trf_garment_temporary')->name('undo_trf_garment_temporary');
        Route::post('/reset_trf_garment_temporary', 'reset_trf_garment_temporary')->name('reset_trf_garment_temporary');
        Route::get('/stok_temporary_transfer_garment', 'stok_temporary_transfer_garment')->name('stok-temporary-transfer-garment');
    });

    // Packing In
    Route::controller(PackingPackingInController::class)->prefix("packing-in-packing")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('packing-in');
        Route::get('/show_preview_packing_in', 'show_preview_packing_in')->name('show_preview_packing_in');
        Route::post('/store', 'store')->name('store-packing-packing-in');
        Route::get('/export_excel_packing_in', 'export_excel_packing_in')->name('export_excel_packing_in');
    });

    // Packing Out
    Route::controller(PackingPackingOutController::class)->prefix("packing-out-packing")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('packing-out');
        Route::get('/create', 'create')->name('create-packing-out');
        Route::get('/getno_carton', 'getno_carton')->name('getno_carton');
        Route::get('/getpo', 'getpo')->name('getpo');
        Route::get('/packing_out_show_summary', 'packing_out_show_summary')->name('packing_out_show_summary');
        Route::get('/packing_out_show_tot_input', 'packing_out_show_tot_input')->name('packing_out_show_tot_input');
        Route::get('/packing_out_show_history', 'packing_out_show_history')->name('packing_out_show_history');
        Route::post('/store', 'store')->name('store_packing_out');
        Route::get('/export_excel_packing_out', 'export_excel_packing_out')->name('export_excel_packing_out');
        Route::post('/packing_out_hapus_history', 'packing_out_hapus_history')->name('packing_out_hapus_history');
    });

    // Master Karton
    Route::controller(PackingMasterKartonController::class)->prefix("packing-master-karton")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('master-karton');
        Route::post('/store', 'store')->name('store-tambah-karton');
        Route::get('/show_tot', 'show_tot')->name('show_tot');
        Route::get('/show_detail_karton', 'show_detail_karton')->name('show_detail_karton');
        // Route::get('/show_preview_packing_in', 'show_preview_packing_in')->name('show_preview_packing_in');
        // Route::post('/store', 'store')->name('store-packing-packing-in');
    });


    // PPIC
    // Master
    Route::controller(PPIC_MasterSOController::class)->prefix("master-so-ppic")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('master-so');
        Route::post('/import-excel-so', 'import_excel_so')->name('import-excel-so');
        Route::get('/show_tmp_ppic_so', 'show_tmp_ppic_so')->name('show_tmp_ppic_so');
        Route::get('/contoh_upload_ppic_so', 'contoh_upload_ppic_so')->name('contoh_upload_ppic_so');
        Route::post('/undo_tmp_ppic_so', 'undo_tmp_ppic_so')->name('undo_tmp_ppic_so');
        Route::get('/export_excel_master_sb_so', 'export_excel_master_sb_so')->name('export_excel_master_sb_so');
        Route::post('/store_tmp_ppic_so', 'store_tmp_ppic_so')->name('store_tmp_ppic_so');
        Route::get('/export_excel_master_so_ppic', 'export_excel_master_so_ppic')->name('export_excel_master_so_ppic');
        Route::post('/hapus_data_temp_ppic_so', 'hapus_data_temp_ppic_so')->name('hapus-data-temp-ppic-so');
        Route::get('/master_so_tracking_output', 'master_so_tracking_output')->name('master_so_tracking_output');
        Route::get('/show_data_ppic_master_so', 'show_data_ppic_master_so')->name('show_data_ppic_master_so');
        Route::post('/update_data_ppic_master_so', 'update_data_ppic_master_so')->name('update_data_ppic_master_so');
        Route::get('/list_master_ppic_edit', 'list_master_ppic_edit')->name('list_master_ppic_edit');
        Route::post('/edit_multiple_ppic_master_so', 'edit_multiple_ppic_master_so')->name('edit_multiple_ppic_master_so');
        Route::get('/getpo_ppic_edit_tgl', 'getpo_ppic_edit_tgl')->name('getpo_ppic_edit_tgl');
        Route::post('/update_tgl_ppic_master_so', 'update_tgl_ppic_master_so')->name('update_tgl_ppic_master_so');
        Route::get('/getpo_ppic_hapus', 'getpo_ppic_hapus')->name('getpo_ppic_hapus');
        Route::post('/hapus_multiple_ppic_master_so', 'hapus_multiple_ppic_master_so')->name('hapus_multiple_ppic_master_so');
    });

    // PPIC Laporan Tracking
    Route::controller(PPIC_LaporanTrackingController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('ppic-laporan-tracking');
        Route::get('/show_lap_tracking_ppic', 'show_lap_tracking_ppic')->name('show_lap_tracking_ppic');
        Route::get('/export_excel_tracking', 'export_excel_tracking')->name('export_excel_tracking');
    });


    // GA
    // Pengajuan
    Route::controller(GAPengajuanBahanBakarController::class)->prefix("ga-pengajuan-bahan-bakar")->middleware('ga')->group(function () {
        Route::get('/', 'index')->name('pengajuan-bahan-bakar');
        Route::post('/store_ga_master_bahan_bakar', 'store_ga_master_bahan_bakar')->name('store-ga-master-bahan-bakar');
        Route::get('/show_master_bahan_bakar', 'show_master_bahan_bakar')->name('show-master-bahan-bakar');
        Route::post('/store_ga_master_kendaraan', 'store_ga_master_kendaraan')->name('store-ga-master-kendaraan');
        Route::get('/show_master_kendaraan', 'show_master_kendaraan')->name('show-master-kendaraan');
        Route::get('/show_getnip', 'show_getnip')->name('show-ga-get-nip');
        Route::get('/show_getjns', 'show_getjns')->name('show-ga-get-jns');
        Route::get('/show_getbhn_bakar', 'show_getbhn_bakar')->name('show-ga-get-bhn-bakar');
        Route::get('/show_getharga', 'show_getharga')->name('show-ga-get-harga');
        Route::post('/store_ga_trans', 'store_ga_trans')->name('store-ga-trans');
        Route::get('/export_pdf_pengajuan_bhn_bakar', 'export_pdf_pengajuan_bhn_bakar')->name('export_pdf_pengajuan_bhn_bakar');
        Route::get('/show_data_bahan_bakar', 'show_data_bahan_bakar')->name('show_data_bahan_bakar');
        Route::post('/update_ga_master_bahan_bakar', 'update_ga_master_bahan_bakar')->name('update-ga-master-bahan-bakar');
        Route::get('/show_data_transaksi_edit', 'show_data_transaksi_edit')->name('show_data_transaksi_edit');
        Route::get('/show_data_transaksi', 'show_data_transaksi')->name('show_data_transaksi');
        Route::get('/show_ga_get_jns_edit', 'show_ga_get_jns_edit')->name('show_ga_get_jns_edit');
        Route::get('/show_ga_get_bhn_bakar_edit', 'show_ga_get_bhn_bakar_edit')->name('show_ga_get_bhn_bakar_edit');
        Route::post('/update_ga_trans', 'update_ga_trans')->name('update_ga_trans');
        Route::post('/update_ga_realisasi', 'update_ga_realisasi')->name('update_ga_realisasi');

        // Route::post('/store', 'store')->name('store-packing-packing-in');
    });

    // Approval
    Route::controller(GAApprovalBahanBakarController::class)->prefix("ga-approval-bahan-bakar")->middleware('ga')->group(function () {
        Route::get('/', 'index')->name('approval-bahan-bakar');
        Route::post('/store', 'store')->name('store-approval-bahan-bakar');
    });


    Route::controller(StockDcCompleteController::class)->prefix("stock-dc-complete")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-complete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-complete-detail');
    });

    Route::controller(StockDcIncompleteController::class)->prefix("stock-dc-incomplete")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-incomplete');
        Route::get('/show/{partId?}/{color?}/{size?}', 'show')->name('stock-dc-incomplete-detail');
    });

    Route::controller(StockDcWipController::class)->prefix("stock-dc-wip")->middleware('admin')->group(function () {
        Route::get('/', 'index')->name('stock-dc-wip');
        Route::get('/show/{partId?}', 'show')->name('stock-dc-wip-detail');
    });
});

// Dashboard
Route::get('/dashboard-track', [DashboardController::class, 'track'])->middleware('auth')->name('dashboard-track');
Route::get('/dashboard-marker', [DashboardController::class, 'marker'])->middleware('auth')->name('dashboard-marker');
Route::get('/marker-qty', [DashboardController::class, 'markerQty'])->middleware('auth')->name('marker-qty');
Route::get('/dashboard-cutting', [DashboardController::class, 'cutting'])->middleware('auth')->name('dashboard-cutting');
Route::get('/cutting-qty', [DashboardController::class, 'cuttingQty'])->middleware('auth')->name('cutting-qty');
Route::get('/dashboard-dc', [DashboardController::class, 'dc'])->middleware('auth')->name('dashboard-dc');
Route::get('/dc-qty', [DashboardController::class, 'dcQty'])->middleware('auth')->name('dc-qty');
Route::get('/dashboard-sewing-eff', [DashboardController::class, 'sewingEff'])->middleware('auth')->name('dashboard-sewing-eff');
Route::get('/sewing-summary', [DashboardController::class, 'sewingSummary'])->middleware('auth')->name('dashboard-sewing-sum');
Route::get('/sewing-output-data', [DashboardController::class, 'sewingOutputData'])->middleware('auth')->name('dashboard-sewing-output');

// Dashboard
// Route::get('/dashboard-marker', function () {
//     return view('dashboard', ['page' => 'dashboard-marker']);
// })->middleware('auth')->name('dashboard-marker');

// Route::get('/dashboard-cutting', function () {
//     return view('dashboard', ['page' => 'dashboard-cutting']);
// })->middleware('auth')->name('dashboard-cutting');

Route::get('/dashboard-stocker', function () {
    return view('dashboard', ['page' => 'dashboard-stocker']);
})->middleware('auth')->name('dashboard-stocker');

//warehouse
// Route::get('/dashboard-warehouse', function () {
//     return view('dashboard-fabric', ['page' => 'dashboard-warehouse']);
// })->middleware('auth')->name('dashboard-warehouse');

//fg stock
Route::get('/dashboard-fg-stock', function () {
    return view('dashboard', ['page' => 'dashboard-fg-stock']);
})->middleware('auth')->name('dashboard-fg-stock');

//Packing
Route::get('/dashboard-packing', function () {
    return view('dashboard', ['page' => 'dashboard-packing']);
})->middleware('auth')->name('dashboard-packing');

//GA
Route::get('/dashboard-ga', function () {
    return view('dashboard', ['page' => 'dashboard-ga']);
})->middleware('auth')->name('dashboard-ga');

//PPIC
Route::get('/dashboard-ppic', function () {
    return view('dashboard', ['page' => 'dashboard-ppic']);
})->middleware('auth')->name('dashboard-ppic');

Route::get('/dashboard-mut-karyawan', function () {
    return view('dashboard', ['page' => 'dashboard-mut-karyawan']);
})->middleware('auth')->name('dashboard-mut-karyawan');

Route::get('/dashboard-mut-mesin', function () {
    return view('dashboard-mesin', ['page' => 'dashboard-mut-mesin']);
})->middleware('auth')->name('dashboard-mut-mesin');

// Misc
Route::get('/timer', function () {
    return view('example.timeout');
})->middleware('auth');

Route::get('/widgets', function () {
    return view('component.widgets');
})->middleware('auth');

Route::get('/kanban', function () {
    return view('component.kanban');
})->middleware('auth');

Route::get('/gallery', function () {
    return view('component.gallery');
})->middleware('auth');

Route::get('/calendar', function () {
    return view('component.calendar');
})->middleware('auth');

Route::get('/timeline', function () {
    return view('component.UI.timeline');
})->middleware('auth');

Route::get('/sliders', function () {
    return view('component.UI.sliders');
})->middleware('auth');

Route::get('/modals', function () {
    return view('component.UI.modals');
})->middleware('auth');

Route::get('/ribbons', function () {
    return view('component.UI.ribbons');
})->middleware('auth');

Route::get('/general', function () {
    return view('component.UI.general');
})->middleware('auth');

Route::get('/datatable', function () {
    return view('component.tables.data');
})->middleware('auth');

Route::get('/jsgrid', function () {
    return view('component.tables.jsgrid');
})->middleware('auth');

Route::get('/simpletable', function () {
    return view('component.tables.simple');
})->middleware('auth');

Route::get('/advanced-form', function () {
    return view('component.forms.advanced');
})->middleware('auth');

Route::get('/general-form', function () {
    return view('component.forms.general');
})->middleware('auth');

Route::get('/validation-form', function () {
    return view('component.forms.validation');
})->middleware('auth');

Route::get('/bon-mutasi', function () {
    return view('bon-mutasi');
})->middleware('auth');
