<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataDetailProduksiDay extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'data_detail_produksi_day';

    protected $guarded = [];

    public function dataDetailProduksi()
    {
        return $this->belongsTo(DataDetailProduksi::class, 'data_detail_produksi_id', 'id');
    }

    public function masterKursBi()
    {
        return $this->belongsTo(MasterKursBi::class, 'kurs_bi_id', 'id');
    }

    public function chief()
    {
        return $this->belongsTo(MasterKaryawan::class, 'chief_enroll_id', 'id');
    }

    public function leader()
    {
        return $this->belongsTo(MasterKaryawan::class, 'leader_enroll_id', 'id');
    }

    public function adm()
    {
        return $this->belongsTo(MasterKaryawan::class, 'adm_enroll_id', 'id');
    }
}
