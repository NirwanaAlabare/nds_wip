<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLine extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = "userpassword";

    protected $guarded = [];
}
