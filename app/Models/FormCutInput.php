<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class FormCutInput extends Model
{
    use HasFactory;

    protected $table = 'form_cut_input';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ThisYearScope);
    }

    public function marker()
    {
        return $this->belongsTo(Marker::class, 'id_marker', 'kode');
    }

    public function alokasiMeja()
    {
        return $this->belongsTo(User::class, 'no_meja', 'id');
    }

    public function cuttingPlanOutput()
    {
        return $this->hasOne(CutPlanOutputForm::class, 'id', 'form_cut_id');
    }

    /**
     * Get the cutting plan for the form cut.
     */
    public function cuttingPlan()
    {
        return $this->hasOne(CutPlan::class, 'no_form_cut_input', 'no_form');
    }

    public function formCutInputDetails()
    {
        return $this->hasMany(FormCutInputDetail::class, 'form_cut_id', 'id');
    }
}
