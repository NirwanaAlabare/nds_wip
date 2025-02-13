<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKaryawan extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'master_karyawan';

    protected $guarded = [];

    public function masterJabatan()
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_id', 'id');
    }
}
