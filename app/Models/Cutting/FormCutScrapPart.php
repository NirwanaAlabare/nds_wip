<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Part\PartDetail;

class FormCutScrapPart extends Model
{
    use HasFactory;

    protected $table = 'form_cut_scrap_part';

    protected $guarded = [];

    public function formCutScrapSizes()
    {
        return $this->hasMany(FormCutScrapSize::class, 'form_scrap_part_id', 'id');
    }

    public function formCutScrapDetail()
    {
        return $this->belongsTo(FormCutScrapDetail::class, 'form_scrap_detail_id', 'id');
    }

    public function partDetail()
    {
        return $this->belongsTo(PartDetail::class, 'part_detail_id', 'id');
    }
}
