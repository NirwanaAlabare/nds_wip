<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputSewingLock extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_sewing_lock';

    protected $guarded = [];
}
