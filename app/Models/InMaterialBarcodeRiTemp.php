<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InMaterialBarcodeRiTemp extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';

    protected $table = 'whs_inmaterial_barcode_ri_temp';

    protected $guarded = [];
}
