<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputPackingNds extends Model
{
    use HasFactory;

    protected $connection = 'mysql_nds';

    protected $table = 'output_rfts_packing';

    protected $guarded = [];
}
