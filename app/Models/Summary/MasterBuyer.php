<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBuyer extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'master_buyer';

    protected $guarded = [];
}
