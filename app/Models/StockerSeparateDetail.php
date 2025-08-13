<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockerSeparateDetail extends Model
{
    use HasFactory;

    protected $table = 'stocker_separate_detail';

    protected $guarded = [];

    /**
     * Get the parent.
     */
    public function stockerSeparate()
    {
        return $this->belongsTo(StockerSeparate::class, 'separate_id', 'id');
    }
}
