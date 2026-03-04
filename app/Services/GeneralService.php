<?php

namespace App\Services;

use App\Models\MasterSbWs;
use Illuminate\Http\Request;
use DB;

class GeneralService
{
    public function updateMasterSbWs()
    {
        ini_set('max_execution_time', 360000);

        $masterSbWsCountBefore = DB::table("master_sb_ws")->count();

        $truncateMasterSbWs = DB::table('master_sb_ws')->truncate();

        $insertSelectMasterSbWs = DB::statement("
            INSERT INTO master_sb_ws (
                id_act_cost, ws, cost_no, tgl_kirim, styleno,
                main_dest, brand, so_no, buyer, id_so_det, dest,
                color, size, qty, price, reff_no,
                styleno_prod, product_group, product_item, curr
            )
            select
                ac.id id_act_cost,
                ac.kpno as ws,
                ac.cost_no,
                ac.deldate tgl_kirim,
                ac.styleno,
                ac.main_dest,
                ac.brand,
                so.so_no,
                mb.supplier buyer,
                sd.id id_so_det,
                sd.dest,
                sd.color,
                sd.size,
                sd.qty,
                sd.price,
                sd.reff_no,
                sd.styleno_prod,
                mp.product_group,
                mp.product_item,
                ac.curr
            from signalbit_erp.jo_det jd
            inner join signalbit_erp.so on jd.id_so = so.id
            inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
            inner join signalbit_erp.so_det sd on so.id = sd.id_so
            inner join signalbit_erp.mastersupplier mb on ac.id_buyer = mb.id_supplier
            left join signalbit_erp.masterproduct mp on ac.id_product = mp.id
            where
                jd.cancel = 'N' and ac.cost_date >= '2019-01-01' and ac.type_ws = 'STD'
            UNION
            select
                ac.id id_act_cost,
                ac.kpno as ws,
                ac.cost_no,
                ac.deldate tgl_kirim,
                ac.styleno,
                ac.main_dest,
                ac.brand,
                so.so_no,
                mb.supplier buyer,
                sd.id id_so_det,
                sd.dest,
                sd.color,
                sd.size,
                sd.qty,
                sd.price,
                sd.reff_no,
                sd.styleno_prod,
                mp.product_group,
                mp.product_item,
                ac.curr
            from
            (select so.*,jd.id_so from signalbit_erp.so left join signalbit_erp.jo_det jd on so.id = jd.id_so where jd.id_so is null) so
            inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
            inner join signalbit_erp.so_det sd on so.id = sd.id_so
            inner join signalbit_erp.mastersupplier mb on ac.id_buyer = mb.id_supplier
            left join signalbit_erp.masterproduct mp on ac.id_product = mp.id
            where
                ac.cost_date >= '2019-01-01' and ac.type_ws = 'STD'
            order by
                tgl_kirim desc, ws asc
        ");

        $masterSbWsCountAfter = DB::table("master_sb_ws")->count();

        return array(
            "deleted" => $masterSbWsCountBefore,
            "inserted" => $masterSbWsCountAfter,
            "updated" => 0,
        );
    }
}
