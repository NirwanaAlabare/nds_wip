<?php

namespace App\Models\qc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterResult extends Model
{
    use HasFactory;
    protected $table = 'qc_inspect_master_result';
    protected $fillable = [
        'result'
    ];
}
