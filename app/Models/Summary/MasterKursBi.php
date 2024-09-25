<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKursBi extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'master_kurs_bi';

    protected $guarded = [];
}
