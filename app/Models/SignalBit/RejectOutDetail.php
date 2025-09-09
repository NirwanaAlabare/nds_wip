<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class RejectOutDetail extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_out_detail';

    protected $fillable = [
        'id',
        'reject_in_id',
        'reject_out_id',
        'created_by',
        'created_by_username',
        'created_at',
        'updated_at',
    ];

    public function rejectIn()
    {
        return $this->belongsTo(RejectIn::class, 'reject_in_id', 'id');
    }

    public function rejectOut()
    {
        return $this->belongsTo(RejectOut::class, 'reject_out_id', 'id');
    }
}
