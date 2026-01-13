<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SewingSecondaryMaster extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_secondary_master';

    protected $guarded = [];
}
