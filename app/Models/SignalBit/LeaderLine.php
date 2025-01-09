<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderLine extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_leader_line';

    protected $guarded = [];

    public $timestamps = true;

    public function line()
    {
        return $this->belongsTo(UserLine::class, 'line_id', 'line_id');
    }

    public function leader()
    {
        return $this->belongsTo(ActCosting::class, 'id_ws', 'id');
    }
}
