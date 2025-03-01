<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cutting\FormCut;

class Marker extends Model
{
    use HasFactory;

    protected $table = 'marker_input';

    protected $guarded = [];

    /**
     * Get the marker details for the marker.
     */
    public function markerDetails()
    {
        return $this->hasMany(MarkerDetail::class, 'marker_id', 'id');
    }

    public function formCuts()
    {
        return $this->hasMany(FormCut::class, 'marker_id', 'id');
    }
}
