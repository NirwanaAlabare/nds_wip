<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoBarcodeTemp extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';
    protected $table = 'whs_ro_barcode_temp';
    protected $guarded = [];
}
