<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_defects';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'product_type_id',
        'defect_type_id',
        'defect_area_id',
        'defect_area_x',
        'defect_area_y',
        'defect_status',
        'status',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(UserSbWip::class, 'created_by', 'id');
    }

    public function soDet()
    {
        return $this->belongsTo(SoDet::class, 'so_det_id', 'id');
    }

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id', 'id');
    }

    public function defectType()
    {
        return $this->belongsTo(DefectType::class, 'defect_type_id', 'id');
    }

    public function defectArea()
    {
        return $this->belongsTo(DefectArea::class, 'defect_area_id', 'id');
    }

    public function rework()
    {
        return $this->hasOne(Rework::class, 'defect_id', 'id');
    }

    public function scopeWithoutTimestamps()
    {
        $this->timestamps = false;
        return $this;
    }
}
