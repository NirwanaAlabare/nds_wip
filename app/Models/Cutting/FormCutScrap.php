<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutScrap extends Model
{
    use HasFactory;

    protected $table = 'form_cut_scrap';

    protected $guarded = [];

    public function formCutScrapDetails()
    {
        return $this->hasMany(FormCutScrapDetail::class, 'form_scrap_id', 'id');
    }

    // Part yang dipilih di header, berlaku untuk semua roll pada form ini
    public function formCutScrapFormParts()
    {
        return $this->hasMany(FormCutScrapFormPart::class, 'form_scrap_id', 'id');
    }
}
