<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutScrapSize extends Model
{
    use HasFactory;

    protected $table = 'form_cut_scrap_size';

    protected $guarded = [];

    public function formCutScrapPart()
    {
        return $this->belongsTo(FormCutScrapPart::class, 'form_scrap_part_id', 'id');
    }
}
