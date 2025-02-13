<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserLine;

class PipingLoading extends Model
{
    use HasFactory;

    protected $table = "piping_loading";

    protected $guarded = [];

    public function pipingProcess()
    {
        return $this->belongsTo(PipingProcess::class, 'piping_process_id', 'id');
    }

    public function sewingLine()
    {
        return $this->belongsTo(UserPassword::class, 'line_id', 'line_id');
    }
}
