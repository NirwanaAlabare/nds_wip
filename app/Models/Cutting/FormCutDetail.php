<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScopeDetail;

class FormCutDetail extends Model
{
    use HasFactory;

    protected $table = "form_cut_input_detail";

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ThisYearScopeDetail);
    }

    public function formCut()
    {
        return $this->belongsTo(FormCut::class, 'form_cut_id', 'id');
    }

    public function formCutDetailLaps()
    {
        return $this->hasMany(FormCutDetailLap::class, 'form_cut_input_detail_id', 'id');
    }
}
