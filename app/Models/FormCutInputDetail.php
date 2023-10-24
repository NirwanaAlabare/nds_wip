<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutInputDetail extends Model
{
    use HasFactory;

    protected $table = "form_cut_input_detail";

    protected $guarded = [];

    public function formCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'no_form_cut_input', 'no_form');
    }
}
