<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLogHistory extends Model
{
    protected $table = 'activity_log_history';

    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
    ];
}