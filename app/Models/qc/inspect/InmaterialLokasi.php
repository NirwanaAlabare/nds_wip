<?php

namespace App\Models\qc\inspect;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InmaterialLokasi extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';
    protected $table = 'whs_lokasi_inmaterial';
}
