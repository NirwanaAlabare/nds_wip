<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutPieceDetailSize extends Model
{
    use HasFactory;

    protected $table = 'form_cut_piece_detail_size';

    protected $guarded = [];

    public function formCutPieceDetail()
    {
        return $this->belongsTo(FormCutPieceDetailSize::class, 'form_detail_id', 'id');
    }
}
