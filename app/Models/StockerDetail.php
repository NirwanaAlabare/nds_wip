<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockerDetail extends Model
{
    use HasFactory;

    protected $table = 'stocker_input_detail';

    protected $guarded = [];
}
