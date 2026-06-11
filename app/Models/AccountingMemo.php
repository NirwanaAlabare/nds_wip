<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingMemo extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';
    protected $table     = 'accounting_memo';
    protected $guarded   = [];
}
