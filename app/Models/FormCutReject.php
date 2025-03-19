<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutReject extends Model
{
    use HasFactory;

    protected $table = 'form_cut_reject';

    protected $guarded = [];

    public function formCutRejectDetails()
    {
        return $this->hasMany(FormCutRejectDetail::class, 'form_id', 'id');
    }
}
