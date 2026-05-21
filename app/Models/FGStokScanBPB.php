<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FGStokScanBPB extends Model
{
    use HasFactory;

    protected $table = 'fg_stok_bpb_scan';

    protected $guarded = [];

    /**
     * Get the marker that own the details.
     */
}
