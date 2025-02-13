<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class CutPlanOutput extends Model
{
    use HasFactory;

    protected $table = 'cutting_plan_output';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function cutPlan()
    {
        return $this->hasOne(CutPlan::class, 'tgl_plan', 'tgl_plan');
    }

    public function meja()
    {
        return $this->belongsTo(User::class, 'no_meja', 'id');
    }

    public function formCutInputs()
    {
        return $this->hasManyThrough(
            FormCutInput::class,
            CutPlanOutputForm::class,
            'no_form', // Foreign key on the cutplanoutputform table...
            'no_form', // Foreign key on the formcutinput table...
            'id', // Local key on the cutplanoutput table...
            'cutting_plan_id' // Local key on the cutplanoutputform table...
        );
    }
}
