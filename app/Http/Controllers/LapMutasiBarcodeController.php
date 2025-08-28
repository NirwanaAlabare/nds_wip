<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutBarcode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use DB;
use QrCode;
use DNS1D;
use PDF;

class LapMutasiBarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_bppb >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_bppb <= '" . $request->dateTo . "' ";
            // }

            // $data_mutasi = DB::connection('mysql_sb')->select("select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY no_barcode
            //     UNION
            //     select 'TB' id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, 0 qty_in, sum(qty_sj) qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok < '" . $request->dateFrom . "' GROUP BY no_barcode
            //     UNION
            //     select 'SA' id, id idnya, '-' supplier, no_bpb, tgl_bpb,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,unit from whs_sa_fabric GROUP BY no_barcode
            //     UNION
            //     select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' GROUP BY no_barcode
            //     UNION
            //     select 'IMB' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_sj qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y' GROUP BY no_barcode) a GROUP BY no_barcode) a LEFT JOIN
            //     (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
            //     UNION
            //     select 'OB' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, 0 qty_out, qty_out qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $request->dateFrom . "' and a.status = 'Y'
            //     UNION
            //     select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
            //     UNION
            //     select 'OMB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, a.qty_mutasi qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y'
            //     UNION
            //     select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
            //     UNION
            //     select 'OMSB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_mutasi qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y') a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0");


            $data_mutasi = DB::connection('mysql_sb')->select("select * from (select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY no_barcode
                UNION
                select 'TB' id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, 0 qty_in, sum(qty_sj) qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok >= DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') and tgl_dok < '" . $request->dateFrom . "' GROUP BY no_barcode
                UNION
                select 'SA' id, id idnya, supplier, no_dok, tgl_dok,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,satuan from whs_sa_fabric_copy where tgl_periode = DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') GROUP BY no_barcode
                UNION
                select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' GROUP BY no_barcode
                UNION
                select 'IMB' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_sj qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') and tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y' GROUP BY no_barcode) a GROUP BY no_barcode) a LEFT JOIN
                (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
                UNION
                select 'OB' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, 0 qty_out, qty_out qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb >= DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') and b.tgl_bppb < '" . $request->dateFrom . "' and a.status = 'Y'
                UNION
                select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' 
                UNION
                select 'OMB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, a.qty_mutasi qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') and tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y'
                UNION
                select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
                UNION
                select 'OMSB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_mutasi qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') and tgl_mut < '" . $request->dateFrom . "' and a.status = 'Y') a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0) a where no_barcode NOT IN ('391191','394292','F25704','F28526')");


return DataTables::of($data_mutasi)->toJson();
}

return view("lap-mutasi-barcode.lap_mutasi_barcode", ["page" => "dashboard-warehouse"]);
}


public function copySaldo(Request $request)
{
    $from = $request->input('from');
    $to   = $request->input('to');
    $tgl_periode = $request->input('tgl_periode');

    if (!$from || !$to || !$tgl_periode) {
        return response()->json([
            'status' => 400,
            'message' => 'Parameter tidak lengkap!'
        ]);
    }

    try {
        DB::beginTransaction();

            // Hapus dulu jika tgl_periode sama
        DB::connection('mysql_sb')->table('whs_sa_fabric_copy')->where('tgl_periode', $tgl_periode)->delete();

            // Insert query asli (tidak diubah sama sekali)
        DB::connection('mysql_sb')->insert("insert into whs_sa_fabric_copy select '', no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, sal_akhir qty, kpno, styleno, itemdesc, DATE_FORMAT(DATE_ADD('" . $from . "', INTERVAL 1 MONTH), '%Y-%m-01') tgl_periode from (select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $from . "' and '" . $to . "' GROUP BY no_barcode
            UNION
            select 'TB' id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, 0 qty_in, sum(qty_sj) qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok >= DATE_FORMAT('" . $from . "', '%Y-%m-01') and tgl_dok < '" . $from . "' GROUP BY no_barcode
            UNION
            select 'SA' id, id idnya, supplier, no_dok, tgl_dok,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,satuan from whs_sa_fabric_copy where tgl_periode = DATE_FORMAT('" . $from . "', '%Y-%m-01') GROUP BY no_barcode
            UNION
            select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut BETWEEN '" . $from . "' and '" . $to . "' and a.status = 'Y' GROUP BY no_barcode
            UNION
            select 'IMB' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_sj qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $from . "', '%Y-%m-01') and tgl_mut < '" . $from . "' and a.status = 'Y' GROUP BY no_barcode) a GROUP BY no_barcode) a LEFT JOIN
            (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $from . "' and '" . $to . "' and a.status = 'Y'
            UNION
            select 'OB' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, 0 qty_out, qty_out qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb >= DATE_FORMAT('" . $from . "', '%Y-%m-01') and b.tgl_bppb < '" . $from . "' and a.status = 'Y'
            UNION
            select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $from . "' and '" . $to . "' and a.status = 'Y' 
            UNION
            select 'OMB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, a.qty_mutasi qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $from . "', '%Y-%m-01') and tgl_mut < '" . $from . "' and a.status = 'Y'
            UNION
            select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $from . "' and '" . $to . "' and a.status = 'Y'
            UNION
            select 'OMSB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_mutasi qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut >= DATE_FORMAT('" . $from . "', '%Y-%m-01') and tgl_mut < '" . $from . "' and a.status = 'Y') a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0) a where no_barcode NOT IN ('391191','394292','F25704','F28526')");

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Saldo berhasil dicopy!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Gagal copy saldo: ' . $e->getMessage()
        ]);
    }
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function export_excel_mut_barcode(Request $request)
    {
        return Excel::download(new ExportLaporanMutBarcode($request->from, $request->to), 'Laporan_mutasi_barcode_fabric.xlsx');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function edit(Stocker $stocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }




}
