<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ScannedItem extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'scanned_item';

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
