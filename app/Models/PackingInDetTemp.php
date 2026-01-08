<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingInDetTemp extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'packing_in_det_temp';

    protected $guarded = [];

}