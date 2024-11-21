<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadingLineHistory extends Model
{
    use HasFactory;

    protected $table = "loading_line_history";

    protected $guarded = [];
}
