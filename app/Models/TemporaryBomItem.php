<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryBomItem extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';
    protected $table = 'temporary_bom_items';
}
