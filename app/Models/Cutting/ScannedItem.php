<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ScannedItem extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'scanned_item';

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];

    public function penerimaanCutting()
    {
        return $this->hasMany(PenerimaanCutting::class, 'id_roll', 'id_roll');
    }
}
