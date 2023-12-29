<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

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
