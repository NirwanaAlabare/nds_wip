<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cutting\FormCutInput;
use App\Models\Stocker\Stocker\Stocker;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PartForm extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'part_form';

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Get the part that own the relation.
     */
    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id', 'id');
    }

    /**
     * Get the form that own the relation.
     */
    public function formCutInput()
    {
        return $this->hasOne(FormCutInput::class, "form_id", "id");
    }

    /**
     * Get stockers.
     */
    public function stockers() {
        return $this->hasMany(Stocker::class, 'stocker_id', 'id');
    }
}
