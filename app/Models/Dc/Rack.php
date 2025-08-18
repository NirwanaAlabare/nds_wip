<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    use HasFactory;

    protected $table = "rack";

    protected $guarded = [];

    /**
     * Get the rack details.
     */
    public function rackDetails() {
        return $this->hasMany(RackDetail::class, 'rack_id', 'id');
    }
}
