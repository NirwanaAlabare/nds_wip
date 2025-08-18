<?php

namespace App\Models\Stocker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cutting\FormCutInput;

class ModifySizeQty extends Model
{
    use HasFactory;

    protected $table = "modify_size_qty";

    protected $guarded = [];

    /**
     * Get the form.
     */
    public function form()
    {
        return $this->belongsTo(FormCutInput::class, 'no_form', 'no_form');
    }
}
