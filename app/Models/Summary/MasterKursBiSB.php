<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKursBiSB extends Model
{
    use HasFactory;

    protected $connection = "mysql_sb";

    protected $table = 'master_kurs_bi';

    protected $guarded = [];
}
