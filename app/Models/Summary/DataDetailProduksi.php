<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataDetailProduksi extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'data_detail_produksi';

    protected $guarded = [];

    public function dataProduksi()
    {
        return $this->belongsTo(DataProduksi::class, 'data_produksi_id', 'id');
    }

    public function dataDetailProduksiDay()
    {
        return $this->hasMany(DataDetailProduksi::class, 'data_detail_produksi_id', 'id');
    }
}
