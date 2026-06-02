<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PartDetail extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    protected $table = 'part_detail';

    protected $guarded = [];

    /**
     * Get the part that own the details.
     */
    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id', 'id');
    }

    public function masterPart()
    {
        return $this->belongsTo(MasterPart::class, 'master_part_id', 'id');
    }

    public function secondary()
    {
        return $this->belongsTo(MasterSecondary::class, 'master_secondary_id', 'id');
    }

    public function secondaries()
    {
        return $this->hasMany(PartDetailSecondary::class, 'part_detail_id', 'id');
    }
}
