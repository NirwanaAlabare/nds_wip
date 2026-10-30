<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndoGudangStok extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_undo_gudang_stok';

    protected $fillable = [
        'id',
        'kode_numbering',
        'output_id',
        'so_det_id',
        'reject_out_id',
        'packing_po_id',
        'type',
        'created_by',
        'created_by_username',
        'created_by_line',
        'created_at',
        'updated_at',
        'undo_by',
        'undo_by_nds',
        'undo_at'
    ];

    public function outputGudangStok()
    {
        return $this->hasOne(OutputGudangStok::class, 'id', 'output_id');
    }
}
