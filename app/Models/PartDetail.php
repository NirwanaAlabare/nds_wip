<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartDetail extends Model
{
    use HasFactory;

    protected $table = 'marker_input_detail';

    protected $guarded = [];

    /**
     * Get the part that own the details.
     */
    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id', 'id');
    }
}
