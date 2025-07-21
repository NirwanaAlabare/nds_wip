<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutPiece extends Model
{
    use HasFactory;

    protected $table = 'form_cut_piece';

    protected $guarded = [];

    public function formCutPieceDetails()
    {
        return $this->hasMany(FormCutPieceDetail::class, 'form_id', 'id');
    }

    public function formCutPieceDetailSizes()
    {
        return $this->hasManyThrough(
            FormCutPieceDetailSize::class,   // Final model
            FormCutPieceDetail::class,   // Intermediate model
            'form_id',  // Foreign key on form_cut_piece_detail table
            'form_detail_id',     // Foreign key on form_cut_piece_detail_size table
            'id',          // Local key on form_cut_piece table
            'id'           // Local key on form_cut_piece_detail table
        );
    }
}
