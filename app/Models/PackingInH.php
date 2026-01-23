<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingInH extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'packing_in_h';

    protected $guarded = [];

    /**
     * Get the marker details for the marker.
     */
    // public function markerDetails()
    // {
    //     return $this->hasMany(MarkerDetail::class, 'marker_id', 'id');
    // }
}