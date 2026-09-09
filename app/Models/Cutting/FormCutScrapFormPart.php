<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Part\PartDetail;

class FormCutScrapFormPart extends Model
{
    use HasFactory;

    protected $table = 'form_cut_scrap_form_part';

    protected $guarded = [];

    public function formCutScrap()
    {
        return $this->belongsTo(FormCutScrap::class, 'form_scrap_id', 'id');
    }

    public function partDetail()
    {
        return $this->belongsTo(PartDetail::class, 'part_detail_id', 'id');
    }
}
