<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryInUpdate extends Model
{
    use HasFactory;

    protected $table = "secondary_in_update";

    protected $guarded = [];

    /**
     * Get the secondary in.
     */
    public function secondaryIn()
    {
        return $this->belongsTo(SecondaryIn::class, 'secondary_in_id', 'id');
    }
}
