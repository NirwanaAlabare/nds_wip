<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiCuttingPcsSaldoTmp extends Model
{
    use HasFactory;

    protected $table = 'mut_cut_pcs_tmp_pre';

    protected $guarded = [];

    public $timestamps = false;
}
