<?php

namespace App\Models\qc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGroupInspect extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';
    protected $table = 'qc_inspect_master_group_inspect';
    protected $fillable = [
        'group_inspect',
        'name_fabric_group',
        'individu',
        'shipment'
    ];
}
