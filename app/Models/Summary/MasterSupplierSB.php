<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSupplierSB extends Model
{
    use HasFactory;

    protected $connection = "mysql_sb";

    protected $table = 'mastersupplier';

    protected $primaryKey = 'IdSupplier';
}
