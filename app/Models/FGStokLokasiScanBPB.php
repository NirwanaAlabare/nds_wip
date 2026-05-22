<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FGStokLokasiScanBPB extends Model
{
    use HasFactory;

    protected $table = 'fg_stok_bpb_lokasi_scan';

    protected $guarded = [];

    /**
     * Get the marker that own the details.
     */
}
