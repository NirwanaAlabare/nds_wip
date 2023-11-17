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
    public function partDetail()
    {
        return $this->hasMany(PartDetail::class, 'part_id', 'id');
    }
}
