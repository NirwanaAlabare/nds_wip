<?php

namespace App\Models\qc\inspect;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\qc\MasterGroupInspect;

class QcInspectDetail extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';
    protected $table = 'qc_inspect_list_inspect_det';

    protected $fillable = [
        'result',
        'rata_rata',
        'id_master_group_inspect',
        'id_inspect_list_header',
        'percentage'
    ];

    public function master_group_inspect()
    {
        return $this->belongsTo(MasterGroupInspect::class, 'id_master_group_inspect', 'id');
    }
    
    public function qc_inspect_header()
    {
        return $this->belongsTo(QcInspectHeader::class, 'id_inspect_list_header', 'id');
    }

}
