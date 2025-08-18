<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFormCutDetailLap extends Model
{
    use HasFactory;

    protected $table = "manual_form_cut_input_detail_lap";

    protected $guarded = [];
}
