<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSbWs extends Model
{
    use HasFactory;

    protected $table = 'master_sb_ws';

    protected $guarded = [];

    public $timestamps = false;

    public function masterSbWs()
    {
        return $this->hasMany(MarkerDetail::class, 'so_det_id', 'id_so_det');
    }
}
