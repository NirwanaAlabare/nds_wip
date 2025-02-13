<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormCutInput;
use App\Models\ScannedItem;

class PipingProcessDetail extends Model
{
    use HasFactory;

    protected $table = "piping_process_detail";

    protected $guarded = [];

    public function pipingProcess()
    {
        return $this->belongsTo(PipingProcess::class, 'piping_process_id', 'id');
    }

    public function formCutInput()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_id', 'id');
    }

    public function scannedItem()
    {
        return $this->belongsTo(ScannedItem::class, 'id_roll', 'id_roll');
    }
}
