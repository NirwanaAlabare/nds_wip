<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterEmployee extends Model
{
    use HasFactory;

    protected $connection = 'mysql_hris';

    protected $table = "employee_atribut";

    protected $fillable = [];
}
