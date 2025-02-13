<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenKaryawan extends Model
{
    use HasFactory;

    protected $connection = 'mysql_hris';

    protected $table = "master_data_absen_kehadiran";

    protected $guarded = [];
}
