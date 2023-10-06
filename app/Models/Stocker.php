<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocker extends Model
{
    use HasFactory;

    protected $table = 'stocker_input';

    protected $guarded = [];
}
