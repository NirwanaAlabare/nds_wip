<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipingProcess extends Model
{
    use HasFactory;

    protected $table = "piping_process";

    protected $guarded = [];

    public function masterPiping()
    {
        return $this->belongsTo(MasterPiping::class, 'master_piping_id', 'id');
    }

    public function pipingProcessDetails()
    {
        return $this->hasMany(PipingProcessDetail::class, 'piping_process_id', 'id');
    }

    public function pipingLoadings()
    {
        return $this->hasMany(PipingLoading::class, 'piping_process_id', 'id');
    }
}
