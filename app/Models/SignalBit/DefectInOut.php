<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectInOut extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_defect_in_out';

    protected $guarded = [];

    public function defect()
    {
        return $this->hasOne(Defect::class, 'id', 'defect_id');
    }

    public function defectPacking()
    {
        return $this->hasOne(DefectPacking::class, 'id', 'defect_id');
    }
}
