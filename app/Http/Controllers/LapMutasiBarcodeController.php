<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutBarcode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \avadim\FastExcelLaravel\Excel as FastExcel;
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

            if ($request->dateFrom >= '2025-11-01') {
               $data_mutasi = DB::connection('mysql_sb')->select("WITH 
buyer as (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier ms on ms.id_supplier = ac.id_buyer group by id_jo),

saldo_awal as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, c.kpno, c.styleno, a.id_item, b.itemdesc, no_roll, '' no_roll_buyer, no_lot, satuan, qty, 0 qty_in  from whs_sa_fabric_copy a INNER JOIN masteritem b on b.id_item = a.id_item INNER JOIN  buyer c on c.id_jo=a.id_jo where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $request->dateFrom . "') GROUP BY no_barcode, kode_lok),

in_before as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $request->dateFrom . "') and tgl_dok < '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $request->dateFrom . "') and tgl_mut < '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok),

in_act as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
),

out_before as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $request->dateFrom . "') and tgl_bppb < '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $request->dateFrom . "') and tgl_mut < '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

out_act as (select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateFrom . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

pemasukan as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sum(COALESCE(qty,0)) qty_awal, sum(COALESCE(qty_in,0)) qty_in from (SELECT * FROM saldo_awal
UNION ALL
SELECT * FROM in_before
UNION ALL
SELECT * FROM in_act) a GROUP BY no_barcode, kode_lok),

pengeluaran as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out_bfr,0)) qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from (SELECT * FROM out_before
UNION ALL
SELECT * FROM out_act) a GROUP BY id_roll, no_rak)

select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, kpno, styleno, a.id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, qty_awal sal_awal, qty_in, COALESCE(qty_out_bfr,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (qty_awal + qty_in - COALESCE(qty_out_bfr,0) - COALESCE(qty_out,0)) sal_akhir from pemasukan a left join pengeluaran b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok");
            }else{
            $data_mutasi = DB::connection('mysql_sb')->select("select * from (select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (
                select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY no_barcode
                UNION
                select 'SA' id, id idnya, supplier, no_dok, tgl_dok,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,satuan from whs_sa_fabric_copy where tgl_periode = DATE_FORMAT('" . $request->dateFrom . "', '%Y-%m-01') GROUP BY no_barcode
                UNION
                select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det and b.kode_lok = a.rak_tujuan where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' GROUP BY no_barcode
                ) a GROUP BY no_barcode) a LEFT JOIN
                (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (
                select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
                UNION
                select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
                UNION
                select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y'
            ) a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0) a where no_barcode NOT IN ('391191','394292','F25704','F28526')");
        }


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
            DB::connection('mysql_sb')->insert("WITH 
buyer as (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier ms on ms.id_supplier = ac.id_buyer group by id_jo),

saldo_awal as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, c.kpno, c.styleno, a.id_item, b.itemdesc, no_roll, '' no_roll_buyer, no_lot, satuan, qty, 0 qty_in  from whs_sa_fabric_copy a INNER JOIN masteritem b on b.id_item = a.id_item INNER JOIN  buyer c on c.id_jo=a.id_jo where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') GROUP BY no_barcode, kode_lok),

in_before as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_aktual) qty, 0 qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_dok < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_aktual) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok),

in_act as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_aktual) qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_aktual) qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
),

out_before as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_bppb < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

out_act as (select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

pemasukan as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sum(COALESCE(qty,0)) qty_awal, sum(COALESCE(qty_in,0)) qty_in from (SELECT * FROM saldo_awal
UNION ALL
SELECT * FROM in_before
UNION ALL
SELECT * FROM in_act) a GROUP BY no_barcode, kode_lok),

pengeluaran as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out_bfr,0)) qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from (SELECT * FROM out_before
UNION ALL
SELECT * FROM out_act) a GROUP BY id_roll, no_rak),

mutasi as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, kpno, styleno, a.id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, qty_awal sal_awal, qty_in, COALESCE(qty_out_bfr,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (qty_awal + qty_in - COALESCE(qty_out_bfr,0) - COALESCE(qty_out,0)) sal_akhir from pemasukan a left join pengeluaran b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok)

insert into whs_sa_fabric_copy  select '', no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, itemdesc, no_lot, no_roll, satuan, sal_akhir, kpno, styleno, itemdesc, DATE_FORMAT(DATE_ADD('" . $from . "', INTERVAL 1 MONTH), '%Y-%m-01') from mutasi where (sal_awal + qty_in) > 0");

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


    // public function export_excel_mut_barcode(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutBarcode($request->from, $request->to), 'Laporan_mutasi_barcode_fabric.xlsx');
    // }

//     public function export_excel_mut_barcode(Request $request)
// {
//     $from = $request->from;
//     $to   = $request->to;

//     // ===========================
//     // Ambil data (perbaikan: gunakan DB::select untuk CTE)
//     // ===========================
//     if ($from >= '2025-11-01') {
//         $sql = "WITH 
// buyer as (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier ms on ms.id_supplier = ac.id_buyer group by id_jo),

// saldo_awal as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, c.kpno, c.styleno, a.id_item, b.itemdesc, no_roll, '' no_roll_buyer, no_lot, satuan, qty, 0 qty_in  from whs_sa_fabric_copy a INNER JOIN masteritem b on b.id_item = a.id_item INNER JOIN  buyer c on c.id_jo=a.id_jo where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') GROUP BY no_barcode, kode_lok),

// in_before as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_aktual) qty, 0 qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_dok < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
// UNION ALL
// select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_aktual) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok),

// in_act as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_aktual) qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
// UNION ALL
// select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_aktual) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
// ),

// out_before as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_bppb < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
// UNION ALL
// select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
// ),

// out_act as (select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
// UNION ALL
// select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
// ),

// pemasukan as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sum(COALESCE(qty,0)) qty_awal, sum(COALESCE(qty_in,0)) qty_in from (SELECT * FROM saldo_awal
// UNION ALL
// SELECT * FROM in_before
// UNION ALL
// SELECT * FROM in_act) a GROUP BY no_barcode, kode_lok),

// pengeluaran as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out_bfr,0)) qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from (SELECT * FROM out_before
// UNION ALL
// SELECT * FROM out_act) a GROUP BY id_roll, no_rak),

// mutasi as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, kpno, styleno, a.id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, qty_awal sal_awal, qty_in, COALESCE(qty_out_bfr,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (qty_awal + qty_in - COALESCE(qty_out_bfr,0) - COALESCE(qty_out,0)) sal_akhir from pemasukan a left join pengeluaran b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok)

// select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sal_awal, qty_in, qty_out_sbl, qty_out, sal_akhir from mutasi where (sal_awal + qty_in) > 0";

//         $rawData = DB::connection('mysql_sb')->select(DB::raw($sql));
//     } else {
//         // untuk branch lama, tetap gunakan select sehingga hasil konsisten
//         $sql = " (
//         select * from (select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (
//                 select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '".$from."' and '$to' GROUP BY no_barcode
//                 UNION
//                 select 'SA' id, id idnya, supplier, no_dok, tgl_dok,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,satuan from whs_sa_fabric_copy where tgl_periode = DATE_FORMAT('".$from."', '%Y-%m-01') GROUP BY no_barcode
//                 UNION
//                 select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det and b.kode_lok = a.rak_tujuan where tgl_mut BETWEEN '".$from."' and '$to' and a.status = 'Y' GROUP BY no_barcode
//                 ) a GROUP BY no_barcode) a LEFT JOIN
//                 (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (
//                 select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '".$from."' and '$to' and a.status = 'Y'
//                 UNION
//                 select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '".$from."' and '$to' and a.status = 'Y' 
//                 UNION
//                 select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '".$from."' and '$to' and a.status = 'Y'
//             ) a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0) a where no_barcode NOT IN ('391191','394292','F25704','F28526')
//     ) as z";
//         $rawData = DB::connection('mysql_sb')->select(DB::raw($sql));
//     }

//     // agar konsisten: rawData → array of objects
//     $data = collect($rawData);

//     // ===========================
//     // Buat Spreadsheet
//     // ===========================
//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     // Judul align kiri
//     $sheet->mergeCells('A1:Q1');
//     $sheet->setCellValue('A1', 'Laporan Mutasi Barcode');
//     $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
//     $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

//     // Periode align kiri
//     $sheet->mergeCells('A2:Q2');
//     $sheet->setCellValue('A2', "Periode $from s/d $to");
//     $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

//     // Header (tambahkan No sebagai kolom pertama)
//     $headers = [
//         "No", "No Barcode", "No BPB", "Tgl BPB", "Supplier", "Lokasi", "Id JO",
//         "No WS", "Style", "Id Item", "Nama Barang", "No Roll", "No Lot",
//         "Satuan", "Saldo Awal", "Pemasukan", "Pengeluaran", "Saldo Akhir"
//     ];
//     $sheet->fromArray($headers, null, 'A3');

//     // Data array (tambahkan nomor urut)
//     $rows = [];
//     $no = 1;
//     foreach($data as $row){
//         $rows[] = [
//             $no++,
//             $row->no_barcode ?? '',
//             $row->no_dok ?? '',
//             $row->tgl_dok ?? '',
//             $row->supplier ?? '',
//             $row->kode_lok ?? '',
//             $row->id_jo ?? '',
//             $row->kpno ?? '',
//             $row->styleno ?? '',
//             $row->id_item ?? '',
//             $row->itemdesc ?? '',
//             $row->no_roll ?? '',
//             $row->no_lot ?? '',
//             $row->satuan ?? '',
//             $row->sal_awal ?? 0,
//             $row->qty_in ?? 0,
//             $row->qty_out ?? 0,
//             $row->sal_akhir ?? 0,
//         ];
//     }
//     // tulis semua data sekaligus → jauh lebih cepat
//     $sheet->fromArray($rows, null, 'A4');

//     // Border
//     $lastRow = count($rows) + 3;
//     $sheet->getStyle("A3:R$lastRow")
//         ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

//     // Autosize kolom (A..R karena sekarang 18 kolom)
//     foreach(range('A', 'R') as $col){
//         $sheet->getColumnDimension($col)->setAutoSize(true);
//     }

//     // ===========================
//     // Download
//     // ===========================
//     $fileName = "Laporan_Mutasi_Barcode_{$from}_sampai_{$to}.xlsx";

//     // bersihkan output buffer jika ada
//     if (ob_get_length()) {
//         ob_end_clean();
//     }

//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header("Content-Disposition: attachment; filename=\"$fileName\"");
//     header('Cache-Control: max-age=0');

//     $writer = new Xlsx($spreadsheet);
//     $writer->save('php://output');
//     exit;
// }



    public function export_excel_mut_barcode(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "
        WITH 
buyer as (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier ms on ms.id_supplier = ac.id_buyer group by id_jo),

saldo_awal as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, c.kpno, c.styleno, a.id_item, b.itemdesc, no_roll, '' no_roll_buyer, no_lot, satuan, qty, 0 qty_in  from whs_sa_fabric_copy a INNER JOIN masteritem b on b.id_item = a.id_item INNER JOIN  buyer c on c.id_jo=a.id_jo where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') GROUP BY no_barcode, kode_lok),

in_before as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_dok < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok),

in_act as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
),

out_before as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_bppb < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" .$from. "') and tgl_mut < '" .$from. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

out_act as (select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut BETWEEN '" .$from. "' and '" .$to. "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

pemasukan as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sum(COALESCE(qty,0)) qty_awal, sum(COALESCE(qty_in,0)) qty_in from (SELECT * FROM saldo_awal
UNION ALL
SELECT * FROM in_before
UNION ALL
SELECT * FROM in_act) a GROUP BY no_barcode, kode_lok),

pengeluaran as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out_bfr,0)) qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from (SELECT * FROM out_before
UNION ALL
SELECT * FROM out_act) a GROUP BY id_roll, no_rak),

mutasi as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, kpno, styleno, a.id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, qty_awal sal_awal, qty_in, COALESCE(qty_out_bfr,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (qty_awal + qty_in - COALESCE(qty_out_bfr,0) - COALESCE(qty_out,0)) sal_akhir from pemasukan a left join pengeluaran b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok)

select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sal_awal, qty_in, qty_out_sbl, qty_out, sal_akhir from mutasi where (sal_awal + qty_in) > 0
    ";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('MutasiBarcode');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Mutasi Barcode'])->applyFontStyleBold();
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:S1');


    // HEADER
    $sheet->writeRow([
        'No Barcode', 'No BPB', 'Tgl BPB', 'Supplier', 'Buyer', 'Lokasi', 'Id JO',
        'No WS', 'Style', 'Id Item', 'Nama Barang', 'No Roll', 'No Roll Buyer',
        'No Lot', 'Satuan', 'Saldo Awal', 'Pemasukan', 'Pengeluaran', 'Saldo Akhir'
    ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
    $sheet->mergeCells('A2:S2');
    // DATA
    $maxLen = [];

foreach ($rows as $r) {
    $rowData = [
        $r['no_barcode'] ?? '',
        $r['no_dok'] ?? '',
        $r['tgl_dok'] ?? '',
        $r['supplier'] ?? '',
        $r['buyer'] ?? '',
       ($r['kode_lok'] ?? '') . ' FABRIC WAREHOUSE RACK',
        $r['id_jo'] ?? '',
        $r['kpno'] ?? '',
        $r['styleno'] ?? '',
        $r['id_item'] ?? '',
        $r['itemdesc'] ?? '',
        $r['no_roll'] ?? '',
        $r['no_roll_buyer'] ?? '',
        $r['no_lot'] ?? '',
        $r['satuan'] ?? '',
        round($r['sal_awal'] ?? 0, 2),
        round($r['qty_in'] ?? 0, 2),
        round($r['qty_out'] ?? 0, 2),
        round($r['sal_akhir'] ?? 0, 2),
    ];

    foreach ($rowData as $i => $v) {
        $len = strlen((string)$v);
        $maxLen[$i] = max($maxLen[$i] ?? 0, $len);
    }

    $sheet->writeRow($rowData)
          ->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
}

// Setelah semua row ditulis → atur width sesuai panjang isi
foreach ($maxLen as $i => $len) {
    // +3 space tampilan
    $sheet->setColWidth($i + 1, $len + 3);
}


    // DOWNLOAD
    $filename = "Mutasi_Barcode_{$from}_sd_{$to}.xlsx";
    return $excel->download($filename);
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
