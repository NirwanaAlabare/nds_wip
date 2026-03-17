<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScopeDetail;
use App\Models\Traits\HasUuid;
use Spatie\Activitylog\Traits\LogsActivity;

class FormCutInputDetail extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    protected $table = "form_cut_input_detail";

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ThisYearScopeDetail);
    }

    public function formCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_id', 'id');
    }

    public function formCutInputDetailLaps()
    {
        return $this->hasMany(FormCutInputDetailLap::class, 'form_cut_input_detail_id', 'id');
    }

    public function formCutInputDetailSambungan()
    {
        return $this->hasMany(FormCutInputDetailSambungan::class, 'form_cut_input_detail_id', 'id');
    }
}
