<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SecondaryInhouse extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected static $logAttributes = ['*'];

    protected $table = "secondary_inhouse_input";

    protected $guarded = [];
}
