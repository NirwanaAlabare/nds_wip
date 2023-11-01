<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutKaryawan extends Model
{
    use HasFactory;

    protected $table = "mut_karyawan_input";

    protected $guarded = [];
}
