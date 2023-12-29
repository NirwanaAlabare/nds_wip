<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFormCutDetail extends Model
{
    use HasFactory;

    protected $table = "manual_form_cut_input_detail";

    protected $guarded = [];

    // public function formCutInput()
    // {
    //     return $this->belongsTo(ManualFormCutInput::class, 'no_form_cut_input', 'no_form');
    // }
}
