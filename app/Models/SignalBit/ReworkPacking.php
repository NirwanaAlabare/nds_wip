<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReworkPacking extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reworks_packing';

    protected $fillable = [
        'id',
        'defect_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function defect()
    {
        return $this->hasOne(DefectPacking::class, 'id', 'defect_id');
    }

    public function rft()
    {
        return $this->hasOne(RftPacking::class, 'rework_id', 'id');
    }
}
