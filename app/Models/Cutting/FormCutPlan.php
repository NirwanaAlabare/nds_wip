<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class FormCutPlan extends Model
{
    use HasFactory;

    protected $table = 'cutting_plan';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function formCut()
    {
        return $this->hasOne(FormCut::class, 'id', 'form_cut_id');
    }
}
