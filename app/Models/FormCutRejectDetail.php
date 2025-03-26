<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\SoDet;

class FormCutRejectDetail extends Model
{
    use HasFactory;

    protected $table = 'form_cut_reject_detail';

    protected $guarded = [];

    public function formCutReject()
    {
        return $this->belongsTo(FormCutReject::class, 'form_id', 'id');
    }

    public function soDet()
    {
        return $this->belongsTo(SoDet::class, 'form_id', 'id');
    }
}
