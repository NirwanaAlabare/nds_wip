<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DCIn extends Model
{
    use HasFactory;

    protected $table = "dc_in_input";

    protected $guarded = [];
}
