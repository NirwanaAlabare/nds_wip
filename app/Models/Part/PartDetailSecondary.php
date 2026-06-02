<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PartDetailSecondary extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    protected $table = 'part_detail_secondary';

    protected $guarded = [];

    /**
     * Get the part that own the details.
     */
    public function partDetail()
    {
        return $this->belongsTo(Part::class, 'part_detail_id', 'id');
    }

    public function secondary()
    {
        return $this->belongsTo(MasterSecondary::class, 'master_secondary_id', 'id');
    }
}
