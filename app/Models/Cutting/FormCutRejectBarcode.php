<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutRejectBarcode extends Model
{
    use HasFactory;

    protected $table = 'form_cut_reject_barcode';

    protected $guarded = [];

    public function formCutReject()
    {
        return $this->belongsTo(FormCutReject::class, 'form_id', 'id');
    }
}
