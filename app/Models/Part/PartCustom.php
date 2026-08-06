<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PartCustom extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    protected $table = 'part_custom';

    protected $guarded = [];

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id', 'id');
    }

    public function partDetail()
    {
        return $this->belongsTo(PartDetail::class, 'part_detail_id', 'id');
    }
}
