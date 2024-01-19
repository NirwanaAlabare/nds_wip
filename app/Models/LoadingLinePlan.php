<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\UserLine;

class LoadingLinePlan extends Model
{
    use HasFactory;

    protected $table = "loading_line_plan";

    protected $guarded = [];

    /**
     * Get the userline.
     */
    public function userLine() {
        return $this->belongsTo(UserLine::class, 'line_id', 'line_id');
    }

    public function loadingLines() {
        return $this->hasMany(LoadingLine::class, 'loading_plan_id', 'id');
    }
}
