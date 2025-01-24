<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutInputDetailLap extends Model
{
    use HasFactory;

    protected $table = "form_cut_input_detail_lap";

    protected $guarded = [];

    public function formCutInputDetail()
    {
        return $this->belongsTo(FormCutInputDetail::class, 'form_cut_input_detail_id', 'id');
    }
}
