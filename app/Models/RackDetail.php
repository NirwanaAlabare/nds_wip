<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackDetail extends Model
{
    use HasFactory;

    protected $table = "rack_detail";

    protected $guarded = [];

    /**
     * Get the rack that own the details.
     */
    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id', 'id');
    }

    public function rackDetailStockers() {
        return $this->hasMany(RackDetailStocker::class, 'detail_rack_id', 'id');
    }
}
