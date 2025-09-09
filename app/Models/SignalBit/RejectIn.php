<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectIn extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_in';

    protected $fillable = [
        'id',
        'reject_id',
        'so_det_id',
        'master_plan_id',
        'line_id',
        'kode_numbering',
        'status',
        'type',
        'output_type',
        'process',
        'grade',
        'reject_type_id',
        'reject_area_id',
        'reject_area_x',
        'reject_area_y',
        'created_by',
        'created_by_username',
        'created_at',
        'updated_at',
        'reworked_at'
    ];

    public function reject()
    {
        return $this->hasOne(Reject::class, 'id', 'reject_id');
    }

    public function rejectPacking()
    {
        return $this->hasOne(RejectPacking::class, 'id', 'reject_id');
    }

    public function rejectInDetail()
    {
        return $this->hasMany(RejectInDetail::class, 'reject_in_id', 'id');
    }
}
