<?php

use App\Http\Livewire\Qc\Master\Satuan;
use App\Models\qc\MasterSatuan;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/qc-inspect-master-satuan', [Satuan::class, 'render'])->name('qc-inspect-master-satuan');
    Route::get('/qc-inspect-satuan/data', [Satuan::class, 'getDatatables'])->name('qc-inspect-satuan.data');
});
