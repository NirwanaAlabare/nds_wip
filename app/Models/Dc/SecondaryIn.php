<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryIn extends Model
{
    use HasFactory;

    protected $table = "secondary_in_input";

    protected $guarded = [];
}
