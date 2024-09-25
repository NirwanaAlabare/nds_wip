<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class CutPlanOutputForm extends Model
{
    use HasFactory;

    protected $table = 'cutting_plan_output_form';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function cutPlanOutput()
    {
        return $this->belongsTo(CutPlanOutput::class, 'cutting_plan_id', 'id');
    }

    public function formCutInput()
    {
        return $this->hasOne(FormCutInput::class, 'no_form', 'no_form');
    }
}
