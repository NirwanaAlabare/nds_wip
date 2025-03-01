<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutDetailLap extends Model
{
    use HasFactory;

    protected $table = "form_cut_input_detail_lap";

    protected $guarded = [];

    public function formCutDetail()
    {
        return $this->belongsTo(FormCutDetail::class, 'form_cut_input_detail_id', 'id');
    }
}
