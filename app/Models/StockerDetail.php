<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockerDetail extends Model
{
    use HasFactory;

    // protected $table = 'stocker_input_detail';
    protected $table = 'stocker_numbering';

    protected $guarded = [];

    /**
     * Get the stocker that own the detail.
     */
    // public function stocker()
    // {
    //     return $this->belongsTo(Stocker::class, 'stocker_id', 'id');
    // }
}
