<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// User
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\ManageRoleController;
use App\Http\Controllers\ManageAccessController;
use App\Http\Controllers\ManageUserLineController;

// Dashboard WIP Line
use App\Http\Controllers\DashboardWipLineController;

// General
use App\Http\Controllers\GeneralController;

// Worksheet
use App\Http\Controllers\WorksheetController;
use App\Events\TestEvent;

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
use App\Http\Controllers\Cutting\CuttingFormRejectController;
use App\Http\Controllers\Cutting\CuttingFormPieceController;
use App\Http\Controllers\Cutting\PipingController;
use App\Http\Controllers\Cutting\CuttingPlanController;
use App\Http\Controllers\Cutting\ReportCuttingController;
use App\Http\Controllers\Cutting\CompletedFormController;
use App\Http\Controllers\Cutting\RollController;
// Piping Process
use App\Http\Controllers\Cutting\MasterPipingController;
use App\Http\Controllers\Cutting\PipingProcessController;
use App\Http\Controllers\Cutting\PipingLoadingController;
use App\Http\Controllers\Cutting\PipingStockController;
// Cutting Tools
use App\Http\Controllers\Cutting\CuttingToolsController;

// Stocker
use App\Http\Controllers\Stocker\StockerController;
use App\Http\Controllers\Stocker\StockerToolsController;

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
use App\Http\Controllers\DC\BonLoadingController;
use App\Http\Controllers\DC\DcToolsController;

// Sewing
use App\Http\Controllers\Sewing\MasterLineController;
use App\Http\Controllers\Sewing\MasterPlanController;
use App\Http\Controllers\Sewing\MasterDefectController;
use App\Http\Controllers\Sewing\ReportController;
use App\Http\Controllers\Sewing\OrderDefectController;
use App\Http\Controllers\Sewing\TrackOrderOutputController;
use App\Http\Controllers\Sewing\TransferOutputController;
use App\Http\Controllers\Sewing\LineDashboardController;
use App\Http\Controllers\Sewing\LineWipController;
use App\Http\Controllers\Sewing\UndoOutputController;
use App\Http\Controllers\Sewing\ReportDefectController;
use App\Http\Controllers\Sewing\SewingToolsController;

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
use App\Http\Controllers\Sewing\ReportEfficiencyNewController;
use App\Http\Controllers\Sewing\ReportDetailOutputController;
use App\Http\Controllers\Sewing\ReportMutasiOutputController;

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
use App\Http\Controllers\MutasiMesinStockOpnameController;
use App\Http\Controllers\MutasiMesinMasterController;
use App\Http\Controllers\MutasiMesinLaporanController;
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
use App\Http\Controllers\TransferBpbController;
use App\Http\Controllers\LapMutasiBarcodeController;

use App\Http\Controllers\PPICDashboardController;

//  PPIC
use App\Http\Controllers\PPIC_MasterSOController;
use App\Http\Controllers\PPIC_LaporanTrackingController;
use App\Http\Controllers\PPIC_MonitoringMaterialController;
use App\Http\Controllers\PPIC_MonitoringMaterialDetController;
use App\Http\Controllers\PPIC_MonitoringMaterialSumController;
use App\Http\Controllers\ReportHourlyController;
use App\Http\Controllers\BarcodePackingController;
use App\Http\Controllers\PPIC_tools_adjustmentController;
// PACKING
use App\Http\Controllers\PackingDashboardController;
use App\Http\Controllers\PackingTransferGarmentController;
use App\Http\Controllers\PackingPackingInController;
use App\Http\Controllers\PackingPackingOutController;
use App\Http\Controllers\PackingNeedleCheckController;
use App\Http\Controllers\PackingMasterKartonController;
use App\Http\Controllers\PackingPackingListController;
use App\Http\Controllers\PackingReportController;

// FINISH GOOD
use App\Http\Controllers\FinishGoodDashboardController;
use App\Http\Controllers\FinishGoodMasterLokasiController;
use App\Http\Controllers\FinishGoodPenerimaanController;
use App\Http\Controllers\FinishGoodPengeluaranController;
use App\Http\Controllers\FinishGoodReturController;
use App\Http\Controllers\FinishGoodAlokasiKartonController;

// REPORT DOC
use App\Http\Controllers\ReportDocController;

// GA GAIS
use App\Http\Controllers\GAPengajuanBahanBakarController;
use App\Http\Controllers\GAApprovalBahanBakarController;
use App\Http\Controllers\StockOpnameController;

use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\AccountingController;

// Marketing
use App\Http\Controllers\MarketingDashboardController;
use App\Http\Controllers\Marketing_CostingController;

// QC Inspect Kain
use App\Http\Controllers\QCInspectDashboardController;
use App\Http\Controllers\QCInspectProsesPackingListController;

//maintain-bpb
use App\Http\Controllers\MaintainBpbController;
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
        // get panels new
        Route::get('/get-panels-new', 'getPanelListNew')->name('get-panels');

        Route::get('/general-tools', 'generalTools')->middleware('role:superadmin')->name('general-tools');
        Route::post('/update-general-order', 'updateGeneralOrder')->middleware('role:superadmin')->name('update-general-order');

        // get scanned employee
        Route::get('/get-scanned-employee/{id?}', 'getScannedEmployee')->name('get-scanned-employee');

        // cutting items
        Route::get('/get-scanned-item/{id?}', 'getScannedItem')->name('get-scanned-form-cut-input');
        Route::get('/get-item', 'getItem')->name('get-item-form-cut-input');
    });

    // Worksheet
    Route::controller(WorksheetController::class)->prefix("worksheet")->group(function () {
        // get worksheet
        Route::get('/', 'index')->name('worksheet');
        Route::post('/print-qr', 'printQr')->name('worksheet-print-qr');
    });

    // Part :
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
        Route::get('/get_proses', 'get_proses')->name('get_proses');
        Route::post('/store_part_secondary', 'store_part_secondary')->name('store_part_secondary');
        Route::put('/update-part-secondary', 'updatePartSecondary')->name('update-part-secondary');

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

    // Cutting :
    // Spreading
    Route::controller(SpreadingController::class)->prefix("spreading")->middleware('role:cutting')->group(function () {
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
        Route::post('/export', 'exportExcel')->name('export-cutting-form');
        // export excel
        // Route::get('/export_excel', 'export_excel')->name('export_excel');
        // Route::get('/export', 'export')->name('export');
    });

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

    // Piping  Controller
    Route::controller(PipingController::class)->prefix("form-cut-piping")->middleware("role:cutting")->group(function () {
        Route::get('/', 'index')->name('form-cut-piping');
        Route::get('/create', 'create')->name('create-piping');
        Route::post('/store', 'store')->name('store-piping');

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
        Route::get('/item/piping/{id?}/{idForm?}', 'itemPiping')->name('item-piping-piping');
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
    });

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
    });

    // CompletedForm
    Route::controller(CompletedFormController::class)->prefix("manager")->middleware("role:cutting")->group(function () {
        Route::get('/cutting', 'cutting')->name('manage-cutting');
        Route::get('/cutting/detail/{id?}', 'detailCutting')->name('detail-cutting');
        Route::put('/cutting/generate/{id?}', 'generateStocker')->name('generate-stocker');
        Route::post('/cutting/update-form', 'updateCutting')->name('update-spreading-form');
        Route::put('/cutting/update-finish/{id?}', 'updateFinish')->name('finish-update-spreading-form');
        Route::delete('/cutting/destroy-roll/{id?}', 'destroySpreadingRoll')->name('destroy-spreading-roll');
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

        // export excel
        Route::post('/cutting/export', 'export')->name('report-cutting-export');
        Route::post('/cutting-daily/export', 'cuttingDailyExport')->name('report-cutting-daily-export');
        Route::post('/pemakaian-roll/export', 'pemakaianRollExport')->name('pemakaian-roll-export');
        Route::post('/detail-pemakaian-roll/export', 'detailPemakaianRollExport')->name('detail-pemakaian-roll-export');
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
    });

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
    });

    // Stocker :
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
        Route::get('/modify-year-sequence-list', 'modifyYearSequenceList')->name('modify-year-sequence-list');
        Route::post('/modify-year-sequence-update', 'modifyYearSequenceUpdate')->name('update-year-sequence');
        Route::post('/modify-year-sequence-delete', 'modifyYearSequenceDelete')->name('delete-year-sequence');

        // get stocker
        Route::get('/get-stocker', 'getStocker')->name('get-stocker');
        Route::get('/get-stocker-month-count', 'getStockerMonthCount')->name('get-stocker-month-count');
        Route::get('/get-stocker-year-sequence', 'getStockerYearSequence')->name('get-stocker-year-sequence');

        // add
        Route::post('/print-stocker-all-size-add', 'printStockerAllSizeAdd')->name('print-stocker-all-size-add');
        Route::post('/submit-stocker-add', 'submitStockerAdd')->name('submit-stocker-add');

        // stocker reject
        Route::post('/print-stocker-reject-all-size/{partDetailId?}', 'printStockerRejectAllSize')->name('print-stocker-reject-all-size');
        Route::post('/print-stocker-reject-checked', 'printStockerRejectChecked')->name('print-stocker-reject-checked');
        Route::post('/print-stocker-reject/{id?}', 'printStockerReject')->name('print-stocker-reject');
    });

    // Stocker Tools
    Route::controller(StockerToolsController::class)->prefix("stocker")->middleware('role:superadmin')->group(function () {
        // form
        Route::get('/index', 'index')->name('stocker-tools');

        // reset stocker
        Route::post('/reset-stocker-form', 'resetStockerForm')->name('reset-stocker-form');
        Route::post('/reset-redundant-stocker', 'resetRedundantStocker')->name('reset-redundant-stocker');
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

        Route::get('/export-excel', 'exportExcel')->name('dc-in-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('dc-in-detail-export-excel');
    });

    // Secondary INHOUSE
    Route::controller(SecondaryInhouseController::class)->prefix("secondary-inhouse")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('secondary-inhouse');
        Route::get('/cek_data_stocker_inhouse', 'cek_data_stocker_inhouse')->name('cek_data_stocker_inhouse');
        Route::post('/store', 'store')->name('store-secondary-inhouse');
        Route::post('/mass-store', 'massStore')->name('mass-store-secondary-inhouse');
        Route::get('/detail_stocker_inhouse', 'detail_stocker_inhouse')->name('detail_stocker_inhouse');

        Route::get('/filter-sec-inhouse', 'filterSecondaryInhouse')->name('filter-sec-inhouse');
        Route::get('/filter-detail-sec-inhouse', 'filterDetailSecondaryInhouse')->name('filter-detail-sec-inhouse');

        Route::get('/export-excel', 'exportExcel')->name('secondary-inhouse-export-excel');
        Route::get('/export-excel-detail', 'exportExcelDetail')->name('secondary-inhouse-detail-export-excel');
    });

    // Secondary IN
    Route::controller(SecondaryInController::class)->prefix("secondary-in")->middleware('role:dc')->group(function () {
        Route::get('/', 'index')->name('secondary-in');
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
        Route::get('/modify-dc-qty', 'modifyDcQty')->middleware('role:superadmin')->name('modify-dc-qty');
        Route::get('/get-dc-qty', 'getDcQty')->middleware('role:superadmin')->name('get-dc-qty');
        Route::post('/update-dc-qty', 'updateDcQty')->middleware('role:superadmin')->name('update-dc-qty');
    });

    // Sewing :
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
    });

    // Master Plan
    Route::controller(MasterPlanController::class)->prefix("master-plan")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('master-plan');
        Route::get('show/{line?}/{date?}', 'show')->name('master-plan-detail');
        Route::put('update', 'update')->name('update-master-plan');
        Route::post('store', 'store')->name('store-master-plan');
        Route::delete('destroy/{id?}', 'destroy')->name('destroy-master-plan');
    });

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

    // Report Daily
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

    // Pareto Chart
    Route::controller(OrderDefectController::class)->prefix('order-defects')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('order-defects');
        Route::get('/{buyerId?}/{dateFrom?}/{dateTo?}/{type?}', 'getOrderDefects')->name('get-order-defects');
    });

    // Track Order Output
    Route::controller(TrackOrderOutputController::class)->prefix('track-order-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-track-order-output');
    });

    // Transfer Output
    Route::controller(TransferOutputController::class)->prefix('transfer-output')->middleware('role:superadmin')->group(function () {
        Route::get('/', 'index')->name('sewing-transfer-output');
    });

    // Sewing Input Output
    Route::controller(SewingInputOutput::class)->prefix('input-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-input-output');
    });

    // Dashboard List
    Route::controller(LineDashboardController::class)->prefix('line-dashboards')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('sewing-dashboard');
    });

    // Report
    Route::controller(MasterKursBiController::class)->prefix('master-kurs-bi')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('kursBi');
        Route::get('/get-data', 'getData')->name('kursBi.getData');
        Route::post('/scrap-data', 'scrapData')->name('kursBi.scrapData');
    });

    Route::controller(MasterJabatanController::class)->prefix('master-jabatan')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('jabatan');
        Route::get('/get-data', 'getData')->name('jabatan.getData');
        Route::post('/store-data', 'store')->name('jabatan.storeData');
        Route::put('/update-data', 'update')->name('jabatan.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('jabatan.destroyData');
    });

    Route::controller(MasterKaryawanController::class)->prefix('master-karyawan')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('karyawan');
        Route::get('/get-data', 'getData')->name('karyawan.getData');
        Route::put('/update-data', 'update')->name('karyawan.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('karyawan.destroyData');

        Route::get('/other-source', 'otherSource')->name('karyawan.otherSource');
        Route::post('/transfer-data', 'transfer')->name('karyawan.transferData');
    });

    Route::controller(MasterBuyerController::class)->prefix('master-buyer')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('buyer');
        Route::get('/get-data', 'getData')->name('buyer.getData');
        Route::put('/update-data', 'update')->name('buyer.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('buyer.destroyData');

        Route::get('/other-source', 'otherSource')->name('buyer.otherSource');
        Route::post('/transfer-data', 'transfer')->name('buyer.transferData');
    });

    Route::controller(DataProduksiController::class)->prefix('data-produksi')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('dataProduksi');
        Route::get('/get-data', 'getData')->name('dataProduksi.getData');
        Route::put('/update-data', 'update')->name('dataProduksi.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataProduksi.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataProduksi.transferData');
    });

    Route::controller(DataDetailProduksiController::class)->prefix('data-detail-produksi')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('dataDetailProduksi');
        Route::get('/get-data', 'getData')->name('dataDetailProduksi.getData');
        Route::put('/update-data', 'update')->name('dataDetailProduksi.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataDetailProduksi.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataDetailProduksi.transferData');
    });

    Route::controller(DataDetailProduksiDayController::class)->prefix('data-detail-produksi-day')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('dataDetailProduksiDay');
        Route::get('/get-data', 'getData')->name('dataDetailProduksiDay.getData');
        Route::put('/update-data', 'update')->name('dataDetailProduksiDay.updateData');
        Route::delete('/destroy-data/{id}', 'destroy')->name('dataDetailProduksiDay.destroyData');

        Route::post('/transfer-data', 'transfer')->name('dataDetailProduksiDay.transferData');
    });

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
        Route::get('/update-date-from', 'updateDateFrom')->name("update-date-from");

        Route::get('/defect-map', 'defectMap')->name("defect-map");
        Route::get('/defect-map/data', 'defectMapData')->name("defect-map-data");

        Route::post('/report-defect-export', 'reportDefectExport')->name("report-defect-export");
    });

    // Report Efficiency New
    Route::controller(ReportEfficiencyNewController::class)->prefix("report-efficiency-new")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportEfficiencynew');
        Route::get('/export_excel_rep_eff_new', 'export_excel_rep_eff_new')->name('export_excel_rep_eff_new');
    });


    // Report Mutasi Output
    Route::controller(ReportMutasiOutputController::class)->prefix("report-mut-output")->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('report_mut_output');
        Route::post('/show_mut_output', 'show_mut_output')->name('show_mut_output');
        Route::post('/export_excel_mut_output', 'export_excel_mut_output')->name('export_excel_mut_output');
    });

    Route::controller(ReportDetailOutputController::class)->prefix('report-detail-output')->middleware('role:sewing')->group(function () {
        Route::get('/', 'index')->name('reportDetailOutput');
        Route::get('/packing', 'packing')->name('reportDetailOutputPacking');
        Route::get('/get-data', 'getData')->name('reportDetailOutput.getData');
        Route::post('/export-data', 'exportData')->name('reportDetailOutput.exportData');
        Route::post('/export-data-packing', 'exportDataPacking')->name('reportDetailOutput.exportDataPacking');
        Route::post('/transfer-data', 'transfer')->name('reportDetailOutput.transferData');
    });

    // Line WIP
    Route::controller(LineWipController::class)->prefix("line-wip")->middleware('role:sewing')->group(function () {
        Route::get('/index', 'index')->name('line-wip');
        Route::get('/total', 'total')->name('total-line-wip');
        Route::get('/export-excel', 'exportExcel')->name('export-excel-line-wip');
    });

    // Track
    Route::controller(TrackController::class)->prefix("track")->middleware('role:sewing')->group(function () {
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

    // Undo History
    Route::controller(UndoOutputController::class)->prefix("undo-output")->middleware("sewing")->group(function () {
        Route::get('/', 'history')->name("undo-output-history");
    });

    // Sewing Tools
    Route::controller(SewingToolsController::class)->prefix("sewing-tools")->middleware("role:superadmin")->group(function () {
        Route::get('/', 'index')->name("sewing-tools");
        Route::post('/miss-user', 'missUser')->name("sewing-miss-user");
        Route::post('/miss-masterplan', 'missMasterPlan')->name("sewing-miss-masterplan");
        Route::post('/miss-rework', 'missRework')->name("sewing-miss-rework");
    });

    // Mutasi Mesin
    Route::controller(MutasiMesinController::class)->prefix("mut-mesin")->middleware("role:machine")->group(function () {
        Route::get('/', 'index')->name('mut-mesin');
        Route::get('/create', 'create')->name('create-mut-mesin');
        Route::post('/store', 'store')->name('store-mut-mesin');
        Route::get('/getdataline', 'getdataline')->name('getdataline');
        Route::get('/gettotal', 'gettotal')->name('gettotal');
        Route::get('/getdatamesin', 'getdatamesin')->name('getdatamesin');
        Route::get('/getdatalinemesin', 'getdatalinemesin')->name('getdatalinemesin');
        Route::get('/export_excel_mut_mesin', 'export_excel_mut_mesin')->name('export_excel_mut_mesin');
        Route::get('/line-chart-data', 'lineChartData')->name('line-chart-data');
        Route::post('/webcam_capture', 'webcam_capture')->name('webcam_capture');
    });
    Route::controller(MutasiMesinStockOpnameController::class)->prefix("mut-mesin")->middleware("role:machine")->group(function () {
        Route::get('/so_mesin', 'so_mesin')->name('so_mesin');
        Route::get('/export_excel_so_mesin', 'export_excel_so_mesin')->name('export_excel_so_mesin');
        Route::get('/so_mesin_detail_modal', 'so_mesin_detail_modal')->name('so_mesin_detail_modal');
        Route::get('/create_so_mesin', 'create_so_mesin')->name('create_so_mesin');
        Route::get('/getdata_so_mesin', 'getdata_so_mesin')->name('getdata_so_mesin');
        Route::post('/store_so_mesin', 'store_so_mesin')->name('store_so_mesin');
        Route::post('/update_ket_so_mesin', 'update_ket_so_mesin')->name('update_ket_so_mesin');
        Route::post('/so_mesin_delete', 'so_mesin_delete')->name('so_mesin_delete');
    });

    // Mutasi Mesin Master
    Route::controller(MutasiMesinMasterController::class)->prefix("master-mut-mesin")->middleware("role:machine")->group(function () {
        Route::get('/', 'index')->name('master-mut-mesin');
        Route::post('/store', 'store')->name('store-master-mut-mesin');
        Route::get('/export_excel_master_mesin', 'export_excel_master_mesin')->name('export_excel_master_mesin');
        Route::post('/hapus_data_mesin', 'hapus_data_mesin')->name('hapus-data-mesin');
        Route::get('/getdata_mesin', 'getdata_mesin')->name('getdata_mesin');
        Route::post('/edit_master_mut_mesin', 'edit_master_mut_mesin')->name('edit-master-mut-mesin');
        Route::get('/master_mesin_lokasi', 'master_mesin_lokasi')->name('master_mesin_lokasi');
        Route::post('/store_master_lokasi_mesin', 'store_master_lokasi_mesin')->name('store_master_lokasi_mesin');
    });

    // Laporan Mesin
    Route::controller(MutasiMesinLaporanController::class)->prefix("master-mut-mesin")->middleware("role:machine")->group(function () {
        Route::get('/lap_stok_mesin', 'lap_stok_mesin')->name('lap_stok_mesin');
        Route::get('/export_excel_stok_mesin', 'export_excel_stok_mesin')->name('export_excel_stok_mesin');
        Route::get('/lap_stok_detail_mesin', 'lap_stok_detail_mesin')->name('lap_stok_detail_mesin');
        Route::get('/export_excel_stok_detail_mesin', 'export_excel_stok_detail_mesin')->name('export_excel_stok_detail_mesin');
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

    // stock opname
    Route::controller(StockOpnameController::class)->prefix("so")->group(function () {
        // get worksheet
        Route::get('stock_opname/', 'index')->name('stock_opname');
        Route::get('/datarak', 'datarak')->name('data-rak');
        Route::get('/copysaldostok', 'copysaldostok')->name('copy-saldo-stokopname');
        Route::get('/copysaldostokpartial', 'copysaldostokpartial')->name('copy-saldo-stokopname-partial');
        Route::get('/replacesaldostok', 'replacesaldostok')->name('replace-saldo-stokopname');
        Route::get('/list-stok-opname', 'stokopname')->name('list-stok-opname');
        Route::get('/proses-scan-so/{lok?}/{nodok?}', 'prosesscanso')->name('proses-scan-so');
        Route::get('/get-barcode', 'getbarcodeso')->name('get-data-barcodeso');
        Route::get('/save-barcode', 'simpanbarcodeso')->name('simpan-scan-barcode-so');
        Route::get('/list-scan-barcode', 'listscanbarcode')->name('list-scan-barcode-so');
        Route::get('/list-scan-barcode-cancel', 'listscanbarcodecancel')->name('list-scan-barcode-so-cancel');
        Route::get('/get-sum-barcode', 'getsumbarcodeso')->name('get-sum-barcodeso');
        Route::get('/get-nomor-so', 'getNolapSO')->name('get-nomor-so');
        Route::get('/laporan-stok-opname', 'laporanstokopname')->name('laporan-stok-opname');
        Route::get('/export_excel_laporan_so', 'export_excel_laporanso')->name('export_excel_laporan_so');
        Route::get('/delete-so-temp', 'deletesotemp')->name('delete-so-temp');
        Route::get('/delete-so-temp-all', 'deletesotempall')->name('delete-so-temp-all');
        Route::get('/undo-so-temp', 'undosotemp')->name('undo-so-temp');
        Route::get('/undo-so-temp-all', 'undosotempall')->name('undo-so-temp-all');
        Route::get('/edit-barcode', 'editbarcodeso')->name('simpan-edit-barcode-so');
        Route::post('/store', 'store')->name('save-stockopname-fabric');
        Route::get('/detail-stock-opname', 'detailstokopname')->name('detail-stok-opname');
        Route::get('/export_excel_detail_so', 'export_excel_detailso')->name('export_excel_detail_so');
        Route::get('/get-list-partial-so', 'getListpartialso')->name('get-list-partial-so');
        Route::get('/get-list-partial-so-replace', 'getListpartialsoreplace')->name('get-list-partial-so-replace');
        Route::get('/show-detail-so/{id?}', 'showdetailso')->name('show-detail-so');
        Route::get('/list-so-detail-show', 'listsodetailshow')->name('list-so-detail-show');
        Route::get('/export_excel_laporan_so_detail', 'export_excel_laporanso_detail')->name('export_excel_laporan_so_detail');
        Route::get('/export_excel_laporan_so_detail_barcode', 'export_excel_laporanso_detail_barcode')->name('export_excel_laporan_so_detail_barcode');
        Route::get('/cancel-report-so', 'cancelreportso')->name('cancel-report-so');
        Route::get('/draft-report-so', 'draftreportso')->name('draft-report-so');
        Route::get('/final-report-so', 'finalreportso')->name('final-report-so');
        Route::get('/list-scan-barcode2', 'listscanbarcode2')->name('list-scan-barcode-so2');
        Route::get('/list-scan-barcode3', 'listscanbarcode3')->name('list-scan-barcode-so3');
    });

    // stock opname
    Route::controller(ProcurementController::class)->prefix("procurement")->group(function () {
        Route::get('procurement/', 'index')->name('procurement');
        Route::get('/detail-return-sb', 'detailreturnsb')->name('detail-return-sb');
        Route::get('/export_excel_detail_return_sb', 'export_excel_detailreturn_sb')->name('export_excel_detail_return_sb');
        Route::get('/simpanedit-returnsb', 'simpaneditreturnsb')->name('simpan-edit-returnsb');
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
        Route::get('/create-ri-cutting', 'createricutting')->name('create-retur-inmaterial-cutting');
        Route::get('/get-no-bppb-cutting', 'getNobppbCutting')->name('get-no-bppb-cutting');
        Route::get('/get-list-barcode-out', 'getListbarcodeout')->name('get-list-barcode-out');
        Route::get('/get-data-barcode-out', 'showdetailbarcodeout')->name('get-data-barcode-out');
        Route::post('/save-barcode-ri-scan', 'savebarcoderiscan')->name('save-barcode-ri-scan');
        Route::get('/delete-scanri-temp', 'deletescanritemp')->name('delete-scanri-temp');
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

    //laporan mutasi barcode
    Route::controller(LapMutasiBarcodeController::class)->prefix("lap-mutasi-barcode")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('lap-mutasi-barcode');
        // export excel
        Route::get('/export_excel_mut_barcode', 'export_excel_mut_barcode')->name('export_excel_mut_barcode');
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

    //maintain bpb
    Route::controller(MaintainBpbController::class)->prefix("maintain-bpb")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('maintain-bpb');
        Route::get('/create', 'create')->name('create-maintain-bpb');
        Route::post('/store', 'store')->name('save-maintainbpb');
        Route::get('/detail', 'detailmodal')->name('maintain-bpb-detail');
        Route::get('/cancel-maintain', 'cancelmaintain')->name('cancel-maintain');
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
        Route::get('/rep_mutasi_fg_stock', 'rep_mutasi_fg_stock')->name('rep_mutasi_fg_stock');
        Route::post('/export_excel_mutasi_fg_stock', 'export_excel_mutasi_fg_stock')->name('export_excel_mutasi_fg_stock');
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
    // Dashboard
    Route::controller(PackingDashboardController::class)->middleware('packing')->group(function () {
        Route::get('/dashboard_packing', 'dashboard_packing')->name('dashboard-packing');
        Route::get('/show_tot_dash_packing', 'show_tot_dash_packing')->name('show_tot_dash_packing');
    });

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
        Route::get('/packing_out_tot_barcode', 'packing_out_tot_barcode')->name('packing_out_tot_barcode');
        Route::get('/show_sum_max_carton', 'show_sum_max_carton')->name('show_sum_max_carton');
    });

    // Needle Check
    Route::controller(PackingNeedleCheckController::class)->prefix("packing-needle-check")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('needle-check');
        Route::get('/create', 'create')->name('create-needle-check');
        Route::post('/store_packing_needle', 'store_packing_needle')->name('store_packing_needle');
        Route::get('/packing_needle_check_show_summary', 'packing_needle_check_show_summary')->name('packing_needle_check_show_summary');
        Route::get('/packing_needle_check_show_history', 'packing_needle_check_show_history')->name('packing_needle_check_show_history');
        Route::get('/packing_needle_check_show_tot_input', 'packing_needle_check_show_tot_input')->name('packing_needle_check_show_tot_input');
        Route::post('/packing_needle_check_hapus_history', 'packing_needle_check_hapus_history')->name('packing_needle_check_hapus_history');
        Route::get('/export_excel_packing_needle_check', 'export_excel_packing_needle_check')->name('export_excel_packing_needle_check');
    });

    // Master Karton
    Route::controller(PackingMasterKartonController::class)->prefix("packing-master-karton")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('master-karton');
        Route::post('/store', 'store')->name('store-tambah-karton');
        Route::get('/show_tot', 'show_tot')->name('show_tot');
        Route::get('/show_detail_karton', 'show_detail_karton')->name('show_detail_karton');
        Route::get('/getno_carton_hapus', 'getno_carton_hapus')->name('getno_carton_hapus');
        Route::get('/list_data_no_carton', 'list_data_no_carton')->name('list_data_no_carton');
        Route::post('/hapus_master_karton_det', 'hapus_master_karton_det')->name('hapus_master_karton_det');
        Route::get('/getno_carton_tambah', 'getno_carton_tambah')->name('getno_carton_tambah');
        Route::get('/getbarcode_tambah', 'getbarcode_tambah')->name('getbarcode_tambah');
        Route::get('/list_data_no_carton_tambah', 'list_data_no_carton_tambah')->name('list_data_no_carton_tambah');
        Route::post('/store_tambah_data_karton_det', 'store_tambah_data_karton_det')->name('store_tambah_data_karton_det');
        Route::get('/get_data_stok_packing_in', 'get_data_stok_packing_in')->name('get_data_stok_packing_in');
        Route::post('/simpan_short_karton', 'simpan_short_karton')->name('simpan_short_karton');
        Route::get('/export_excel_packing_master_carton', 'export_excel_packing_master_carton')->name('export_excel_packing_master_carton');
        Route::get('/show_data_upload_karton', 'show_data_upload_karton')->name('show_data_upload_karton');
        Route::get('/export_data_po_upload', 'export_data_po_upload')->name('export_data_po_upload');
        Route::post('/upload_qty_karton', 'upload_qty_karton')->name('upload_qty_karton');
        Route::post('/delete_upload_po_karton', 'delete_upload_po_karton')->name('delete_upload_po_karton');
        Route::post('/store_upload_qty_karton', 'store_upload_qty_karton')->name('store_upload_qty_karton');
        Route::get('/list_data_no_carton_hapus', 'list_data_no_carton_hapus')->name('list_data_no_carton_hapus');
        Route::post('/hapus_master_karton', 'hapus_master_karton')->name('hapus_master_karton');
        // Route::get('/show_preview_packing_in', 'show_preview_packing_in')->name('show_preview_packing_in');
        // Route::post('/store', 'store')->name('store-packing-packing-in');
    });

    // Packing List
    Route::controller(PackingPackingListController::class)->prefix("packing-packing-list")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('packing-list');
        Route::post('/upload_packing_list', 'upload_packing_list')->name('upload-packing-list');
        Route::get('/show_det_po', 'show_det_po')->name('show_det_po');
        Route::get('/export_data_template_po_packing_list_horizontal', 'export_data_template_po_packing_list_horizontal')->name('export_data_template_po_packing_list_horizontal');
        Route::get('/export_data_template_po_packing_list_vertical', 'export_data_template_po_packing_list_vertical')->name('export_data_template_po_packing_list_vertical');
        Route::get('/show_datatable_upload_packing_list', 'show_datatable_upload_packing_list')->name('show_datatable_upload_packing_list');
        Route::post('/delete_upload_packing_list', 'delete_upload_packing_list')->name('delete_upload_packing_list');
        Route::post('/store', 'store')->name('store_upload_packing_list');
        Route::get('/getPoData', 'getPoData')->name('getPoData');
        Route::get('/show_detail_packing_list', 'show_detail_packing_list')->name('show_detail_packing_list');
        Route::get('/show_detail_packing_list_hapus', 'show_detail_packing_list_hapus')->name('show_detail_packing_list_hapus');
        Route::post('/hapus_packing_list', 'hapus_packing_list')->name('hapus_packing_list');
        Route::post('/tambah_packing_list', 'tambah_packing_list')->name('tambah_packing_list');
        Route::get('/show_datatable_upload_packing_list_tambah', 'show_datatable_upload_packing_list_tambah')->name('show_datatable_upload_packing_list_tambah');
    });

    // Packing List
    Route::controller(PackingReportController::class)->prefix("packing-packing-report")->middleware('packing')->group(function () {
        Route::get('/packing_rep_packing_line_sum', 'packing_rep_packing_line_sum')->name('packing_rep_packing_line_sum');
        Route::get('/packing_rep_packing_line_sum_range', 'packing_rep_packing_line_sum_range')->name('packing_rep_packing_line_sum_range');
        Route::get('/packing_rep_packing_line_sum_buyer', 'packing_rep_packing_line_sum_buyer')->name('packing_rep_packing_line_sum_buyer');
        Route::get('/packing_rep_packing_mutasi', 'packing_rep_packing_mutasi')->name('packing_rep_packing_mutasi');
        Route::get('/packing_rep_packing_mutasi_load', 'packing_rep_packing_mutasi_load')->name('packing_rep_packing_mutasi_load');
        Route::get('/packing_rep_packing_mutasi_wip', 'packing_rep_packing_mutasi_wip')->name('packing_rep_packing_mutasi_wip');
        Route::get('/export_excel_rep_packing_line_sum_range', 'export_excel_rep_packing_line_sum_range')->name('export_excel_rep_packing_line_sum_range');
        Route::get('/export_excel_rep_packing_line_sum_buyer', 'export_excel_rep_packing_line_sum_buyer')->name('export_excel_rep_packing_line_sum_buyer');
        Route::get('/export_excel_rep_packing_mutasi', 'export_excel_rep_packing_mutasi')->name('export_excel_rep_packing_mutasi');
        Route::get('/export_excel_rep_packing_mutasi_wip', 'export_excel_rep_packing_mutasi_wip')->name('export_excel_rep_packing_mutasi_wip');
    });

    // Finish Good
    // Dashboard Finish Good
    Route::controller(FinishGoodDashboardController::class)->middleware('finishgood')->group(function () {
        Route::get('/dashboard_finish_good', 'dashboard_finish_good')->name('dashboard_finish_good');
        Route::get('/get_data_dashboard_fg_ekspedisi', 'get_data_dashboard_fg_ekspedisi')->name('get_data_dashboard_fg_ekspedisi');
        Route::get('/show_tot_dash_fg_ekspedisi', 'show_tot_dash_fg_ekspedisi')->name('show_tot_dash_fg_ekspedisi');
        Route::get('/getws_dashboard_ekspedisi', 'getws_dashboard_ekspedisi')->name('getws_dashboard_ekspedisi');
        Route::get('/getpo_dashboard_ekspedisi', 'getpo_dashboard_ekspedisi')->name('getpo_dashboard_ekspedisi');
        Route::get('/get_detail_dashboard_ekspedisi', 'get_detail_dashboard_ekspedisi')->name('get_detail_dashboard_ekspedisi');
    });

    // Master Finish Good
    Route::controller(FinishGoodMasterLokasiController::class)->prefix("finish_good_master")->middleware('finishgood')->group(function () {
        Route::get('/', 'index')->name('finish_good_master_lokasi');
        Route::post('/store', 'store')->name('store_finish_good_master_lokasi');
        Route::get('/getdata_finish_good_master_lokasi', 'getdata_finish_good_master_lokasi')->name('getdata_finish_good_master_lokasi');
        Route::post('/edit_finish_good_master_lokasi', 'edit_finish_good_master_lokasi')->name('edit_finish_good_master_lokasi');
    });

    // Alokasi Karton
    Route::controller(FinishGoodAlokasiKartonController::class)->prefix("finish_good_alokasi_karton")->middleware('finishgood')->group(function () {
        Route::get('/', 'index')->name('finish_good_alokasi_karton');
        Route::get('/getdata_lokasi_alokasi', 'getdata_lokasi_alokasi')->name('getdata_lokasi_alokasi');
        Route::get('/getno_carton_alokasi', 'getno_carton_alokasi')->name('getno_carton_alokasi');
        Route::get('/show_preview_detail_alokasi', 'show_preview_detail_alokasi')->name('show_preview_detail_alokasi');
        Route::post('/insert_tmp_alokasi_karton', 'insert_tmp_alokasi_karton')->name('insert_tmp_alokasi_karton');
        Route::post('/alokasi_hapus_tmp', 'alokasi_hapus_tmp')->name('alokasi_hapus_tmp');
        Route::post('/delete_tmp_all_alokasi_karton', 'delete_tmp_all_alokasi_karton')->name('delete_tmp_all_alokasi_karton');
        Route::post('/store', 'store')->name('store_karton_alokasi');
        Route::get('/export_excel_fg_alokasi', 'export_excel_fg_alokasi')->name('export_excel_fg_alokasi');
    });

    // Penerimaan Finish Good
    Route::controller(FinishGoodPenerimaanController::class)->prefix("finish_good_penerimaan")->middleware('finishgood')->group(function () {
        Route::get('/', 'index')->name('finish_good_penerimaan');
        Route::get('/fg_in_getno_carton', 'fg_in_getno_carton')->name('fg_in_getno_carton');
        Route::get('/show_preview_fg_in', 'show_preview_fg_in')->name('show_preview_fg_in');
        Route::get('/create', 'create')->name('create_penerimaan_finish_good');
        Route::post('/store', 'store')->name('store-fg-in');
        Route::get('/export_excel_fg_in_list', 'export_excel_fg_in_list')->name('export_excel_fg_in_list');
        Route::get('/export_excel_fg_in_summary', 'export_excel_fg_in_summary')->name('export_excel_fg_in_summary');
    });

    // Pengeluaran Finish Good
    Route::controller(FinishGoodPengeluaranController::class)->prefix("finish_good_pengeluaran")->middleware('finishgood')->group(function () {
        Route::get('/', 'index')->name('finish_good_pengeluaran');
        Route::post('/store', 'store')->name('store-fg-out');
        Route::get('/create', 'create')->name('create_pengeluaran_finish_good');
        Route::get('/getpo_fg_out', 'getpo_fg_out')->name('getpo_fg_out');
        Route::get('/getcarton_notes_fg_out', 'getcarton_notes_fg_out')->name('getcarton_notes_fg_out');
        Route::get('/show_number_carton_fg_out', 'show_number_carton_fg_out')->name('show_number_carton_fg_out');
        Route::post('/insert_tmp_fg_out', 'insert_tmp_fg_out')->name('insert_tmp_fg_out');
        Route::get('/show_det_karton_fg_out', 'show_det_karton_fg_out')->name('show_det_karton_fg_out');
        Route::get('/show_summary_karton_fg_out', 'show_summary_karton_fg_out')->name('show_summary_karton_fg_out');
        Route::get('/show_delete_karton_fg_out', 'show_delete_karton_fg_out')->name('show_delete_karton_fg_out');
        Route::post('/delete_karton_fg_out', 'delete_karton_fg_out')->name('delete_karton_fg_out');
        Route::post('/clear_tmp_fg_out', 'clear_tmp_fg_out')->name('clear_tmp_fg_out');
        Route::get('/edit_fg_out/{id?}', 'edit_fg_out')->name('edit_fg_out');
        Route::get('/show_det_karton_fg_out_terinput', 'show_det_karton_fg_out_terinput')->name('show_det_karton_fg_out_terinput');
        Route::get('/show_summary_karton_fg_out_terinput', 'show_summary_karton_fg_out_terinput')->name('show_summary_karton_fg_out_terinput');
        Route::post('/edit_store_fg_out', 'edit_store_fg_out')->name('edit-store-fg-out');
        Route::get('/export_excel_fg_out_list', 'export_excel_fg_out_list')->name('export_excel_fg_out_list');
        Route::get('/export_excel_fg_out_summary', 'export_excel_fg_out_summary')->name('export_excel_fg_out_summary');
    });

    // Retur Finish Good
    Route::controller(FinishGoodReturController::class)->prefix("finish_good_retur")->middleware('finishgood')->group(function () {
        Route::get('/', 'index')->name('finish_good_retur');
        Route::post('/store', 'store')->name('store-fg-retur');
        Route::get('/create', 'create')->name('create_retur_finish_good');
        Route::get('/getpo_fg_retur', 'getpo_fg_retur')->name('getpo_fg_retur');
        Route::get('/getcarton_notes_fg_retur', 'getcarton_notes_fg_retur')->name('getcarton_notes_fg_retur');
        Route::get('/show_number_carton_fg_retur', 'show_number_carton_fg_retur')->name('show_number_carton_fg_retur');
        Route::post('/insert_tmp_fg_retur', 'insert_tmp_fg_retur')->name('insert_tmp_fg_retur');
        Route::get('/show_det_karton_fg_retur', 'show_det_karton_fg_retur')->name('show_det_karton_fg_retur');
        Route::get('/show_summary_karton_fg_retur', 'show_summary_karton_fg_retur')->name('show_summary_karton_fg_retur');
        Route::get('/show_delete_karton_fg_retur', 'show_delete_karton_fg_retur')->name('show_delete_karton_fg_retur');
        Route::post('/delete_karton_fg_retur', 'delete_karton_fg_retur')->name('delete_karton_fg_retur');
        Route::post('/clear_tmp_fg_retur', 'clear_tmp_fg_retur')->name('clear_tmp_fg_retur');
        Route::get('/export_excel_fg_retur_list', 'export_excel_fg_retur_list')->name('export_excel_fg_retur_list');
        Route::get('/export_excel_fg_retur_summary', 'export_excel_fg_retur_summary')->name('export_excel_fg_retur_summary');
    });

    // Report Doc
    // Laporan BC
    Route::controller(ReportDocController::class)->prefix("report_doc_laporan")->middleware('bc')->group(function () {
        Route::get('/report_doc_laporan_wip', 'report_doc_laporan_wip')->name('report-doc-laporan-wip');
        Route::get('/show_report_doc_lap_wip', 'show_report_doc_lap_wip')->name('show_report_doc_lap_wip');
        Route::get('/export_excel_doc_lap_wip', 'export_excel_doc_lap_wip')->name('export_excel_doc_lap_wip');
    });

    // PPIC
    // Dashboard
    Route::controller(PPICDashboardController::class)->middleware('packing')->group(function () {
        Route::get('/dashboard_ppic', 'dashboard_ppic')->name('dashboard-ppic');
        Route::get('/show_tot_dash_ppic', 'show_tot_dash_ppic')->name('show_tot_dash_ppic');
        Route::get('/get_data_dash_ppic', 'get_data_dash_ppic')->name('get_data_dash_ppic');
        Route::get('/show_data_dash_ship_hr_ini', 'show_data_dash_ship_hr_ini')->name('show_data_dash_ship_hr_ini');
    });

    // Master
    Route::controller(PPIC_MasterSOController::class)->prefix("master-so-ppic")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('master-so');
        Route::post('/import-excel-so', 'import_excel_so')->name('import-excel-so');
        Route::get('/show_tmp_ppic_so', 'show_tmp_ppic_so')->name('show_tmp_ppic_so');
        Route::get('/data_cek_double_tmp_ppic_so', 'data_cek_double_tmp_ppic_so')->name('data_cek_double_tmp_ppic_so');
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
        Route::get('/get_ws_header_ppic', 'get_ws_header_ppic')->name('get_ws_header_ppic');
        Route::get('/get_style_header_ppic', 'get_style_header_ppic')->name('get_style_header_ppic');
        Route::get('/get_ws_style_ppic', 'get_ws_style_ppic')->name('get_ws_style_ppic');
    });

    // PPIC Laporan Tracking
    Route::controller(PPIC_LaporanTrackingController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('ppic-laporan-tracking');
        Route::get('/show_lap_tracking_ppic', 'show_lap_tracking_ppic')->name('show_lap_tracking_ppic');
        Route::get('/export_excel_tracking', 'export_excel_tracking')->name('export_excel_tracking');
        Route::get('/ppic_monitoring_order', 'ppic_monitoring_order')->name('ppic_monitoring_order');
        Route::get('/get_ppic_monitoring_order_reff', 'get_ppic_monitoring_order_reff')->name('get_ppic_monitoring_order_reff');
        Route::get('/get_ppic_monitoring_order_ws', 'get_ppic_monitoring_order_ws')->name('get_ppic_monitoring_order_ws');
        Route::get('/get_ppic_monitoring_order_color', 'get_ppic_monitoring_order_color')->name('get_ppic_monitoring_order_color');
        Route::get('/get_ppic_monitoring_order_size', 'get_ppic_monitoring_order_size')->name('get_ppic_monitoring_order_size');
        Route::get('/show_lap_monitoring_order', 'show_lap_monitoring_order')->name('show_lap_monitoring_order');
        Route::post('/export_excel_monitoring_order', 'export_excel_monitoring_order')->name('export_excel_monitoring_order');
    });

    // PPIC Monitoring Order
    Route::controller(PPIC_MonitoringMaterialController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/ppic_monitoring_material', 'ppic_monitoring_material')->name('ppic_monitoring_material');
        Route::get('/show_lap_monitoring_material_f_det', 'show_lap_monitoring_material_f_det')->name('show_lap_monitoring_material_f_det');
    });

    // PPIC Monitoring Order Detail
    Route::controller(PPIC_MonitoringMaterialDetController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/ppic_monitoring_material_det', 'ppic_monitoring_material_det')->name('ppic_monitoring_material_det');
        Route::get('/show_lap_monitoring_material_f_detail', 'show_lap_monitoring_material_f_detail')->name('show_lap_monitoring_material_f_detail');
    });

    // PPIC Monitoring Order Summary
    Route::controller(PPIC_MonitoringMaterialSumController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/ppic_monitoring_material_sum', 'ppic_monitoring_material_sum')->name('ppic_monitoring_material_sum');
        Route::get('/get_ppic_monitoring_material_sum_style', 'get_ppic_monitoring_material_sum_style')->name('get_ppic_monitoring_material_sum_style');
        Route::get('/show_lap_monitoring_material_f_sum', 'show_lap_monitoring_material_f_sum')->name('show_lap_monitoring_material_f_sum');
    });

    // Barcode Packing Controller
    Route::controller(BarcodePackingController::class)->prefix("barcode-packing")->middleware('role:ppic,packing')->group(function () {
        Route::get('/index', 'index')->name('barcode-packing');
        Route::get('/get-barcode', 'getBarcode')->name('get-barcode-packing');
        Route::get('/generate-barcode/{barcode?}', 'generateBarcode')->name('generate-barcode-packing');
        Route::post('/download-barcode', 'downloadBarcode')->name('download-barcode-packing');
    });

    // Tools Adjustment PPIC
    Route::controller(PPIC_tools_adjustmentController::class)->prefix("laporan-ppic")->middleware('packing')->group(function () {
        Route::get('/ppic_tools_adj_mut_output', 'ppic_tools_adj_mut_output')->name('ppic_tools_adj_mut_output');
        Route::get('/contoh_upload_adj_mut_output', 'contoh_upload_adj_mut_output')->name('contoh_upload_adj_mut_output');
        Route::post('/upload_adj_mut_output', 'upload_adj_mut_output')->name('upload_adj_mut_output');
        Route::get('/show_datatable_upload_adj_mut_output', 'show_datatable_upload_adj_mut_output')->name('show_datatable_upload_adj_mut_output');
        Route::post('/undo_upload_adj_mut_output', 'undo_upload_adj_mut_output')->name('undo_upload_adj_mut_output');
        Route::post('/store_upload_adj_mut_output', 'store_upload_adj_mut_output')->name('store_upload_adj_mut_output');
        Route::post('/delete_upload_adj_mut_output', 'delete_upload_adj_mut_output')->name('delete_upload_adj_mut_output');
    });

    // Report Hourly Output
    Route::controller(ReportHourlyController::class)->prefix("laporan-report-hourly")->middleware('packing')->group(function () {
        Route::get('/', 'index')->name('report-hourly');
        // Route::get('/show_lap_tracking_ppic', 'show_lap_tracking_ppic')->name('show_lap_tracking_ppic');
        // Route::get('/export_excel_tracking', 'export_excel_tracking')->name('export_excel_tracking');
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
        Route::get('/export_excel_data_bahan_bakar', 'export_excel_data_bahan_bakar')->name('export_excel_data_bahan_bakar');

        // Route::post('/store', 'store')->name('store-packing-packing-in');
    });

    // Approval
    Route::controller(GAApprovalBahanBakarController::class)->prefix("ga-approval-bahan-bakar")->middleware('ga')->group(function () {
        Route::get('/', 'index')->name('approval-bahan-bakar');
        Route::post('/store', 'store')->name('store-approval-bahan-bakar');
    });

    Route::controller(DashboardController::class)->prefix("dashboard-chart")->middleware('role:cutting')->group(function () {
        Route::get('/', 'cuttingMeja')->name('dashboard-chart');
        Route::get('/{mejaId?}', 'cuttingMejaDetail')->name('dashboard-chart-detail');

        // TEST TRIGGER SOCKET.IO
        Route::get('/trigger/all/{date?}', 'cutting_chart_trigger_all')->name('cutting-chart-trigger-all');
        Route::get('/trigger/{date?}/{mejaId?}', 'cutting_trigger_chart_by_mejaid')->name('cutting-trigger-chart-by-mejaid');
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

    // Marketing
    // Dashboard
    Route::controller(MarketingDashboardController::class)->middleware('marketing')->group(function () {
        Route::get('/dashboard_marketing', 'dashboard_marketing')->name('dashboard-marketing');
        Route::get('/get_data_dash_marketing', 'get_data_dash_marketing')->name('get_data_dash_marketing');
        Route::get('/get_data_dash_marketing_top_buyer', 'get_data_dash_marketing_top_buyer')->name('get_data_dash_marketing_top_buyer');
    });

    // Master
    Route::controller(Marketing_CostingController::class)->prefix("master-costing")->middleware('marketing')->group(function () {
        Route::get('/', 'index')->name('master-costing');
        Route::get('/getprod_item_costing', 'getprod_item_costing')->name('getprod_item_costing');
        Route::post('/store_master_costing_production', 'store_master_costing_production')->name('store_master_costing_production');
        Route::get('/edit_costing/{id?}', 'edit_costing')->name('edit_costing');
        Route::post('/update_header_master_costing', 'update_header_master_costing')->name('update_header_master_costing');
        Route::get('/get_jns_costing_material', 'get_jns_costing_material')->name('get_jns_costing_material');
        Route::get('/get_material_costing', 'get_material_costing')->name('get_material_costing');
    });



    // QC Inspect Kain
    // Dashboard
    Route::controller(QCInspectDashboardController::class)->middleware('warehouse')->group(function () {
        Route::get('/dashboard_qc_inspect', 'dashboard_qc_inspect')->name('dashboard-qc-inspect');
    });

    // Proses Packing List
    Route::controller(QCInspectProsesPackingListController::class)->prefix("proses-packing-list")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_proses_packing_list');
        Route::get('/qc_inspect_proses_packing_list_det/{id_lok_in_material?}', 'qc_inspect_proses_packing_list_det')->name('qc_inspect_proses_packing_list_det');
        Route::get('/show_calculate_qc_inspect', 'show_calculate_qc_inspect')->name('show_calculate_qc_inspect');
    });
});

// Dashboard
Route::get('/dashboard-track', [DashboardController::class, 'track'])->middleware('auth')->name('dashboard-track');
Route::get('/dashboard-marker', [DashboardController::class, 'marker'])->middleware('auth')->name('dashboard-marker');
Route::get('/dashboard-wip', [DashboardWipLineController::class, 'index'])->middleware('auth')->name('dashboard-wip');
Route::get('/dashboard-wip/wip-line/{id?}', [DashboardWipLineController::class, 'show_wip_line'])->name('show_wip_line');
// Factory
Route::get('/dashboard-wip/factory-performance/{year?}/{month?}', [DashboardWipLineController::class, 'factoryPerformance'])->name('dashboard-factory-performance');
// Chief
Route::get('/dashboard-wip/chief-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'chiefSewing'])->name('dashboard-chief-sewing');
Route::get('/dashboard-wip/chief-sewing-data', [DashboardWipLineController::class, 'chiefSewingData'])->name('dashboard-chief-sewing-data');
// Chief Range
Route::get('/dashboard-wip/chief-sewing-range/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'chiefSewingRange'])->middleware('auth')->name('dashboard-chief-sewing-range');
Route::get('/dashboard-wip/chief-sewing-range-data', [DashboardWipLineController::class, 'chiefSewingRangeData'])->middleware('auth')->name('dashboard-chief-sewing-range-data');
Route::post('/dashboard-wip/chief-sewing-range-data-export', [DashboardWipLineController::class, 'chiefSewingRangeDataExport'])->middleware('auth')->name('dashboard-chief-sewing-range-data-export');
// Leader
Route::get('/dashboard-wip/leader-sewing/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'leaderSewing'])->middleware('auth')->name('dashboard-leader-sewing');
Route::get('/dashboard-wip/leader-sewing-data', [DashboardWipLineController::class, 'leaderSewingData'])->middleware('auth')->name('dashboard-leader-sewing-data');
Route::get('/dashboard-wip/leader-sewing-filter', [DashboardWipLineController::class, 'leaderSewingFilter'])->middleware('auth')->name('dashboard-leader-sewing-filter');
Route::post('/dashboard-wip/leader-sewing-range-data-export', [DashboardWipLineController::class, 'leaderSewingRangeDataExport'])->middleware('auth')->name('dashboard-leader-sewing-range-data-export');
// Line Support
Route::get('/dashboard-wip/support-line-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'supportLineSewing'])->name('dashboard-support-line-sewing');
Route::get('/dashboard-wip/support-line-sewing-data', [DashboardWipLineController::class, 'supportLineSewingData'])->name('dashboard-support-line-sewing-data');
// Factory
Route::get('/dashboard-wip/factory-daily-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'factoryDailyPerformance'])->name('dashboard-factory-daily-sewing');
Route::get('/dashboard-wip/factory-daily-sewing-data', [DashboardWipLineController::class, 'factoryDailyPerformanceData'])->name('dashboard-factory-daily-sewing-data');
// Chief Leader
Route::get('/dashboard-wip/chief-leader-sewing/{dateFrom?}/{dateTo?}', [DashboardWipLineController::class, 'chiefLeaderSewing'])->middleware('auth')->name('dashboard-chief-leader-sewing');
Route::get('/dashboard-wip/chief-leader-sewing-data', [DashboardWipLineController::class, 'chiefLeaderSewingData'])->middleware('auth')->name('dashboard-chief-leader-sewing-data');
Route::post('/dashboard-wip/chief-leader-sewing-range-data-export', [DashboardWipLineController::class, 'chiefLeaderSewingRangeDataExport'])->middleware('auth')->name('dashboard-chief-leader-sewing-range-data-export');
// Chief Top
Route::get('/dashboard-wip/top-chief-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'topChiefSewing'])->name('dashboard-top-chief-sewing');
Route::get('/dashboard-wip/top-chief-sewing-data', [DashboardWipLineController::class, 'topChiefSewingData'])->name('dashboard-top-chief-sewing-data');
// Leader Top
Route::get('/dashboard-wip/top-leader-sewing/{year?}/{month?}', [DashboardWipLineController::class, 'topLeaderSewing'])->name('dashboard-top-leader-sewing');
Route::get('/dashboard-wip/top-leader-sewing-data', [DashboardWipLineController::class, 'topLeaderSewingData'])->name('dashboard-top-leader-sewing-data');

Route::get('/marker-qty', [DashboardController::class, 'markerQty'])->middleware('auth')->name('marker-qty');
Route::get('/dashboard-cutting', [DashboardController::class, 'cutting'])->middleware('auth')->name('dashboard-cutting');
Route::get('/dashboard-cutting-chart', [DashboardController::class, 'cutting_chart'])->middleware('auth')->name('dashboard-cutting-chart');
Route::get('/meja-dashboard-cutting', [DashboardController::class, 'get_cutting_chart_meja'])->middleware('auth')->name('meja-dashboard-cutting');
Route::get('/cutting-chart-by-mejaid', [DashboardController::class, 'cutting_chart_by_mejaid'])->middleware('auth')->name('cutting-chart-by-mejaid');
Route::get('/cutting-qty', [DashboardController::class, 'cuttingQty'])->middleware('auth')->name('cutting-qty');
Route::get('/cutting-dashboard-list', [DashboardController::class, 'cuttingDashboardList'])->middleware('auth')->name('cutting-dashboard-list');
Route::get('/cutting-form-list', [DashboardController::class, 'cuttingFormList'])->middleware('auth')->name('cutting-form-list');
Route::get('/cutting-form-chart', [DashboardController::class, 'cuttingFormChart'])->middleware('auth')->name('cutting-form-chart');
Route::get('/cutting-worksheet-list', [DashboardController::class, 'cuttingWorksheetList'])->middleware('auth')->name('cutting-worksheet-list');
Route::get('/cutting-worksheet-total', [DashboardController::class, 'cuttingWorksheetTotal'])->middleware('auth')->name('cutting-worksheet-total');
Route::get('/cutting-output-list', [DashboardController::class, 'cuttingOutputList'])->middleware('auth')->name('cutting-output-list');
Route::get('/cutting-output-list-all', [DashboardController::class, 'cuttingOutputListAll'])->middleware('auth')->name('cutting-output-list-all');
Route::get('/cutting-output-list-panels', [DashboardController::class, 'cuttingOutputListPanels'])->middleware('auth')->name('cutting-output-list-panels');
Route::get('/cutting-output-list-data', [DashboardController::class, 'cuttingOutputListData'])->middleware('auth')->name('cutting-output-list-data');
Route::get('/cutting-stock-list-data', [DashboardController::class, 'cuttingStockListData'])->middleware('auth')->name('cutting-stock-list-data');

Route::get('/dashboard-stocker', [DashboardController::class, 'stocker'])->middleware('auth')->name('dashboard-stocker');
Route::get('/dashboard-stocker/show/{actCostingId?}', [DashboardController::class, 'showStocker'])->middleware('auth')->name('dashboard-stocker-show');

Route::get('/dashboard-dc', [DashboardController::class, 'dc'])->middleware('auth')->name('dashboard-dc');
Route::get('/dc-qty', [DashboardController::class, 'dcQty'])->middleware('auth')->name('dc-qty');
Route::get('/dashboard-sewing-eff', [DashboardController::class, 'sewingEff'])->middleware('auth')->name('dashboard-sewing-eff');
Route::get('/sewing-summary', [DashboardController::class, 'sewingSummary'])->middleware('auth')->name('dashboard-sewing-sum');
Route::get('/sewing-output-data', [DashboardController::class, 'sewingOutputData'])->middleware('auth')->name('dashboard-sewing-output');
Route::get('/dashboard-manage-user', [DashboardController::class, 'manageUser'])->middleware('auth')->name('dashboard-manage-user');

// Accounting
Route::controller(AccountingController::class)->prefix("accounting")->middleware('role:accounting')->group(function () {
    // get worksheet
    Route::get('/', 'index')->name('accounting');
    Route::get('/update-data-ceisa', 'UpdateData')->name('update-data-ceisa');
    Route::get('/create', 'create')->name('create-update-ceisa');
    Route::get('/get-data-ceisa', 'getData')->name('get-data-ceisa');
    Route::post('/store', 'store')->name('store-update-ceisa');
    Route::get('/cancel-keterangan-ceisa', 'CancelDataCeisa')->name('cancel-keterangan-ceisa');
    Route::get('/edit-keterangan-ceisa', 'EditDataCeisa')->name('edit-keterangan-ceisa');
    Route::get('/report-rekonsiliasi-ceisa', 'ReportRekonsiliasi')->name('report-rekonsiliasi-ceisa');
    Route::get('/export-rekonsiliasi-ceisa', 'ExportReportRekonsiliasi')->name('export-rekonsiliasi-ceisa');
    Route::get('/report-ceisa-detail', 'ReportCeisaDetail')->name('report-ceisa-detail');
    Route::get('/export-ceisa-detail', 'ExportReportCeisaDetail')->name('export-ceisa-detail');
});

// Route::get('/dashboard-chart', function () {
//    return view('cutting.chart.dashboard-chart');
// });
Route::get('/trigger', function () {
    event(new TestEvent('This is realtime data'));
    return response()->json(['status' => 'Event sent testing']);
});

// Dashboard
// Route::get('/dashboard-marker', function () {
//     return view('dashboard', ['page' => 'dashboard-marker']);
// })->middleware('auth')->name('dashboard-marker');

// Route::get('/dashboard-cutting', function () {
//     return view('dashboard', ['page' => 'dashboard-cutting']);
// })->middleware('auth')->name('dashboard-cutting');

// Route::get('/dashboard-stocker', function () {
//     return view('dashboard', ['page' => 'dashboard-stocker']);
// })->middleware('auth')->name('dashboard-stocker');

//warehouse
// Route::get('/dashboard-warehouse', function () {
//     return view('dashboard-fabric', ['page' => 'dashboard-warehouse']);
// })->middleware('auth')->name('dashboard-warehouse');

//fg stock
Route::get('/dashboard-fg-stock', function () {
    return view('dashboard', ['page' => 'dashboard-fg-stock']);
})->middleware('auth')->name('dashboard-fg-stock');

//Finish Good
Route::get('/dashboard-finish-good', function () {
    return view('dashboard', ['page' => 'dashboard-finish-good']);
})->middleware('auth')->name('dashboard-finish-good');

//Report Doc
Route::get('/dashboard-report-doc', function () {
    return view('dashboard', ['page' => 'dashboard-report-doc']);
})->middleware('auth')->name('dashboard-report-doc');

//GA
Route::get('/dashboard-ga', function () {
    return view('dashboard', ['page' => 'dashboard-ga']);
})->middleware('auth')->name('dashboard-ga');

Route::get('/dashboard-mut-karyawan', function () {
    return view('dashboard', ['page' => 'dashboard-mut-karyawan']);
})->middleware('auth')->name('dashboard-mut-karyawan');

Route::get('/dashboard-mut-mesin', function () {
    return view('dashboard-mesin', ['page' => 'dashboard-mut-mesin']);
})->middleware('auth')->name('dashboard-mut-mesin');

// Sym Link
Route::get('/symlink', function () {
    Artisan::call('storage:link');
});

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

require __DIR__ . '/qc_inspect.php';
