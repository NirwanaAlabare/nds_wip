<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiCuttingPcsSaldoDetail extends Model
{
    use HasFactory;

    protected $table = 'mut_cut_pcs_tmp_detail';

    protected $guarded = [];

    public $timestamps = false;
}
