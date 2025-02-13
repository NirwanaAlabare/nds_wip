<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActCosting extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'act_costing';

    protected $fillable = [];

    public function masterPlan()
    {
        return $this->hasMany(MasterPlan::class, 'id', 'id_ws');
    }
}
