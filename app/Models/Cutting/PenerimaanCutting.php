<?php

namespace App\Models\Cutting;

use App\Models\Cutting\ScannedItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanCutting extends Model
{
     use HasFactory;

    protected $table = 'penerimaan_cutting';

    protected $guarded = [];
}
