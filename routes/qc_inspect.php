<?php

use App\Http\Livewire\Qc\Master\Satuan;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Main page route
    Route::get('/qc-inspect-master-satuan', [Satuan::class, 'render'])->name('qc-inspect-master-satuan');
    
    // DataTable route
    Route::get('/qc-inspect-satuan/data', [Satuan::class, 'getDatatables'])->name('qc-inspect-satuan.data');
    
    // CRUD operation routes
    Route::post('/qc-inspect-satuan/create', [Satuan::class, 'create'])->name('qc-inspect-satuan.create');
    Route::post('/qc-inspect-satuan/update', [Satuan::class, 'update'])->name('qc-inspect-satuan.update');
    Route::delete('/qc-inspect-satuan/delete/{id}', [Satuan::class, 'delete'])->name('qc-inspect-satuan.delete');
});