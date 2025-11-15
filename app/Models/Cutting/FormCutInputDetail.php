<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScopeDetail;
use App\Models\Traits\HasUuid;

class FormCutInputDetail extends Model
{
    use HasFactory, HasUuid;

    protected $table = "form_cut_input_detail";

    protected $guarded = [];

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
