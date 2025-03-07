<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutRejectDetail extends Model
{
    use HasFactory;

    protected $table = 'form_cut_reject_detail';

    public function formCutReject()
    {
        return $this->belongsTo(FormCutReject::class, 'form_id', 'id');
    }
}
