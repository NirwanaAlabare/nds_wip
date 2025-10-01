<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputGudangStok extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_gudang_stok';

    protected $fillable = [
        'id',
        'kode_numbering',
        'so_det_id',
        'packing_po_id',
        'created_by',
        'created_by_username',
        'created_by_line',
        'created_at',
        'updated_at',
    ];

    public function packingPo()
    {
        return $this->belongsTo(OutputPackingPo::class, 'packing_po_id', 'id');
    }

    public function rejectOut()
    {
        return $this->belongsTo(RejectOutDetail::class, 'reject_out_id', 'id');
    }
}
