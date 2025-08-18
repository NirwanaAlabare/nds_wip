<?php

namespace App\Models\Stocker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockerAdditionalDetail extends Model
{
    use HasFactory;

    protected $table = 'stocker_ws_additional_detail';

    protected $guarded = [];
}
