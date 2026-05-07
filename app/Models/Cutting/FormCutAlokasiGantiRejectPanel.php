<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutAlokasiGantiRejectPanel extends Model
{
    use HasFactory;

    protected $table = "form_cut_alokasi_gr_panel_barcode";

    protected $guarded = [];

    public function formCutInputDetail()
    {
        return $this->belongsTo(FormCutInputDetail::class, 'form_cut_input_detail_id', 'id');
    }
}
