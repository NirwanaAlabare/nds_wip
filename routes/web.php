<?php

use App\Events\TestEvent;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\BarcodePackingController;
use App\Http\Controllers\DashboardFabricController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Exim\ExportImportController;
use App\Http\Controllers\FGStokBPBController;
use App\Http\Controllers\FGStokBPPBController;
use App\Http\Controllers\FGStokLaporanController;
use App\Http\Controllers\FGStokMasterController;
use App\Http\Controllers\FGStokMutasiController;
use App\Http\Controllers\FinishGoodAlokasiKartonController;
use App\Http\Controllers\FinishGoodDashboardController;
use App\Http\Controllers\FinishGoodMasterLokasiController;
use App\Http\Controllers\FinishGoodPenerimaanController;
use App\Http\Controllers\FinishGoodPengeluaranController;
use App\Http\Controllers\FinishGoodReturController;
use App\Http\Controllers\GAApprovalBahanBakarController;
use App\Http\Controllers\GAPengajuanBahanBakarController;
use App\Http\Controllers\IE_Laporan_Controller;
use App\Http\Controllers\IE_Proses_OB_Controller;
use App\Http\Controllers\IEDashboardController;
use App\Http\Controllers\IEMasterPartProcessController;
use App\Http\Controllers\IEMasterProcessController;
use App\Http\Controllers\InMaterialController;
use App\Http\Controllers\KonfPemasukanController;
use App\Http\Controllers\KonfPengeluaranController;
use App\Http\Controllers\LapDetPemasukanController;
use App\Http\Controllers\LapDetPemasukanRollController;
use App\Http\Controllers\LapDetPengeluaranController;
use App\Http\Controllers\LapDetPengeluaranRollController;
use App\Http\Controllers\LapMutasiBarcodeController;
use App\Http\Controllers\LapMutasiDetailController;
use App\Http\Controllers\LapMutasiGlobalController;
use App\Http\Controllers\MaintainBpbController;
use App\Http\Controllers\Marketing_AdditionalBomController;
use App\Http\Controllers\Marketing_BomController;
use App\Http\Controllers\Marketing_CostingController;
use App\Http\Controllers\Marketing_SOController;
use App\Http\Controllers\MarketingDashboardController;
use App\Http\Controllers\MasterLokasiController;
use App\Http\Controllers\MgtReportDailyCostController;
use App\Http\Controllers\MgtReportDailyEarnBuyerController;
use App\Http\Controllers\MgtReportDashboardController;
use App\Http\Controllers\MgtReportEarningController;
use App\Http\Controllers\MgtReportProfitLineController;
use App\Http\Controllers\MgtReportProsesController;
use App\Http\Controllers\MgtReportSumBuyerController;
use App\Http\Controllers\MgtReportSumFullEarnController;
use App\Http\Controllers\MgtReportSumProdEarnController;
use App\Http\Controllers\MutasiMesinController;
use App\Http\Controllers\MutasiMesinLaporanController;
use App\Http\Controllers\MutasiMesinMasterController;
use App\Http\Controllers\MutasiMesinStockOpnameController;
use App\Http\Controllers\MutLokasiController;
use App\Http\Controllers\OutMaterialController;
use App\Http\Controllers\PackingDashboardController;
use App\Http\Controllers\PackingLineController;
use App\Http\Controllers\PackingMasterKartonController;
use App\Http\Controllers\PackingNeedleCheckController;
use App\Http\Controllers\PackingPackingInController;
use App\Http\Controllers\PackingPackingListController;
use App\Http\Controllers\PackingPackingOutController;
use App\Http\Controllers\PackingReportController;
use App\Http\Controllers\PackingSubcontController;
use App\Http\Controllers\PackingTransferGarmentController;
use App\Http\Controllers\PPIC_LaporanTrackingController;
use App\Http\Controllers\PPIC_MasterSOController;
use App\Http\Controllers\PPIC_MonitoringMaterialController;
use App\Http\Controllers\PPIC_MonitoringMaterialDetController;
use App\Http\Controllers\PPIC_MonitoringMaterialSumController;
use App\Http\Controllers\PPIC_tools_adjustmentController;
use App\Http\Controllers\PPICDashboardController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\PurchasingDashboardController;
use App\Http\Controllers\QCInspectDashboardController;
use App\Http\Controllers\QCInspectLaporanController;
use App\Http\Controllers\QCInspectMasterController;
use App\Http\Controllers\QCInspectPrintBintexShadeBandController;
use App\Http\Controllers\QCInspectProsesFabricRelaxationController;
use App\Http\Controllers\QCInspectProsesFormInspectController;
use App\Http\Controllers\QCInspectProsesPackingListController;
use App\Http\Controllers\QCInspectShadeBandController;
use App\Http\Controllers\QcPassController;
use App\Http\Controllers\ReportDocController;
use App\Http\Controllers\ReqMaterialController;
use App\Http\Controllers\ReturInMaterialController;
use App\Http\Controllers\ReturMaterialController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\TransferBpbController;
use App\Http\Controllers\TransferMemoController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\WhsSoljer\PenerimaanGudangInputanAccesoriesController;
use App\Http\Controllers\WhsSoljer\PenerimaanGudangInputanController;
use App\Http\Controllers\WhsSoljer\PenerimaanGudangInputanFgController;
use App\Http\Controllers\WhsSoljer\PengeluaranGudangInputanAccesoriesController;
use App\Http\Controllers\WhsSoljer\PengeluaranGudangInputanController;
use App\Http\Controllers\WhsSoljer\PengeluaranGudangInputanFgController;
use Illuminate\Support\Facades\Route;

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

// Authentication
Auth::routes(['register' => false]);

// Home
Route::get('/', [App\Http\Controllers\General\HomeController::class, 'index']);

Route::get('/home', [App\Http\Controllers\General\HomeController::class, 'index'])->name('home');

// Dashboard
require base_path('routes/dashboard.php');

// User
require base_path('routes/user.php');

// General
require base_path('routes/general.php');

// Part
require base_path('routes/part.php');

// Marker
require base_path('routes/marker.php');

// Cutting
require base_path('routes/cutting.php');

// Cutting
require base_path('routes/stocker.php');

// DC
require base_path('routes/dc.php');

// Sewing
require base_path('routes/sewing.php');

// QC Inspect
require base_path('routes/qcinspect.php');

// SB WIP SUMMARY
require base_path('routes/sbwipsummary.php');

Route::middleware('auth')->group(function () {
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
        Route::post('/print-lokasi-all', 'printLokasiAll')->name('print-lokasi-all');
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
        Route::get('/list-data-stok', 'ListDataStok')->name('list-data-stok');
        Route::get('/cancel-data-opname', 'cancelopname')->name('cancel-data-opname');
        Route::get('/datarak', 'datarak')->name('data-rak');
        Route::get('/get-detail-opname', 'GetdetailOpname')->name('get-detail-opname');
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
        Route::post('/simpan-barcode-force', 'SimpanBarcodeForce')->name('simpan-barcode-force');
        Route::get('/delete-barcode-saldo-opname', 'deletesaldoso')->name('delete-barcode-saldo-opname');
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
        Route::get('/cancel-retur-material', 'CancelReturMaterial')->name('cancel-retur-material');
        Route::post('/delete-detail-barcode-rak', 'DeleteDataBarcode')->name('delete-detail-barcode-rak');
        Route::get('/export-format-upload-roll', 'ExportUploadRoll')->name('export-format-upload-roll');
        Route::post('/update-all-barcode-rak', 'updateAllLokasi')->name('update-all-barcode-rak');
    });

    //permintaan
    Route::controller(ReqMaterialController::class)->prefix("req-material")->middleware('req-material')->group(function () {
        Route::get('/', 'index')->name('req-material');
        Route::get('/create', 'create')->name('create-reqmaterial');
        Route::get('/get-ws-req', 'getWSReq')->name('get-ws-req');
        Route::get('/get-ws-act', 'getWSact')->name('get-ws-act');
        Route::get('/show-detail', 'showdetail')->name('get-detail-req');
        Route::get('/sum-detail', 'sumdetail')->name('get-sum-req');
        Route::get('/get-style-actual', 'getStyleAct')->name('get-style-actual');
        Route::post('/store', 'store')->name('store-reqmaterial-fabric');
        Route::post('/print-pdf-reqmaterial/{bppbno?}', 'pdfreqmaterial')->name('print-pdf-reqmaterial');
        Route::get('/edit-request/{id?}', 'editrequest')->name('edit-reqmaterial');
        Route::get('/update-req-fabric', 'updateReq')->name('update-reqmaterial-fabric');
        Route::get('/cancel-request', 'CancelRequest')->name('cancel-request');
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
        Route::post('/approve-outmaterial', 'approveOutMaterial')->name('approve-outmaterial');
        Route::post('/print-pdf-outmaterial/{id?}', 'pdfoutmaterial')->name('print-pdf-outmaterial');
        Route::get('/delete-scan-temp', 'deletescantemp')->name('delete-scan-temp');
        Route::get('/delete-all-temp', 'deletealltemp')->name('delete-all-temp');
        Route::get('/edit-out-material/{id?}', 'editoutmaterial')->name('edit-out-material');
        Route::get('/update-out-material', 'updateOut')->name('update-outmaterial-fabric');
        Route::get('/show-detail-bppb', 'showdetailBppb')->name('get-detail-bppb');
        Route::post('/update-detail-barcode-bppb', 'updateBarcodeBppb')->name('update-detail-barcode-bppb');
        Route::post('/delete-detail-barcode-bppb', 'DeleteDataBarcodeBppb')->name('delete-detail-barcode-bppb');
        Route::post('/save-out-scan-edit', 'saveoutscanEdit')->name('save-out-scan-edit');
        Route::post('/save-out-manual-edit', 'saveoutmanualEdit')->name('save-out-manual-edit');
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
        Route::get('/get-barcode', 'getbarcodemutasi')->name('get-data-barcodemutasi');
        Route::get('/save-barcode', 'simpanbarcodemutasi')->name('simpan-scan-barcode-mutasi');
        Route::get('/list-scan-barcode', 'listscanbarcodemut')->name('list-scan-barcode-mutasi');
        Route::get('/delete-mut-temp', 'deletemuttemp')->name('delete-mut-temp');
        Route::get('/delete-mut-temp-all', 'deletemuttempall')->name('delete-mut-temp-all');
        Route::get('/update_lokasi-mut-temp', 'updatelokasimuttemp')->name('update_lokasi-mut-temp');
        Route::post('/store_new', 'store_new')->name('save-mutasi-rak-fabric');
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
        Route::post('/save-ro-manual', 'saveromanual')->name('save-ro-manual');
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
        Route::post('/copy_saldo_mutasi_barcode', 'CopySaldo')->name('copy-saldo-mutasi-barcode');
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

    //transfer memo
    Route::controller(TransferMemoController::class)->prefix("transfer-memo")->middleware('marketing')->group(function () {
        Route::get('/approve-transfer-memo', 'ApproveTransferMemo')->name('approve-transfer-memo');
        Route::get('/get-detail-transfer-memo/{id?}', 'DetailTransferMemo')->name('get-detail-transfer-memo');
        Route::post('/update-transfer-memo-approve', 'UpdateTransferMemoApprove')->name('update-transfer-memo-approve');
        Route::post('/update-transfer-memo-cancel', 'UpdateTransferMemoCancel')->name('update-transfer-memo-cancel');
        Route::get('/', 'index')->name('transfer-memo-to-exim');
        Route::get('/create', 'create')->name('create-transfer-memo');
        Route::post('/store', 'store')->name('save-transfer-memo');
        Route::get('/cancel-transfer-memo', 'canceltransfer')->name('cancel-transfer-memo');
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

    Route::controller(PackingLineController::class)->prefix("packing-line")->middleware('packing')->group(function () {
        Route::get('/track-packing-output', 'trackPackingOutput')->name('track-packing-output');
        Route::post('/track-packing-output/export', 'exportPackingOutput')->name('export-packing-output');
    });

    Route::controller(PackingSubcontController::class)->prefix("packing-subcont")->middleware('role:sewing,packing')->group(function () {
        Route::get('/packing-out-subcont', 'index')->name('sewing-out-subcont');
        Route::get('/create-packing-out-subcont', 'create')->name('create-sewing-out-subcont');
        Route::get('/get-detail-item-subcont', 'getDetailList')->name('get-detail-item-subcont');
        Route::get('/show-detail-so-subcont', 'showdetailitem')->name('show-detail-so-subcont');
        Route::post('/save-out-detail-temp', 'SaveOutDetailTemp')->name('save-out-detail-temp');
        Route::get('/delete-out-detail-temp', 'DeleteOutDetailTemp')->name('delete-out-detail-temp');
        Route::post('/store', 'store')->name('store-sewing-out-subcont');
        Route::get('/get-detail-packing-out/{id?}', 'DetailPackingOut')->name('get-detail-sewing-out');
        Route::get('/export-pl-packing-out/{id?}', 'PLPackingOut')->name('export-pl-sewing-out');

        Route::get('/approve-packing-out-subcont', 'ApprovePackingOutSubcont')->name('approve-sewing-out-subcont');
        Route::get('/save-approve-packing-out', 'SaveApprovePackingOut')->name('save-approve-sewing-out');

        Route::get('/report-packing-out-subcont', 'ReportOutSubcont')->name('report-sewing-out-subcont');
        Route::get('/export-excel-packing-subcont-out', 'ExportOutSubcont')->name('export-excel-sewing-subcont-out');

        Route::get('/packing-in-subcont', 'indexIN')->name('sewing-in-subcont');
        Route::get('/create-packing-in-subcont', 'createIN')->name('create-sewing-in-subcont');
        Route::get('/get-detail-item-in-subcont', 'getDetailListIN')->name('get-detail-item-in-subcont');
        Route::get('/get-supplier-subcont', 'getsupplierSubcont')->name('get-supplier-subcont');
        Route::get('/show-detail-po-in-subcont', 'showdetailitemIN')->name('show-detail-po-in-subcont');
        Route::post('/save-in-detail-temp', 'SaveINDetailTemp')->name('save-in-detail-temp');
        Route::post('/store-in', 'storeIN')->name('store-sewing-in-subcont');
        Route::get('/get-detail-packing-in/{id?}', 'DetailPackingIN')->name('get-detail-sewing-in');

        Route::get('/report-packing-in-subcont', 'ReportINSubcont')->name('report-sewing-in-subcont');
        Route::get('/export-excel-packing-subcont-in', 'ExportINSubcont')->name('export-excel-sewing-subcont-in');

        Route::get('/report-packing-monitoring-subcont', 'ReportMonitoringSubcont')->name('report-sewing-monitoring-subcont');
        Route::get('/export-excel-packing-subcont-monitoring', 'ExportMonitoringSubcont')->name('export-excel-sewing-subcont-monitoring');

        Route::get('/report-packing-mutasi-subcont', 'ReportMutasiSubcont')->name('report-sewing-mutasi-subcont');
        Route::get('/export-excel-packing-subcont-mutasi', 'ExportMutasiSubcont')->name('export-excel-sewing-subcont-mutasi');

        Route::get('/approve-packing-in-subcont', 'ApprovePackingInSubcont')->name('approve-sewing-in-subcont');
        Route::get('/save-approve-packing-in', 'SaveApprovePackingIn')->name('save-approve-sewing-in');
        Route::post('/cancel-packing-in-subcont', 'CancelPackingInSubcont')->name('cancel-sewing-in-subcont');
        Route::post('/cancel-packing-out-subcont', 'CancelPackingOutSubcont')->name('cancel-sewing-out-subcont');
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
        Route::get('/export_excel_packing_list', 'export_excel_packing_list')->name('export_excel_packing_list');
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

    // Marketing
    // Dashboard
    Route::controller(MarketingDashboardController::class)->middleware('marketing')->group(function () {
        Route::get('/dashboard_marketing', 'dashboard_marketing')->name('dashboard-marketing');
        Route::get('/get_data_dash_marketing', 'get_data_dash_marketing')->name('get_data_dash_marketing');
        Route::get('/get_data_dash_marketing_top_buyer', 'get_data_dash_marketing_top_buyer')->name('get_data_dash_marketing_top_buyer');
    });

    // Master
    // Route::controller(Marketing_CostingController::class)->prefix("master-costing")->middleware('marketing')->group(function () {
    //     Route::get('/', 'index')->name('master-costing');
    //     Route::get('/getprod_item_costing', 'getprod_item_costing')->name('getprod_item_costing');
    //     Route::post('/store_master_costing_production', 'store_master_costing_production')->name('store_master_costing_production');
    //     Route::get('/edit_costing/{id?}', 'edit_costing')->name('edit_costing');
    //     Route::post('/update_header_master_costing', 'update_header_master_costing')->name('update_header_master_costing');
    //     Route::get('/get_jns_costing_material', 'get_jns_costing_material')->name('get_jns_costing_material');
    //     Route::get('/get_material_costing', 'get_material_costing')->name('get_material_costing');

    // });

    // Master BOM
    Route::controller(Marketing_CostingController::class)->prefix("master-costing")->middleware('marketing')->group(function () {
        Route::get('/', 'index')->name('master-costing');
        Route::get('/create', 'create')->name('create-costing');
        Route::post('/store', 'store')->name('store-costing');
        Route::get('/get-item-contents', 'getItemContents')->name('get-item-contents');
        Route::get('/edit/{id}', 'edit')->name('edit-costing');
        Route::post('/store-detail', 'storeDetail')->name('store-costing-detail');
        Route::put('/update-header/{id}', 'updateHeader')->name('update-costing-header');
        Route::delete('/delete-detail/{id}', 'destroyDetail')->name('delete-costing-detail');
        Route::get('/print-pdf/{id}', 'printPdf')->name('print-costing-pdf');
        Route::get('get-detail-row-costing/{id}', 'getDetailRow')->name('get-detail-row-costing');
        Route::post('update-detail', 'updateDetail')->name('update-detail-costing');
        Route::get('print-excel-costing/{id}', 'printExcel')->name('print-excel-costing');
        Route::get('/approval', 'approval')->name('master-costing-approval');
        Route::post('/approve/{id}', 'submitApproval')->name('submit-costing-approval');
    });

    // Master BOM
    Route::controller(Marketing_BomController::class)->prefix("master-bom")->middleware('marketing')->group(function () {
        Route::get('/', 'index')->name('master-bom');
        Route::get('/create', 'create')->name('create-bom');
        Route::post('/store-color', [Marketing_BomController::class, 'storeColor'])->name('store-color');
        Route::post('/store-size', [Marketing_BomController::class, 'storeSize'])->name('store-size');
        Route::post('/get-rule-bom', [Marketing_BomController::class, 'getRuleBom'])->name('get-rule-bom');
        Route::post('/get-list-data-bom', [Marketing_BomController::class, 'getListData'])->name('get-list-data-bom');
        Route::get('/detail/{id}', [Marketing_BomController::class, 'showDetail'])->name('show-detail-bom');
        Route::post('/store-header', [Marketing_BomController::class, 'storeHeader'])->name('store-bom-header');
        Route::post('/store-detail', [Marketing_BomController::class, 'storeDetail'])->name('store-bom');
        Route::get('/get-items/{id}', 'getItems')->name('get-items');
        Route::get('/edit/{id}', 'edit')->name('edit-bom');
        Route::get('/get-item-row/{id}', [Marketing_BomController::class, 'getItemRow'])->name('get-item-row-bom');
        Route::post('/update-item-row/{id}', [Marketing_BomController::class, 'updateItemRow'])->name('update-item-row-bom');
        Route::post('/delete-batch-bom', [Marketing_BomController::class, 'deleteBatch'])->name('delete-batch-bom');
        Route::get('/export-excel-bom', [Marketing_BomController::class, 'exportExcel'])->name('export-excel-bom');
        Route::post('/get-item-contents', [Marketing_BomController::class, 'getItemContents'])->name('get-item-contents-bom');
        Route::post('store-other', [Marketing_BomController::class, 'storeOther'])->name('bom.store_other');
        Route::get('/bom-marketing/get-other/{id}', [Marketing_BomController::class, 'getOther'])->name('bom.get_other');
        Route::delete('/bom-marketing/delete-other/{id}', [Marketing_BomController::class, 'destroyOther'])->name('bom.destroy_other');
        Route::post('/master-marketing-bom/store-detail-edit', [Marketing_BomController::class, 'storeDetailEdit'])->name('store-bom-detail-edit');
        Route::post('/master-marketing-bom/update-header', [Marketing_BomController::class, 'updateBomHeader'])->name('update-bom-header');
        Route::get('/approval', 'approval')->name('master-bom-approval');
        Route::post('/approve/{id}', 'submitApproval')->name('submit-bom-approval');
    });

     // Master BOM Additional
    Route::controller(Marketing_AdditionalBomController::class)->prefix("master-bom-additional")->middleware('marketing')->group(function () {

        Route::get('/', 'index')->name('master-bom-additional');
        Route::get('/create', 'create')->name('create-bom-additional');
        Route::get('/edit/{id}', 'edit')->name('edit-bom-additional');
        Route::get('/detail/{id}', 'showDetail')->name('show-detail-bom-additional');


        Route::get('/get-po-by-so', 'getPoBySo')->name('get-po-by-so');
        Route::get('/get-items/{id}', 'getItems')->name('get-items-additional');
        Route::get('/get-item-row/{id}', 'getItemRow')->name('get-item-row-bom-additional');

        Route::post('/get-rule-bom-additional', 'getRuleBom')->name('get-rule-bom-additional');
        Route::post('/get-list-data-bom-additional', 'getListData')->name('get-list-data-bom-additional');

        Route::post('/store-header', 'storeHeader')->name('store-bom-additional-header');
        Route::post('/store-item', 'storeDetail')->name('store-bom-additional-item');
        Route::post('/update-po', 'updatePo')->name('bom-add.update-po');
        Route::post('/update-item-row/{id}', 'updateItemRow')->name('update-item-row-bom-additional');


        Route::post('/delete-batch-bom-additional', 'deleteBatch')->name('delete-batch-bom-additional');
        Route::get('/export-excel-bom-additional', 'exportExcel')->name('export-excel-bom-additional');

    });

     // Master SO
    Route::controller(Marketing_SOController::class)->prefix("master-marketing-so")->middleware('marketing')->group(function () {
        Route::get('/', 'index')->name('master-marketing-so');
        Route::get('/create', 'create')->name('create-so');
        Route::get('/detail/{id}', 'get_detail')->name('get-detail-so');
        Route::post('/get-product-items', 'getProductItems')->name('get-product-items');
        Route::post('/upload-excel', 'uploadExcelSO')->name('so-upload-excel');
        Route::get('/get-temp-data', 'getTempData')->name('so-get-temp-data');
        Route::post('/store', 'store')->name('so-store');
        Route::get('/get-detail-material/{id}', 'getDetailMaterialSo')->name('get-detail-material-so');
        Route::post('/store-detail', 'storeDetail')->name('store-bom-detail');
        Route::post('/so-get-item-contents', 'getItemContents')->name('so-get-item-contents');
        Route::post('/so-get-rule', 'getRuleBom')->name('so-get-rule');
        Route::post('/so-get-list-data', 'getListData')->name('so-get-list-data');
        Route::post('/so-store-bom-detail', 'storeDetail')->name('so-store-bom-detail');
        Route::get('/so-get-bom-items/{id}', 'getItems')->name('so-get-bom-items');
        Route::get('/so-get-bom-master', 'getBomMasterData')->name('so-get-bom-master');
        Route::post('/so-update-bom-header', 'updateBomHeader')->name('so-update-bom-header');
        Route::post('/so-store-master-color', 'storeMasterColorQuick')->name('so-store-master-color');
        Route::post('/so-store-master-size', 'storeMasterSizeQuick')->name('so-store-master-size');
        Route::post('/update-qty', 'updateQtySO')->name('update-qty-so');
        Route::post('/cancel-restore-so', 'cancelRestoreSO')->name('cancel-restore-so');
        Route::get('/print-pdf/{id}', 'printPdfSO')->name('print-pdf-so');
        Route::get('/get-bom-data', 'getBomCostingData')->name('so-get-bom-data');
    });

    // QC Inspect Kain
    // Dashboard
    Route::controller(QCInspectDashboardController::class)->middleware('warehouse')->group(function () {
        Route::get('/dashboard_qc_inspect', 'dashboard_qc_inspect')->name('dashboard-qc-inspect');
    });

    // Master QC Inspect
    Route::controller(QCInspectMasterController::class)->prefix("master")->middleware('warehouse')->group(function () {
        Route::get('/qc_inspect_master_critical_defect_show', 'qc_inspect_master_critical_defect_show')->name('qc_inspect_master_critical_defect_show');
        Route::post('/qc_inspect_master_critical_defect_add', 'qc_inspect_master_critical_defect_add')->name('qc_inspect_master_critical_defect_add');
        Route::get('/qc_inspect_master_founding_issue_show', 'qc_inspect_master_founding_issue_show')->name('qc_inspect_master_founding_issue_show');
        Route::post('/qc_inspect_master_founding_issue_add', 'qc_inspect_master_founding_issue_add')->name('qc_inspect_master_founding_issue_add');
    });

    // Proses Packing List
    Route::controller(QCInspectProsesPackingListController::class)->prefix("proses-packing-list")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_proses_packing_list');
        Route::get('/qc_inspect_proses_packing_list_det/{id_lok_in_material?}', 'qc_inspect_proses_packing_list_det')->name('qc_inspect_proses_packing_list_det');
        Route::get('/show_calculate_qc_inspect', 'show_calculate_qc_inspect')->name('show_calculate_qc_inspect');
        Route::post('/generate_qc_inspect', 'generate_qc_inspect')->name('generate_qc_inspect');
        Route::get('/show_qc_inspect_form_modal', 'show_qc_inspect_form_modal')->name('show_qc_inspect_form_modal');
        Route::post('/generate_form_kedua', 'generate_form_kedua')->name('generate_form_kedua');
        Route::get('/show_inspect_pertama', 'show_inspect_pertama')->name('show_inspect_pertama');
        Route::get('/show_inspect_kedua', 'show_inspect_kedua')->name('show_inspect_kedua');
        Route::get('/export_qc_inspect/{id_lok_in_material?}', 'export_qc_inspect')->name('export_qc_inspect');
        Route::post('/pass_with_condition', 'pass_with_condition')->name('pass_with_condition');
        Route::post('/upload_blanket_photo', 'upload_blanket_photo')->name('upload_blanket_photo');
        Route::get('/get_blanket_photo', 'get_blanket_photo')->name('get_blanket_photo');
        Route::get('/get_info_modal_defect_packing_list', 'get_info_modal_defect_packing_list')->name('get_info_modal_defect_packing_list');
        Route::post('/upload_modal_defect_photo', 'upload_modal_defect_photo')->name('upload_modal_defect_photo');
        Route::get('/show_modal_defect_packing_list', 'show_modal_defect_packing_list')->name('show_modal_defect_packing_list');
        Route::post('/delete_modal_defect_packing_list', 'delete_modal_defect_packing_list')->name('delete_modal_defect_packing_list');
        Route::get('/export_pdf_list_defect/{id_lok_in_material?}', 'export_pdf_list_defect')->name('export_pdf_list_defect');
        Route::get('/print_sticker_packing_list', 'print_sticker_packing_list')->name('print_sticker_packing_list');
    });

    // Proses Form Inspect
    Route::controller(QCInspectProsesFormInspectController::class)->prefix("proses-form-inspect")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_proses_form_inspect');
        Route::get('/qc_inspect_proses_form_inspect_det/{id?}', 'qc_inspect_proses_form_inspect_det')->name('qc_inspect_proses_form_inspect_det');
        Route::post('/get_operator_info', 'get_operator_info')->name('get_operator_info');
        Route::post('/save_start_form_inspect', 'save_start_form_inspect')->name('save_start_form_inspect');
        Route::post('/get_barcode_info', 'get_barcode_info')->name('get_barcode_info');
        Route::post('/save_fabric_form_inspect', 'save_fabric_form_inspect')->name('save_fabric_form_inspect');
        Route::post('/save_detail_fabric', 'save_detail_fabric')->name('save_detail_fabric');
        Route::post('/save_visual_inspection', 'save_visual_inspection')->name('save_visual_inspection');
        Route::get('/qc_inspect_show_visual_inspect', 'qc_inspect_show_visual_inspect')->name('qc_inspect_show_visual_inspect');
        Route::post('/qc_inspect_delete_visual', 'qc_inspect_delete_visual')->name('qc_inspect_delete_visual');
        Route::post('/calculate_act_point', 'calculate_act_point')->name('calculate_act_point');
        Route::get('/qc_inspect_show_act_point', 'qc_inspect_show_act_point')->name('qc_inspect_show_act_point');
        Route::post('/finish_form_inspect', 'finish_form_inspect')->name('finish_form_inspect');
        Route::get('/show_calculate_width_length', 'show_calculate_width_length')->name('show_calculate_width_length');
    });

    // Proses Form Inspect
    Route::controller(QCInspectProsesFabricRelaxationController::class)->prefix("proses-fabric-relaxation")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_proses_fabric_relaxation');
        Route::get('/input_fabric_relaxation', 'input_fabric_relaxation')->name('input_fabric_relaxation');
        Route::post('/get_barcode_info_fabric_relaxation', 'get_barcode_info_fabric_relaxation')->name('get_barcode_info_fabric_relaxation');
        Route::post('/save_form_fabric_relaxation', 'save_form_fabric_relaxation')->name('save_form_fabric_relaxation');
        Route::get('/input_fabric_relaxation_det/{id?}', 'input_fabric_relaxation_det')->name('input_fabric_relaxation_det');
        Route::post('/finish_form_fabric_relaxation', 'finish_form_fabric_relaxation')->name('finish_form_fabric_relaxation');
        Route::get('/print_sticker_fabric_relaxation', 'print_sticker_fabric_relaxation')->name('print_sticker_fabric_relaxation');
    });

    // Laporan QC Inspect
    Route::controller(QCInspectLaporanController::class)->prefix("proses-form-inspect")->middleware('warehouse')->group(function () {
        Route::get('/qc_inspect_laporan_roll', 'qc_inspect_laporan_roll')->name('qc_inspect_laporan_roll');
        Route::get('/export_excel_qc_inspect_roll', 'export_excel_qc_inspect_roll')->name('export_excel_qc_inspect_roll');
        Route::get('/qc_inspect_laporan_lot', 'qc_inspect_laporan_lot')->name('qc_inspect_laporan_lot');
        Route::get('/export_excel_qc_inspect_lot', 'export_excel_qc_inspect_lot')->name('export_excel_qc_inspect_lot');
        Route::get('/qc_inspect_report_shade_band', 'qc_inspect_report_shade_band')->name('qc_inspect_report_shade_band');
        Route::get('/qc_inspect_report_shade_band_add/{id_item}/{id_jo}/{group}', 'qc_inspect_report_shade_band_add')->name('qc_inspect_report_shade_band_add');
        Route::get('/qc_inspect_report_shade_band_print/{id_item}/{id_jo}/{group}', 'qc_inspect_report_shade_band_print')->name('qc_inspect_report_shade_band_print');
        Route::get('/qc_inspect_sticker_shade_band_print/{id_item}/{id_jo}/{group}', 'qc_inspect_sticker_shade_band_print')->name('qc_inspect_sticker_shade_band_print');
        Route::get('/qc_inspect_report_shade_band_detail', 'qc_inspect_report_shade_band_detail')->name('qc_inspect_report_shade_band_detail');
        Route::post('/save_report_shade_band_detail', 'save_report_shade_band_detail')->name('save_report_shade_band_detail');
        Route::get('/get_photo_shade_band', 'get_photo_shade_band')->name('get_photo_shade_band');
        Route::post('/delete_photo_shade_band', 'delete_photo_shade_band')->name('delete_photo_shade_band');
    });

    // Proses Print Bintex Shade Band
    Route::controller(QCInspectPrintBintexShadeBandController::class)->prefix("proses-print-bintex-shade-band")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_print_bintex_shade_band');
        Route::get('/print_sticker_bintex_shade_band', 'print_sticker_bintex_shade_band')->name('print_sticker_bintex_shade_band');
    });

    // Proses Shade Band
    Route::controller(QCInspectShadeBandController::class)->prefix("proses-shade-band")->middleware('warehouse')->group(function () {
        Route::get('/', 'index')->name('qc_inspect_shade_band');
        Route::get('/input_shade_band', 'input_shade_band')->name('input_shade_band');
        Route::post('/insert_tmp_shade_band', 'insert_tmp_shade_band')->name('insert_tmp_shade_band');
        Route::post('/get_barcode_info_shade_band', 'get_barcode_info_shade_band')->name('get_barcode_info_shade_band');
        Route::get('/get_list_shade_band_tmp', 'get_list_shade_band_tmp')->name('get_list_shade_band_tmp');
        Route::post('/delete_barcode_tmp_shade_band', 'delete_barcode_tmp_shade_band')->name('delete_barcode_tmp_shade_band');
        Route::post('/save_proses_shade_band', 'save_proses_shade_band')->name('save_proses_shade_band');
        Route::get('/print_sticker_group_shade_band', 'print_sticker_group_shade_band')->name('print_sticker_group_shade_band');
    });


    // Management Report
    // Dashboard
    Route::controller(MgtReportDashboardController::class)->middleware('role:management')->group(function () {
        Route::get('/dashboard_mgt_report', 'dashboard_mgt_report')->name('dashboard-mgt-report');
    });

    // Proses Management Report
    Route::controller(MgtReportProsesController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_proses_daily_cost', 'mgt_report_proses_daily_cost')->name('mgt_report_proses_daily_cost');
        Route::get('/mgt_report_proses_daily_cost_show_working_days', 'mgt_report_proses_daily_cost_show_working_days')->name('mgt_report_proses_daily_cost_show_working_days');
        Route::get('/contoh_upload_daily_cost', 'contoh_upload_daily_cost')->name('contoh_upload_daily_cost');
        Route::post('/upload_excel_daily_cost', 'upload_excel_daily_cost')->name('upload_excel_daily_cost');
        Route::get('/mgt_report_proses_daily_cost_show_preview', 'mgt_report_proses_daily_cost_show_preview')->name('mgt_report_proses_daily_cost_show_preview');
        Route::post('/save_tmp_upload_daily_cost', 'save_tmp_upload_daily_cost')->name('save_tmp_upload_daily_cost');
        Route::post('/delete_tmp_upload_daily_cost', 'delete_tmp_upload_daily_cost')->name('delete_tmp_upload_daily_cost');
        Route::get('/show_mgt_report_det_daily_cost', 'show_mgt_report_det_daily_cost')->name('show_mgt_report_det_daily_cost');
        Route::post('/delete_daily_cost', 'delete_daily_cost')->name('delete_daily_cost');
        Route::post('/update_data_labor', 'update_data_labor')->name('update_data_labor');
    });

    Route::controller(MgtReportDailyCostController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_daily_cost', 'mgt_report_daily_cost')->name('mgt_report_daily_cost');
        Route::get('/export_excel_laporan_daily_cost', 'export_excel_laporan_daily_cost')->name('export_excel_laporan_daily_cost');
    });

    Route::controller(MgtReportEarningController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_earning', 'mgt_report_earning')->name('mgt_report_earning');
        Route::get('/export_excel_laporan_earning', 'export_excel_laporan_earning')->name('export_excel_laporan_earning');
    });

    Route::controller(MgtReportSumProdEarnController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_sum_prod_earn', 'mgt_report_sum_prod_earn')->name('mgt_report_sum_prod_earn');
        Route::get('/export_excel_laporan_sum_prod_earn', 'export_excel_laporan_sum_prod_earn')->name('export_excel_laporan_sum_prod_earn');
    });

    Route::controller(MgtReportSumFullEarnController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_sum_full_earn', 'mgt_report_sum_full_earn')->name('mgt_report_sum_full_earn');
        Route::get('/export_excel_laporan_sum_fullearn', 'export_excel_laporan_sum_full_earn')->name('export_excel_laporan_sum_full_earn');
    });

    Route::controller(MgtReportSumBuyerController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_sum_buyer', 'mgt_report_sum_buyer')->name('mgt_report_sum_buyer');
        Route::get('/export_excel_laporan_sum_buyer', 'export_excel_laporan_sum_buyer')->name('export_excel_laporan_sum_buyer');
    });

    Route::controller(MgtReportDailyEarnBuyerController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_daily_earn_buyer', 'mgt_report_daily_earn_buyer')->name('mgt_report_daily_earn_buyer');
        Route::get('/export_excel_laporan_daily_earn_buyer', 'export_excel_laporan_daily_earn_buyer')->name('export_excel_laporan_daily_earn_buyer');
    });

    Route::controller(MgtReportProfitLineController::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/mgt_report_profit_line', 'mgt_report_profit_line')->name('mgt_report_profit_line');
        Route::get('/export_excel_laporan_profit_line', 'export_excel_laporan_profit_line')->name('export_excel_laporan_profit_line');
    });


    // Industrial Engineering
    // Dashboard
    Route::controller(IEDashboardController::class)->middleware('role:management')->group(function () {
        Route::get('/dashboard_IE', 'dashboard_IE')->name('dashboard-IE');
    });

    // Proses Industrial Engineering Master Process
    Route::controller(IEMasterProcessController::class)->prefix("master")->middleware('role:management')->group(function () {
        Route::get('/IE_master_process', 'IE_master_process')->name('IE_master_process');
        Route::post('/IE_save_master_process', 'IE_save_master_process')->name('IE_save_master_process');
        Route::get('/IE_show_master_process', 'IE_show_master_process')->name('IE_show_master_process');
        Route::post('/IE_edit_master_process', 'IE_edit_master_process')->name('IE_edit_master_process');
        Route::get('/contoh_upload_master_process', 'contoh_upload_master_process')->name('contoh_upload_master_process');
        Route::post('/upload_excel_master_process', 'upload_excel_master_process')->name('upload_excel_master_process');
    });
    // Proses Industrial Engineering Master Part Process
    Route::controller(IEMasterPartProcessController::class)->prefix("master")->middleware('role:management')->group(function () {
        Route::get('/IE_master_part_process', 'IE_master_part_process')->name('IE_master_part_process');
        Route::get('/IE_master_part_process_show_new', 'IE_master_part_process_show_new')->name('IE_master_part_process_show_new');
        Route::post('/IE_save_master_part_process', 'IE_save_master_part_process')->name('IE_save_master_part_process');
        Route::get('/IE_show_master_part_process', 'IE_show_master_part_process')->name('IE_show_master_part_process');
        Route::get('/IE_master_part_process_show_edit', 'IE_master_part_process_show_edit')->name('IE_master_part_process_show_edit');
        Route::post('/IE_update_master_part_process', 'IE_update_master_part_process')->name('IE_update_master_part_process');
    });

    // Proses Industrial Engineering Operational Breakdown
    Route::controller(IE_Proses_OB_Controller::class)->prefix("proses")->middleware('role:management')->group(function () {
        Route::get('/IE_proses_op_breakdown', 'IE_proses_op_breakdown')->name('IE_proses_op_breakdown');
        Route::get('/show_modal_proses_breakdown_new', 'show_modal_proses_breakdown_new')->name('show_modal_proses_breakdown_new');
        Route::get('/show_modal_summary_breakdown', 'show_modal_summary_breakdown')->name('show_modal_summary_breakdown');
        Route::post('/IE_save_op_breakdown', 'IE_save_op_breakdown')->name('IE_save_op_breakdown');
        Route::get('/IE_show_op_breakdown', 'IE_show_op_breakdown')->name('IE_show_op_breakdown');
        Route::get('/IE_show_op_breakdown_edit', 'IE_show_op_breakdown_edit')->name('IE_show_op_breakdown_edit');
        Route::post('/IE_update_op_breakdown', 'IE_update_op_breakdown')->name('IE_update_op_breakdown');
    });

    // Laporan Industrial Engineering Recap SMV
    Route::controller(IE_Laporan_Controller::class)->prefix("laporan")->middleware('role:management')->group(function () {
        Route::get('/IE_lap_recap_smv', 'IE_lap_recap_smv')->name('IE_lap_recap_smv');
        Route::get('/IE_lap_recap_cm_price', 'IE_lap_recap_cm_price')->name('IE_lap_recap_cm_price');
    });

    // Export Import (EXIM)
    Route::controller(ExportImportController::class)->prefix("export-import")->middleware('role:export_import')->group(function () {
        Route::get('/', 'index')->name('export-import');
        Route::get('/report-rekonsiliasi-ceisa', 'ReportRekonsiliasi')->name('report-rekonsiliasi-ceisa');
        Route::get('/export-rekonsiliasi-ceisa', 'ExportReportRekonsiliasi')->name('export-rekonsiliasi-ceisa');
        Route::get('/report-ceisa-detail', 'ReportCeisaDetail')->name('report-ceisa-detail');
        Route::get('/export-ceisa-detail', 'ExportReportCeisaDetail')->name('export-ceisa-detail');
        Route::get('/report-signalbit-bc', 'ReportSignalbitBC')->name('report-signalbit-bc');
        Route::get('/export-excel-report-signalbit-bc', 'ExportReportSignalbitBC')->name('export-excel-report-signalbit-bc');
    });

    // WHS Soljer
    Route::controller(PenerimaanGudangInputanController::class)->prefix("penerimaan-gudang-inputan")->middleware('role:warehouse')->group(function () {
        Route::get('/', 'index')->name('penerimaan-gudang-inputan');
        Route::get('/create', 'create')->name('create-penerimaan-gudang-inputan');
        Route::post('/store', 'store')->name('store-penerimaan-gudang-inputan');
        Route::get('/edit/{id}', 'edit')->name('edit-penerimaan-gudang-inputan');
        Route::put('/update/{id}', 'update')->name('update-penerimaan-gudang-inputan');
        Route::put('/cancel/{id}', 'cancel')->name('cancel-penerimaan-gudang-inputan');
        Route::get('/print-sj/{id}', 'printSj')->name('print-sj-penerimaan-gudang-inputan');
        Route::get('/print-barcode/{id}', 'printBarcode')->name('print-barcode-penerimaan-gudang-inputan');
        Route::get('/contoh-upload-import', 'contohUploadImport')->name('contoh-upload-import-penerimaan-gudang-inputan');
        Route::post('/import-data', 'importData')->name('import-data-penerimaan-gudang-inputan');
    });

    Route::controller(PenerimaanGudangInputanAccesoriesController::class)->prefix("penerimaan-gudang-inputan-accesories")->middleware('role:warehouse')->group(function () {
        Route::get('/', 'index')->name('penerimaan-gudang-inputan-accesories');
        Route::get('/create', 'create')->name('create-penerimaan-gudang-inputan-accesories');
        Route::post('/store', 'store')->name('store-penerimaan-gudang-inputan-accesories');
        Route::get('/edit/{id}', 'edit')->name('edit-penerimaan-gudang-inputan-accesories');
        Route::put('/update/{id}', 'update')->name('update-penerimaan-gudang-inputan-accesories');
        Route::put('/cancel/{id}', 'cancel')->name('cancel-penerimaan-gudang-inputan-accesories');
        Route::get('/print-sj/{id}', 'printSj')->name('print-sj-penerimaan-gudang-inputan-accesories');
        Route::get('/print-barcode/{id}', 'printBarcode')->name('print-barcode-penerimaan-gudang-inputan-accesories');
        Route::get('/contoh-upload-import', 'contohUploadImport')->name('contoh-upload-import-penerimaan-gudang-inputan-accesories');
        Route::post('/import-data', 'importData')->name('import-data-penerimaan-gudang-inputan-accesories');
    });

    Route::controller(PenerimaanGudangInputanFgController::class)->prefix("penerimaan-gudang-inputan-fg")->middleware('role:warehouse')->group(function () {
        Route::get('/', 'index')->name('penerimaan-gudang-inputan-fg');
        Route::get('/create', 'create')->name('create-penerimaan-gudang-inputan-fg');
        Route::post('/store', 'store')->name('store-penerimaan-gudang-inputan-fg');
        Route::get('/edit/{id}', 'edit')->name('edit-penerimaan-gudang-inputan-fg');
        Route::put('/update/{id}', 'update')->name('update-penerimaan-gudang-inputan-fg');
        Route::put('/cancel/{id}', 'cancel')->name('cancel-penerimaan-gudang-inputan-fg');
        Route::get('/print-sj/{id}', 'printSj')->name('print-sj-penerimaan-gudang-inputan-fg');
        Route::get('/print-barcode/{id}', 'printBarcode')->name('print-barcode-penerimaan-gudang-inputan-fg');
        Route::get('/contoh-upload-import', 'contohUploadImport')->name('contoh-upload-import-penerimaan-gudang-inputan-fg');
        Route::post('/import-data', 'importData')->name('import-data-penerimaan-gudang-inputan-fg');
    });

    Route::controller(PengeluaranGudangInputanController::class)->prefix("pengeluaran-gudang-inputan")->middleware('role:warehouse')->group(function () {
        Route::get('/', 'index')->name('pengeluaran-gudang-inputan');
        Route::get('/create', 'create')->name('create-pengeluaran-gudang-inputan');
        Route::post('/store', 'store')->name('store-pengeluaran-gudang-inputan');
        Route::get('/edit/{id}', 'edit')->name('edit-pengeluaran-gudang-inputan');
        Route::put('/update/{id}', 'update')->name('update-pengeluaran-gudang-inputan');
        Route::put('/cancel/{id}', 'cancel')->name('cancel-pengeluaran-gudang-inputan');
        Route::get('/print-sj/{id}', 'printSj')->name('print-sj-pengeluaran-gudang-inputan');
        Route::get('/print-barcode/{id}', 'printBarcode')->name('print-barcode-pengeluaran-gudang-inputan');
        Route::get('/get-data-barcode', 'getDataBarcode')->name('get-data-barcode-pengeluaran-gudang-inputan');
    });


    Route::controller(PurchasingController::class)->prefix("purchasing")->middleware('role:purchasing')->group(function () {
        Route::get('/', 'index')->name('purchasing');
        Route::get('/list-data', 'index')->name('index-purchase-order');
        Route::get('/count-data', 'countData')->name('count-purchase-order');
        Route::get('/create', 'create')->name('create-purchase-order');
        Route::post('/store', 'store')->name('store-purchase-order');
        Route::get('/get-items-by-bom', 'getItemsByBom')->name('get-items-by-bom');
        Route::get('/edit/{id}', 'edit')->name('edit-purchase-order');
        Route::post('/update/{id}', 'update')->name('update-purchase-order');
        Route::get('/show/{id}', 'show')->name('show-purchase-order');
        Route::post('/update-date/{id}', 'updateDate')->name('update-date-purchase-order');
        Route::get('/approval', 'approval')->name('approval-purchase-order');
        Route::post('/approve/{id}', 'approve')->name('approve-purchase-order');
    });

    Route::controller(PengeluaranGudangInputanFgController::class)->prefix("pengeluaran-gudang-inputan-fg")->middleware('role:warehouse')->group(function () {
        Route::get('/', 'index')->name('pengeluaran-gudang-inputan-fg');
        Route::get('/create', 'create')->name('create-pengeluaran-gudang-inputan-fg');
        Route::post('/store', 'store')->name('store-pengeluaran-gudang-inputan-fg');
        Route::get('/edit/{id}', 'edit')->name('edit-pengeluaran-gudang-inputan-fg');
        Route::put('/update/{id}', 'update')->name('update-pengeluaran-gudang-inputan-fg');
        Route::put('/cancel/{id}', 'cancel')->name('cancel-pengeluaran-gudang-inputan-fg');
        Route::get('/print-sj/{id}', 'printSj')->name('print-sj-pengeluaran-gudang-inputan-fg');
        Route::get('/print-barcode/{id}', 'printBarcode')->name('print-barcode-pengeluaran-gudang-inputan-fg');
        Route::get('/get-data-barcode', 'getDataBarcode')->name('get-data-barcode-pengeluaran-gudang-inputan-fg');
    });
});

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
    // Route::get('/report-rekonsiliasi-ceisa', 'ReportRekonsiliasi')->name('report-rekonsiliasi-ceisa');
    // Route::get('/export-rekonsiliasi-ceisa', 'ExportReportRekonsiliasi')->name('export-rekonsiliasi-ceisa');
    // Route::get('/report-ceisa-detail', 'ReportCeisaDetail')->name('report-ceisa-detail');
    // Route::get('/export-ceisa-detail', 'ExportReportCeisaDetail')->name('export-ceisa-detail');

    // Route::get('/report-signalbit-bc', 'ReportSignalbitBC')->name('report-signalbit-bc');
    // Route::get('/export-excel-report-signalbit-bc', 'ExportReportSignalbitBC')->name('export-excel-report-signalbit-bc');
});



// Route::get('/dashboard-chart', function () {
//    return view('cutting.chart.dashboard-chart');
// });
Route::get('/trigger', function () {
    event(new TestEvent('This is realtime data'));
    return response()->json(['status' => 'Event sent testing']);
});

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
