<?php

namespace App\Exports\Cutting;

use App\Models\Cutting\PenerimaanCutting;
use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PenerimaanCuttingExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');
    }

    public function view(): View
    {
        $data = PenerimaanCutting::selectRaw("
                penerimaan_cutting.id,
                DATE_FORMAT(penerimaan_cutting.tanggal_terima, '%d/%m/%Y') as tanggal_terima,
                penerimaan_cutting.id_roll AS barcode,
                penerimaan_cutting.created_by_username,
                DATE_FORMAT(penerimaan_cutting.created_at, '%d/%m/%Y %H:%i:%s') as created_at_format,
                whs_bppb_h.no_req,
                whs_bppb_det.no_bppb,
                whs_bppb_h.tgl_bppb AS tanggal_bppb,
                whs_bppb_h.tujuan,
                whs_bppb_h.no_ws,
                whs_bppb_h.no_ws_aktual AS no_ws_act,
                whs_bppb_det.qty_out,
                whs_bppb_det.satuan AS unit,
                penerimaan_cutting.qty_konv,
                penerimaan_cutting.unit_konv,
                whs_bppb_det.no_lot,
                whs_bppb_det.no_roll,
                whs_bppb_det.no_roll_buyer,
                whs_bppb_det.id_item,
                whs_bppb_det.item_desc AS nama_barang,
                buyer_ws.styleno AS style,
                masteritem.color AS warna
            ")
            ->leftJoin('signalbit_erp.whs_bppb_det', 'signalbit_erp.whs_bppb_det.id', '=', 'penerimaan_cutting.whs_bppb_det_id')
            ->leftJoin('signalbit_erp.whs_bppb_h', 'signalbit_erp.whs_bppb_h.no_bppb', '=', 'signalbit_erp.whs_bppb_det.no_bppb')
            ->leftJoin('signalbit_erp.masteritem', 'signalbit_erp.masteritem.id_item', '=', 'signalbit_erp.whs_bppb_det.id_item')
            ->leftJoinSub(
                DB::table('signalbit_erp.act_costing as ac')
                    ->selectRaw('jod.id_jo, ac.kpno AS no_ws, ac.styleno')
                    ->join('signalbit_erp.so as so', 'ac.id', '=', 'so.id_cost')
                    ->join('signalbit_erp.jo_det as jod', 'so.id', '=', 'jod.id_so')
                    ->groupBy('jod.id_jo', 'ac.kpno', 'ac.styleno'),
                'buyer_ws',
                function ($join) {
                    $join->on('buyer_ws.id_jo', '=', 'signalbit_erp.whs_bppb_det.id_jo');
                }
            )
            ->whereBetween('penerimaan_cutting.created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59'
            ])->get();

        return view("cutting.penerimaan-cutting.export-penerimaan-cutting", [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data,
        ]);
    }
}
