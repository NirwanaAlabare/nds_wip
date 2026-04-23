<?php

use App\Http\Controllers\Sewing\MasterKursBiController;
use App\Http\Controllers\Sewing\MasterJabatanController;
use App\Http\Controllers\Sewing\MasterKaryawanController;
use App\Http\Controllers\Sewing\MasterBuyerController;
use App\Http\Controllers\Sewing\DataProduksiController;
use App\Http\Controllers\Sewing\DataDetailProduksiController;
use App\Http\Controllers\Sewing\DataDetailProduksiDayController;

Route::middleware('auth')->group(function () {
    // SB WIP SUMMARY :
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
    // END OF SB WIP SUMMARY
});
