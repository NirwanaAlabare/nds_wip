<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryInhouse extends Model
{
    use HasFactory;

    protected $table = "secondary_inhouse_input";

    protected $guarded = [];
}
