<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FGStokPenerimaanPacking extends Model
{
    use HasFactory;

    protected $table = 'fg_stok_penerimaan_packing';

    protected $guarded = [];

    /**
     * Get the marker that own the details.
     */
}
