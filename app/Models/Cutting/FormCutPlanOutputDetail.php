<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class FormCutPlanOutputDetail extends Model
{
    use HasFactory;

    protected $table = 'cutting_plan_output_form';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function formCutPlanOutput()
    {
        return $this->belongsTo(FormCutPlanOutput::class, 'cutting_plan_id', 'id');
    }

    public function formCut()
    {
        return $this->hasOne(FormCut::class, 'no_form', 'no_form');
    }
}
