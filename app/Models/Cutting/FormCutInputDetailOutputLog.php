<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Cutting\FormCutInput;
use App\Models\Marker\FormCutInputDetail;
use Spatie\Activitylog\Traits\LogsActivity;

class FormCutInputDetailOutputLog extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'form_cut_input_detail_output_logs';

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];

    protected static function boot()
    {
        parent::boot();
    }

    public function fromFormCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_input_id_asal', 'id');
    }

    public function toFormCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_input_id_tujuan', 'id');
    }
}
