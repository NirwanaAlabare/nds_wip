<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPlanSB extends Model
{
    use HasFactory;

    protected $connection = "mysql_sb";

    protected $table = "master_plan";
}
