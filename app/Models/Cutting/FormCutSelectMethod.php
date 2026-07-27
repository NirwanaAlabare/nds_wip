<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class FormCutSelectMethod extends Model
{
    use HasFactory;

    protected $table = 'form_cut_select_method';

    protected $guarded = [];

    /**
     * Get the form cut data.
     */
    public function formCutInput()
    {
        return $this->hasOne(FormCutInput::class, 'id', 'form_cut_id');
    }

    public function formCutPiece()
    {
        return $this->hasOne(FormCutPiece::class, 'id', 'form_piece_id');
    }
}
