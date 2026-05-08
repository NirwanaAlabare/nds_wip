<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reject extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_rejects';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'kode_numbering',
        'no_cut_size',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'defect_id',
        'reject_type_id',
        'reject_area_id',
        'reject_area_x',
        'reject_area_y',
        'reject_status'
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function scopeWithoutTimestamps()
    {
        $this->timestamps = false;
        return $this;
    }
}
