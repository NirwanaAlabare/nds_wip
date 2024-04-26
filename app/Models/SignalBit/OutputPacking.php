<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputPacking extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_rfts_packing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'no_cut_size',
        'kode_numbering',
        'status',
        'rework_id',
        'created_at',
        'updated_at',
    ];

    public function ppicMasterSo()
    {
        return $this->hasOne(PPICMasterSo::class, 'so_det_id', 'id_so_det');
    }

    public function ppicOutput($line = null) {
        if ($line) {
            return $this->selectRaw("
                    so_det_id isi,
                    concat(ac.kpno,' - ', ac.styleno,' - ', sd.color,' - ', sd.size, ' - > ',count(so_det_id)) tampil
                ")->
                leftJoin("master_plan as mp", "output_rfts_packing.master_plan_id", "=", "mp.id")->
                leftJoin("act_costing as ac", "mp.id_ws", "=", "ac.id")->
                leftJoin("so_det as sd", "sd.id", "=", "output_rfts_packing.so_det_id")->
                leftJoin("master_size_new as msn", "msn.size", "=", "sd.size")->
                groupBy("sd.id")->
                where("sd.id", $this->so_det_id)->
                where("mp.sewing_line", $line);
        }

        return $this->selectRaw("
                so_det_id isi,
                mp.sewing_line,
                concat(ac.kpno,' - ', ac.styleno,' - ', sd.color,' - ', sd.size, ' - > ',count(so_det_id)) tampil
            ")->
            leftJoin("master_plan as mp", "output_rfts_packing.master_plan_id", "=", "mp.id")->
            leftJoin("act_costing as ac", "mp.id_ws", "=", "ac.id")->
            leftJoin("so_det as sd", "sd.id", "=", "output_rfts_packing.so_det_id")->
            leftJoin("master_size_new as msn", "msn.size", "=", "sd.size")->
            groupBy("sd.id")->
            where("sd.id", $this->so_det_id);
    }
}
