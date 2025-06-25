<?php

use App\Http\Livewire\Qc\Master\Satuan;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\QC\Master\GroupInspect;

Route::middleware('auth')->group(function () {
    // Main page route
    Route::get('/qc-inspect-master-satuan', [Satuan::class, 'render'])->name('qc-inspect-master-satuan');
    
    // DataTable route
    Route::get('/qc-inspect-satuan/data', [Satuan::class, 'getDatatables'])->name('qc-inspect-satuan.data');
    
    // CRUD operation routes
    Route::post('/qc-inspect-satuan/create', [Satuan::class, 'create'])->name('qc-inspect-satuan.create');
    Route::post('/qc-inspect-satuan/update', [Satuan::class, 'update'])->name('qc-inspect-satuan.update');
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
});