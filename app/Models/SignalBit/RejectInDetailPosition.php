<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectInDetailPosition extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_in_detail_position';

    protected $fillable = [
        'id',
        'reject_in_detail_id',
        'reject_area_x',
        'reject_area_y',
        'created_at',
        'updated_at',
    ];

    public function rejectInOutDetail()
    {
        return $this->belongsTo(RejectInDetail::class, 'reject_in_detail_id', 'id');
    }
}
