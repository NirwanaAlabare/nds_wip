<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Marker\MarkerDetail;
use App\Models\Cutting\FormCutInput;
use Spatie\Activitylog\Traits\LogsActivity;

class FormCutInputDetailOutput extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'form_cut_input_detail_output';

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];

    protected static function boot()
    {
        parent::boot();
    }

    public function markerDetail()
    {
        return $this->belongsTo(MarkerDetail::class, 'marker_input_detail_id', 'id');
    }

    public function formCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_input_id', 'id');
    }
}
