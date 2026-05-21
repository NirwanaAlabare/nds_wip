<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SewingOutH extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'sewing_out_h';

    protected $guarded = [];
}
