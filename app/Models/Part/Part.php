<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Part extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    protected $table = 'part';

    protected $guarded = [];

    /**
     * Get the part details.
     */
    public function partDetails()
    {
        return $this->hasMany(PartDetail::class, 'part_id', 'id');
    }

    public function partForms()
    {
        return $this->hasMany(PartForm::class, 'part_id', 'id');
    }
}
