<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipingProcessDetail extends Model
{
    use HasFactory;

    protected $table = "piping_process_detail";

    protected $guarded = [];

    public function pipingProcess()
    {
        return $this->belongsTo(PipingProcess::class, 'piping_process_id', 'id');
    }
}
