<?php

namespace App\Models\qc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSatuan extends Model
{
    use HasFactory;

    protected $table = 'qc_inspect_master_satuan';
    protected $fillable = ['satuan'];
}
