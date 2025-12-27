<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingOutDetTemp extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'packing_out_det_temp';

    protected $guarded = [];

}