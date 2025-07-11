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
}
