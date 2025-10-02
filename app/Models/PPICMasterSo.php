<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\RftPacking;

class PPICMasterSo extends Model
{
    use HasFactory;

    protected $table = 'ppic_master_so';

    protected $guarded = [];

    public function outputPacking()
    {
        return $this->hasOne(RftPacking::class, 'so_det_id', 'id_so_det');
    }
}
