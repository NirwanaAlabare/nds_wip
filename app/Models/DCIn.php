<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DCIn extends Model
{
    use HasFactory;

    protected $table = "dc_in_input";

    protected $guarded = [];

    /**
     * Get the stocker dc in.
     */
    public function stocker()
    {
        return $this->hasOne(Stocker::class, 'id_qr_stocker', 'id_qr_stocker');
    }
}
