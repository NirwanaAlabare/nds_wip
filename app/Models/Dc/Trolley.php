<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\UserLine;

class Trolley extends Model
{
    use HasFactory;

    protected $table = 'trolley';

    protected $guarded = [];

    public function userLine() {
        return $this->belongsTo(UserLine::class, 'line_id', 'line_id');
    }

    public function trolleyStockers() {
        return $this->hasMany(TrolleyStocker::class, 'trolley_id', 'id');
    }
}
