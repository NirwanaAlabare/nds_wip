<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockerSeparate extends Model
{
    use HasFactory;

    protected $table = 'stocker_separate';

    protected $guarded = [];

    /**
     * Get the separate details.
     */
    public function stockerSeparateDetails()
    {
        return $this->hasMany(StockerSeparateDetail::class, 'separate_id', 'id');
    }
}
