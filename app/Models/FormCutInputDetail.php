<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScopeDetail;

class FormCutInputDetail extends Model
{
    use HasFactory;

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
}
