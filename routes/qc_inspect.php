<?php

use App\Http\Livewire\Qc\Master\Satuan;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\QC\Master\GroupInspect;
use App\Http\Livewire\QC\Master\Lenght;
use App\Http\Livewire\QC\Master\Result;
use App\Http\Livewire\QC\Inspect\QCInmaterialFabricController;

Route::middleware('auth')->group(function () {
    // Main page route
    Route::get('/qc-inspect-master-satuan', [Satuan::class, 'render'])->name('qc-inspect-master-satuan');
    
    // DataTable route
    Route::get('/qc-inspect-satuan/data', [Satuan::class, 'getDatatables'])->name('qc-inspect-satuan.data');
    
    // CRUD operation routes
    Route::post('/qc-inspect-satuan/create', [Satuan::class, 'create'])->name('qc-inspect-satuan.create');
    Route::put('/qc-inspect-satuan/update/{id}', [Satuan::class, 'update'])->name('qc-inspect-satuan.update');
    Route::delete('/qc-inspect-satuan/delete/{id}', [Satuan::class, 'delete'])->name('qc-inspect-satuan.delete');


    // Group Inspect Routes
    Route::prefix('qc-inspect-master-group-inspect')->group(function () {
        // View Route
        Route::get('/', [GroupInspect::class, 'render'])->name('qc-inspect-master-group-inspect');

        // DataTable Route
        Route::get('/data', [GroupInspect::class, 'getDatatables'])
            ->name('qc-inspect-group-inspect.data');

        // Create Route
        Route::post('/create', [GroupInspect::class, 'create'])
            ->name('qc-inspect-group-inspect.create');

        // Update Route
       Route::post('/update', [GroupInspect::class, 'update'])
        ->name('qc-inspect-group-inspect.update');

        // Delete Route
        Route::delete('/delete/{id}', [GroupInspect::class, 'delete'])
            ->name('qc-inspect-group-inspect.delete');
    });

        // Master Length Routes
    Route::prefix('qc-inspect-master-lenght')->group(function () {
        Route::get('/', [Lenght::class, 'render'])->name('qc-inspect-master-lenght');
        Route::post('/create', [Lenght::class, 'create'])
            ->name('qc-inspect-lenght.create');
        
        Route::post('/update', [Lenght::class, 'update'])
            ->name('qc-inspect-lenght.update');
        
        Route::delete('/delete/{id}', [Lenght::class, 'delete'])
            ->name('qc-inspect-lenght.delete');
        
        Route::get('/data', [Lenght::class, 'getDatatables'])
            ->name('qc-inspect-lenght.data');
    });

    Route::prefix('qc-inspect-master-defect')->group(function() {
        Route::get('/', [\App\Http\Livewire\QC\Master\Defect::class, 'render'])->name('qc-inspect-master-defect');
        Route::get('/data', [\App\Http\Livewire\QC\Master\Defect::class, 'getDatatables'])->name('qc-inspect-master-defect.data');
        Route::post('/create', [\App\Http\Livewire\QC\Master\Defect::class, 'create'])->name('qc-inspect-master-defect.create');
        Route::put('/update/{id}', [\App\Http\Livewire\QC\Master\Defect::class, 'update'])->name('qc-inspect-master-defect.update');
        Route::delete('/delete/{id}', [\App\Http\Livewire\QC\Master\Defect::class, 'delete'])->name('qc-inspect-master-defect.delete');
    });

        // Master Result Routes
    Route::prefix('qc-inspect-master-result')->group(function () {
        Route::get('/', [Result::class, 'render'])->name('qc-inspect-master-result');
        
        Route::post('/create', [Result::class, 'create'])
            ->name('qc-inspect-result.create');
        
        Route::post('/update', [Result::class, 'update'])
            ->name('qc-inspect-result.update');
        
        Route::delete('/delete/{id}', [Result::class, 'delete'])
            ->name('qc-inspect-result.delete');
        
        Route::get('/data', [Result::class, 'getDatatables'])
            ->name('qc-inspect-result.data');
    });

    Route::prefix('qc-inspect-inmaterial')->group(function() {
        Route::get('/', [QCInmaterialFabricController::class, 'index'])->name('qc-inspect-inmaterial');
        Route::post('qc-inspect-inmaterial/data', [App\Http\Livewire\QC\Inspect\QCInmaterialFabricController::class, 'getDatatables'])->name('qc-inspect-inmaterial.data');    });
});