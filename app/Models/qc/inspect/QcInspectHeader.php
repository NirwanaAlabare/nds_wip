<?php

namespace App\Models\qc\inspect;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcInspectHeader extends Model
{
    use HasFactory;
    protected $connection = 'mysql_sb';
    protected $table = 'qc_inspect_list_inspect';
    protected $fillable = [
        'id_whs_lokasi_inmaterial',
        'tgl_pl',
        'no_pl',
        'no_lot',
        'color',
        'supplier',
        'buyer',
        'style',
        'qty_roll',
        'notes',
        'id_item'
    ];

    public function imaterialBarcode()
    {
        return $this->belongsTo(InmaterialLokasi::class, 'id_whs_lokasi_inmaterial', 'id');
    }
}
