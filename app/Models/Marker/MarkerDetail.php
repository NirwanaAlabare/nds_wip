<?php

namespace App\Models\Marker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterSbWs;

class MarkerDetail extends Model
{
<<<<<<< HEAD
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
=======
    use HasFactory;
>>>>>>> parent of e9d21c4b (Merge branch 'main' of https://github.com/NirwanaAlabare/nds_wip)

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
