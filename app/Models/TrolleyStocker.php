<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrolleyStocker extends Model
{
    use HasFactory;

    protected $table = 'trolley_stocker';

    protected $guarded = [];

    /**
     * Get the stocker that own the detail.
     */
    public function trolley()
    {
        return $this->belongsTo(Trolley::class, 'trolley_id', 'id');
    }

    public function stocker()
    {
        return $this->belongsTo(Stocker::class, 'stocker_id', 'id');
    }
}
