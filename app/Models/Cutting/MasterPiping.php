<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPiping extends Model
{
    use HasFactory;

    protected $table = "master_piping";

    protected $guarded = [];

    public function pipingProcesses()
    {
        return $this->hasMany(PipingProcess::class, 'master_piping_id', 'id');
    }
}
