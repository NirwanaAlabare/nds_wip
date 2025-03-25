<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RftPacking extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_rfts_packing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'status',
        'rework_id',
        'created_at',
        'updated_at',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function rework()
    {
        return $this->hasOne(ReworkPacking::class, 'id', 'rework_id');
    }
}
