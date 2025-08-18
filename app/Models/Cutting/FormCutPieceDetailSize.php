<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\SoDet;

class FormCutPieceDetailSize extends Model
{
    use HasFactory;

    protected $table = 'form_cut_piece_detail_size';

    protected $guarded = [];

    public function formCutPieceDetail()
    {
        return $this->belongsTo(FormCutPieceDetailSize::class, 'form_detail_id', 'id');
    }

    public function soDet()
    {
        return $this->belongsTo(SoDet::class, 'so_det_id', 'id');
    }
}
