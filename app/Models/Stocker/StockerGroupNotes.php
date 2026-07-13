<?php

namespace App\Models\Stocker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class StockerGroupNotes extends Model
{
    use HasFactory;

    protected $table = 'stocker_group_notes';

    protected $guarded = [];
}
