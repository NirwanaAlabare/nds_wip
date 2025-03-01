<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class FormCutPlanOutput extends Model
{
    use HasFactory;

    protected $table = 'cutting_plan_output';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function formCutPlan()
    {
        return $this->hasOne(FormCutPlan::class, 'tgl_plan', 'tgl_plan');
    }

    public function meja()
    {
        return $this->belongsTo(User::class, 'no_meja', 'id');
    }

    public function formCuts()
    {
        return $this->hasManyThrough(
            FormCut::class,
            FormCutPlanOutput::class,
            'no_form', // Foreign key on the cutplanoutputform table...
            'no_form', // Foreign key on the FormCut table...
            'id', // Local key on the cutplanoutput table...
            'cutting_plan_id' // Local key on the cutplanoutputform table...
        );
    }
}
