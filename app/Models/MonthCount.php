<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthCount extends Model
{
    use HasFactory;

    protected $table = "month_count";

    protected $guarded = [];
}
