<?php

namespace App\Models\Stocker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;

class StockerReject extends Model
{
    use HasFactory;

    protected $table = 'stocker_reject';

    protected $guarded = [];

    /**
     * Get the stocker that own the detail.
     */
    public function dcIn()
    {
        return $this->belongsTo(DCIn::class, 'dc_in_id', 'id');
    }

    public function secondaryInhouse()
    {
        return $this->belongsTo(SecondaryInhouse::class, 'secondary_inhouse_id', 'id');
    }

    public function secondaryIn()
    {
        return $this->belongsTo(SecondaryIn::class, 'secondary_in_id', 'id');
    }
}
