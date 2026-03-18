<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SewingSecondaryIn extends Model
{
    use HasFactory, LogsActivity;

    protected $connection = 'mysql_sb';

    protected $table = 'output_secondary_in';

    protected $guarded=[];

    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];
}
