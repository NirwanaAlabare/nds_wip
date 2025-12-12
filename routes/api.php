<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\MgtReportProsesController;
use App\Http\Controllers\FGStokLaporanController;
use App\Http\Controllers\DashboardWipLineController;
use App\Http\Controllers\InMaterialController;
use App\Http\Controllers\OutMaterialController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// User API
Route::get('user/get-api', [UserController::class, 'getApi']);
Route::post('user/store-api', [UserController::class, 'storeApi']);

// Mgt Report
Route::controller(MgtReportProsesController::class)->prefix("mgt-report-proses")->group(function () {
    Route::get('/', 'index');
    Route::post('/update_data_labor', 'update_data_labor_new');
});

// FGStok Report
Route::controller(FGStokLaporanController::class)->prefix("laporan-fg-stock")->group(function () {
    Route::get('/', 'index');
    Route::get('/export_excel_mutasi_fg_stok', 'export_excel_mutasi_fg_stok');
    Route::get('/show_fg_stok_mutasi', 'show_fg_stok_mutasi');
});

// DASHBOARD WIP LINE
Route::controller(DashboardWipLineController::class)->prefix("trigger-wip-line")->group(function () {
    Route::post('dashboard-line/wip-line-sign', 'trigger_wip_line');
});

//In Barcode Fabric
Route::controller(InMaterialController::class)->prefix("in-barcode-fabric")->group(function () {
    Route::post('in-material/in-barcode-fabric', 'in_barcode_fabric');
});

//out barcode Fabric
Route::controller(OutMaterialController::class)->prefix("out-barcode-fabric")->group(function () {
    Route::post('out-material/out-barcode-fabric', 'out_barcode_fabric');
});

//out barcode Fabric
Route::controller(OutMaterialController::class)->prefix("mutasi-barcode-fabric")->group(function () {
    Route::post('mutasi-material/mutasi-barcode-fabric', 'mutasi_barcode_fabric');
});
