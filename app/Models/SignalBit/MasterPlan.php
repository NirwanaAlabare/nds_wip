<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPlan extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'master_plan';

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function userPassword()
    {
        return $this->belongsTo(UserLine::class, 'sewing_line', 'username');
    }

    public function actCosting()
    {
        return $this->belongsTo(ActCosting::class, 'id_ws', 'id');
    }

    // QC Endline
    public function rfts()
    {
        return $this->hasMany(Rft::class, 'master_plan_id', 'id');
    }

    public function defects()
    {
        return $this->hasMany(Defect::class, 'master_plan_id', 'id');
    }

    public function rejects()
    {
        return $this->hasMany(Reject::class, 'master_plan_id', 'id');
    }

    public function reworks()
    {
        return $this->hasMany(Rework::class, 'master_plan_id', 'id');
    }

    // QC Packing
    public function rftsPacking()
    {
        return $this->hasMany(RftPacking::class, 'master_plan_id', 'id');
    }
}
