<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Marker;
use App\Scopes\ThisYearScope;

class FormCut extends Model
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
        return $this->belongsTo(Marker::class, 'marker_id', 'id');
    }

    public function alokasiMeja()
    {
        return $this->belongsTo(User::class, 'meja_id', 'id');
    }

    public function formCutDetails()
    {
        return $this->hasMany(FormCutDetail::class, 'form_cut_id', 'id');
    }

    /**
     * Get the cutting plan for the form cut.
     */
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
}
