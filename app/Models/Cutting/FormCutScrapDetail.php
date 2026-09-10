<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCutScrapDetail extends Model
{
    use HasFactory;

    protected $table = 'form_cut_scrap_detail';

    protected $guarded = [];

    public function formCutScrapParts()
    {
        return $this->hasMany(FormCutScrapPart::class, 'form_scrap_detail_id', 'id');
    }

    public function formCutScrap()
    {
        return $this->belongsTo(FormCutScrap::class, 'form_scrap_id', 'id');
    }
}
