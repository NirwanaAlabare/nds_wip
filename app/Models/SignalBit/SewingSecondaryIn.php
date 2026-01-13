<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SewingSecondaryIn extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_secondary_in';

    protected $guarded=[];
}
