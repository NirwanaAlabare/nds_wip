<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FGStokLaporanController;
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

Route::get('user/get-api', [App\Http\Controllers\UserController::class, 'getApi']);
Route::post('user/store-api', [App\Http\Controllers\UserController::class, 'storeApi']);

Route::controller(FGStokLaporanController::class)->prefix("laporan-fg-stock")->group(function () {
    Route::get('/', 'index');
    Route::get('/export_excel_mutasi_fg_stok', 'export_excel_mutasi_fg_stok');
    Route::get('/show_fg_stok_mutasi', 'show_fg_stok_mutasi');
});
