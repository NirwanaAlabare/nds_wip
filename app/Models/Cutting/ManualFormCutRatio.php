<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFormCutRatio extends Model
{
    use HasFactory;

    protected $table = 'manual_form_cut_input_ratio';

    protected $guarded = [];
}
