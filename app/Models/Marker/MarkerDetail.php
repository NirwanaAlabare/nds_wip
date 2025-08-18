<?php

namespace App\Models\Marker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterSbWs;

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

    public function masterSbWs()
    {
        return $this->belongsTo(MasterSbWs::class, 'so_det_id', 'id_so_det');
    }
}
