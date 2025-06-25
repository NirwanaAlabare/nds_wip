<?php

namespace App\Models\qc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDefect extends Model
{
    use HasFactory;
    protected $table = 'qc_inspect_master_defect';
    protected $fillable = [
        'critical_defect',
        'point_defect'
    ];
}
