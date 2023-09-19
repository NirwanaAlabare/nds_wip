<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkerDetail extends Model
{
    use HasFactory;

    protected $table = 'marker_input_detail';

    protected $guarded = [];

    /**
     * Get the marker that own the details.
     */
    public function marker()
    {
        return $this->belongsTo(Marker::class, 'marker_id', 'id');
    }
}
