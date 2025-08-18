<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScannedItem extends Model
{
    use HasFactory;

    protected $table = 'scanned_item';

    protected $guarded = [];
}
