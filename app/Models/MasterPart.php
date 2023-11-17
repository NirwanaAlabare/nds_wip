<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPart extends Model
{
    use HasFactory;

    protected $table = "master_part";

    protected $guarded = [];
}
