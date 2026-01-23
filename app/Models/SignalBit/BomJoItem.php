<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJoItem extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'bom_jo_item';

    protected $fillable = [];
}
