<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataProduksi extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'data_produksi';

    protected $guarded = [];

    public function masterBuyer()
    {
        return $this->belongsTo(MasterBuyer::class, 'buyer_id', 'id');
    }

    public function dataDetailProduksi()
    {
        return $this->hasMany(DataDetailProduksi::class, 'data_produksi_id', 'id');
    }
}
