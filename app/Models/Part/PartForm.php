<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cutting\FormCutInput;
use App\Models\Stocker\Stocker\Stocker;

class PartForm extends Model
{
    use HasFactory;

    protected $table = 'part_form';

    protected $guarded = [];

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
