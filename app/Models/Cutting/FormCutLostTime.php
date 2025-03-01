<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutLostTime extends Model
{
    use HasFactory;

    protected $table = 'form_cut_input_lost_time';

    protected $guarded = [];
}
