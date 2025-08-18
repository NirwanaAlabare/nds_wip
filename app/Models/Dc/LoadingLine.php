<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\UserLine;
use App\Models\Stocker\Stocker;

class LoadingLine extends Model
{
    use HasFactory;

    protected $table = "loading_line";

    protected $guarded = [];

    /**
     * Get the loading plan.
     */
    public function loadingPlan() {
        return $this->belongsTo(LoadingLinePlan::class, 'loading_plan_id', 'id');
    }

    /**
     * Get the userline.
     */
    public function userLine() {
        return $this->belongsTo(UserLine::class, 'line_id', 'line_id');
    }

    /**
     * Get the stocker.
     */
    public function stocker() {
        return $this->belongsTo(Stocker::class, 'stocker_id', 'id');
    }
}
