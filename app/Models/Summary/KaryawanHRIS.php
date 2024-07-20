<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaryawanHRIS extends Model
{
    use HasFactory;

    protected $connection = 'mysql_hris';

    protected $table = 'employee_atribut';
}
