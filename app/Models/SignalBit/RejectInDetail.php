<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectInDetail extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_in_detail';

    protected $fillable = [
        'id',
        'reject_in_id',
        'reject_type_id',
        'reject_area_id',
        'reject_area_x',
        'reject_area_y',
        'created_at',
        'updated_at',
    ];

    public function rejectInOut()
    {
        return $this->belongsTo(RejectIn::class, 'reject_in_id', 'id');
    }

    public function rejectInDetailPosition()
    {
        return $this->hasMany(RejectInDetailPosition::class, 'reject_in_detail_id', 'id');
    }
}
