<?php

namespace App\Models\Marker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cutting\FormCutInput;

class Marker extends Model
{
<<<<<<< HEAD
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
=======
    use HasFactory;
>>>>>>> parent of 1b2bbc43 (Merge branch 'main' of https://github.com/NirwanaAlabare/nds_wip)

    protected $table = 'marker_input';

    protected $guarded = [];

    /**
     * Get the marker details for the marker.
     */
    public function markerDetails()
    {
        return $this->hasMany(MarkerDetail::class, 'marker_id', 'id');
    }

    public function formCutInputs()
    {
        return $this->hasMany(FormCutInput::class, 'id_marker', 'kode');
    }
}
