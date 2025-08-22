<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piping extends Model
{
    use HasFactory;

    protected $table = 'form_cut_piping';

    protected $guarded = [];

    public function scannedItem()
    {
        return $this->belongsTo(ScannedItem::class, 'id_roll', 'id_roll');
    }
}
