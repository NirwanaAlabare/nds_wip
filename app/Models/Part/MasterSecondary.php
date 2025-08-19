<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSecondary extends Model
{
    use HasFactory;

    protected $table = "master_secondary";

    protected $guarded = [];

    public function partDetails() {
        return $this->hasMany(MasterSecondary::class, 'master_secondary_id', 'id');
    }
}
