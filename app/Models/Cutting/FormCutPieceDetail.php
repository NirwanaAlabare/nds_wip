<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutPieceDetail extends Model
{
    use HasFactory;

    protected $table = 'form_cut_piece_detail';

    protected $guarded = [];

    public function formCutPieceDetailSizes()
    {
        return $this->hasMany(FormCutPieceDetailSize::class, 'form_detail_id', 'id');
    }

    public function formCutPiece()
    {
        return $this->belongsTo(FormCutPiece::class, 'form_id', 'id');
    }

    public function scannedItem()
    {
        return $this->belongsTo(ScannedItem::class, 'id_roll', 'id_roll');
    }
}
