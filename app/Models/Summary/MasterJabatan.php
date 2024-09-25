<?php

namespace App\Models\Summary;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJabatan extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'master_jabatan';

    protected $guarded = [];

    public function masterKaryawan()
    {
        return $this->hasMany(MasterKaryawan::class, 'jabatan_id', 'id');
    }
}
