<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackDetailStocker extends Model
{
    use HasFactory;

    protected $table = "rack_detail_stocker";

    protected $guarded = [];

    /**
     * Get the rack detail.
     */
    public function rackDetail()
    {
        return $this->belongsTo(RackDetail::class, 'rack_detail_id', 'id');
    }

    /**
     * Get the stockers.
     */
    public function stocker()
    {
        return $this->belongsTo(Stocker::class, 'rack_detail_id', 'id');
    }
}
