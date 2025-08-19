<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFormCutLostTime extends Model
{
    use HasFactory;

    protected $table = 'manual_form_cut_input_lost_time';

    protected $guarded = [];
}
