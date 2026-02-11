<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use QrCode;
use DNS1D;
use PDF;

class LapMutasiDetailController extends Controller
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

            $data_mutasi = DB::connection('mysql_sb')->select("select * from (select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc, color, size, satuan,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir from (select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok,a.id_jo,no_ws,styleno,buyer,a.id_item,mi.goods_code,mi.itemdesc, mi.color, mi.size ,a.satuan,sal_awal,qty_in,coalesce(qty_out_sbl,'0') qty_out_sbl,coalesce(qty_out,'0') qty_out from (select b.kode_lok,b.id_jo,b.no_ws,b.styleno,b.buyer,b.id_item,b.goods_code,b.itemdesc,b.satuan, sal_awal, qty_in from (select id_item,unit from whs_sa_fabric  group by id_item,unit
            UNION
            select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a left join
            (select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan, sum(sal_awal) sal_awal,sum(qty_in) qty_in from (select 'TR' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_lokasi_inmaterial a 
            inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
            inner join masteritem b on b.id_item = a.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok < '" . $request->dateFrom . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
            UNION
            select 'SAM' id,lk.kode_lok,lk.id_jo,lk.no_ws,jd.styleno,mb.supplier buyer,lk.id_item,b.goods_code,b.itemdesc,lk.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_mut_lokasi_h a 
            inner join whs_lokasi_inmaterial lk on lk.no_dok = a.no_mut
            inner join masteritem b on b.id_item = lk.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on lk.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where lk.status = 'Y' and a.tgl_mut < '" . $request->dateFrom . "' group by lk.kode_lok, lk.id_item, lk.id_jo, lk.satuan
            UNION
            select 'SA' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.unit, round(sum(qty),2) sal_awal,'0' qty_in from whs_sa_fabric a
            inner join masteritem b on b.id_item = a.id_item
            left join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_jo order by id_jo asc) jd on a.id_jo = jd.id_jo
            left join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.qty > 0  group by a.kode_lok, a.id_item, a.id_jo, a.unit
            UNION 
            select 'TRI' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan,'0' sal_awal, round(sum(qty_sj),2) qty_in from whs_lokasi_inmaterial a 
            inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
            inner join masteritem b on b.id_item = a.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
            UNION
            select 'TRM' id,lk.kode_lok,lk.id_jo,lk.no_ws,jd.styleno,mb.supplier buyer,lk.id_item,b.goods_code,b.itemdesc,lk.satuan, '0' sal_awal, sum(qty_sj) qty_in from whs_mut_lokasi_h a 
            inner join whs_lokasi_inmaterial lk on lk.no_dok = a.no_mut
            inner join masteritem b on b.id_item = lk.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on lk.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where lk.status = 'Y' and a.tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by lk.kode_lok, lk.id_item, lk.id_jo, lk.satuan) a group by a.kode_lok, a.id_item, a.id_jo, a.satuan

            ) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select id,kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select 'OMB' id,b.no_rak kode_lok,b.id_item,b.id_jo,satuan,sum(b.qty_out) qty_out_sbl from whs_mut_lokasi a inner join (select no_bppb, id_roll,no_rak,id_item,id_jo,satuan, qty_out FROM whs_bppb_det where status = 'Y' and no_bppb like '%MT%' GROUP BY id_roll, no_bppb) b on a.idbpb_det = b.id_roll and a.no_mut = b.no_bppb where a.status = 'Y' and tgl_mut < '" . $request->dateFrom . "' group by b.no_rak,b.id_item,b.id_jo,satuan

            UNION
            select 'OTB' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $request->dateFrom . "' group by no_rak, id_item, id_jo, satuan) a
            UNION
            select id,kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select 'OM' id,b.no_rak kode_lok,b.id_item,b.id_jo,satuan,sum(b.qty_out) qty_out from whs_mut_lokasi a inner join (select no_bppb, id_roll,no_rak,id_item,id_jo,satuan, qty_out FROM whs_bppb_det where status = 'Y' and no_bppb like '%MT%' GROUP BY id_roll, no_bppb) b on a.idbpb_det = b.id_roll and a.no_mut = b.no_bppb where a.status = 'Y' and tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by b.no_rak,b.id_item,b.id_jo,satuan
            UNION
            select 'OT' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by no_rak, id_item, id_jo, satuan) a) a group by kode_lok, id_item, id_jo, satuan) b on b.kode_lok = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and b.satuan = a.satuan INNER JOIN masteritem mi on mi.id_item = a.id_item) a) a where (sal_awal + qty_in) > 0");


return DataTables::of($data_mutasi)->toJson();
}

return view("lap-mutasi-detail.lap_mutasi_detail", ["page" => "dashboard-warehouse"]);
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


    // public function export_excel_mut_detail(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutDetail($request->from, $request->to), 'Laporan_mutasi_detail_fabric.xlsx');
    // }



    public function export_excel_mut_detail(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "WITH 
buyer as (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier ms on ms.id_supplier = ac.id_buyer group by id_jo),

saldo_awal as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, c.kpno, c.styleno, a.id_item, b.itemdesc, no_roll, '' no_roll_buyer, no_lot, satuan, qty, 0 qty_in  from whs_sa_fabric_copy a INNER JOIN masteritem b on b.id_item = a.id_item INNER JOIN  buyer c on c.id_jo=a.id_jo where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $from . "') GROUP BY no_barcode, kode_lok),

in_before as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $from . "') and tgl_dok < '" . $from . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, sum(qty_sj) qty, 0 qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $from . "') and tgl_mut < '" . $from . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok),

in_act as (select b.no_barcode, a.no_dok, a.tgl_dok, supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN  buyer d on d.id_jo=b.id_jo where tgl_dok BETWEEN '" . $from . "' and '" . $to . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
UNION ALL
select b.no_barcode, a.no_mut, a.tgl_mut, 'Mutasi Lokasi' supplier, d.buyer, b.kode_lok, b.id_jo, d.kpno, d.styleno, b.id_item, c.itemdesc, b.no_roll, b.no_roll_buyer, b.no_lot, b.satuan, 0 qty, sum(qty_sj) qty_in from whs_mut_lokasi_h a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_mut INNER JOIN masteritem c on c.id_item = b.id_item INNER JOIN buyer d on d.id_jo=b.id_jo where tgl_mut BETWEEN '" . $from . "' and '" . $to . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY b.no_barcode, b.kode_lok
),

out_before as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $from . "') and tgl_bppb < '" . $from . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out,0)) qty_out_bfr, 0 qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut >= (SELECT MAX(tgl_periode) FROM whs_sa_fabric_copy WHERE tgl_periode <= '" . $from . "') and tgl_mut < '" . $from . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

out_act as (select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '" . $from . "' and '" . $to . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
UNION ALL
select id_roll, no_rak, id_jo, id_item, 0 qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from whs_mut_lokasi_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_mut where tgl_mut BETWEEN '" . $from . "' and '" . $to . "' and a.status != 'Cancel' and b.status = 'Y' GROUP BY id_roll, no_rak
),

pemasukan as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, sum(COALESCE(qty,0)) qty_awal, sum(COALESCE(qty_in,0)) qty_in from (SELECT * FROM saldo_awal
UNION ALL
SELECT * FROM in_before
UNION ALL
SELECT * FROM in_act) a GROUP BY no_barcode, kode_lok),

pengeluaran as (select id_roll, no_rak, id_jo, id_item, sum(COALESCE(qty_out_bfr,0)) qty_out_bfr, sum(COALESCE(qty_out,0)) qty_out from (SELECT * FROM out_before
UNION ALL
SELECT * FROM out_act) a GROUP BY id_roll, no_rak),

mutasi as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, a.id_jo, kpno, styleno, a.id_item, itemdesc, no_roll, no_roll_buyer, no_lot, satuan, qty_awal sal_awal, qty_in, COALESCE(qty_out_bfr,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (qty_awal + qty_in - COALESCE(qty_out_bfr,0) - COALESCE(qty_out,0)) sal_akhir from pemasukan a left join pengeluaran b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok),

mutasi_fix as (select no_barcode, no_dok, tgl_dok, supplier, buyer, kode_lok, id_jo, kpno, styleno, a.id_item, a.itemdesc, mi.color, mi.size, no_roll, no_roll_buyer, no_lot, satuan, sal_awal, qty_in, qty_out_sbl, qty_out, sal_akhir from mutasi a inner join masteritem mi on mi.id_item = a.id_item where (sal_awal + qty_in) > 0)


select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok, id_jo, kpno no_ws, styleno, buyer, a.id_item, b.goods_code, b.itemdesc, b.color, b.size, satuan, sum(sal_awal) sal_awal, sum(qty_in) qty_in, sum(qty_out_sbl) qty_out_sbl, sum(qty_out) qty_out, sum(sal_akhir) sal_akhir from mutasi_fix a INNER JOIN masteritem b on b.id_item = a.id_item group by kode_lok, a.id_item, a.id_jo, satuan";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('Mutasi Detail');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Mutasi Detail'])->applyFontStyleBold()->applyFontSize(16);
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:K1');
    $sheet->writeRow(['']);


    // HEADER
    $sheet->writeRow([
        'No',
        'Lokasi',
        'Id Jo',
        'WS',
        'Style',
        'Buyer',
        'Id Item',
        'Kode Barang',
        'Nama Barang',
        'Color',
        'Size',
        'satuan',
        'Saldo Awal',
        'Pemasukan',
        'Pengeluaran',
        'Saldo Akhir',
    ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
    $sheet->mergeCells('A2:K2');
    // DATA
    $maxLen = [];
    $no = 1;

foreach ($rows as $r) {
    $rowData = [
        $no++,
        $r['kode_lok'] ?? '',
        $r['id_jo'] ?? '',
        $r['no_ws'] ?? '',
        $r['styleno'] ?? '',
        $r['buyer'] ?? '',
        $r['id_item'] ?? '',
        $r['goods_code'] ?? '',
        $r['itemdesc'] ?? '',
        $r['color'] ?? '',
        $r['size'] ?? '',
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
    $filename = "Laporan Mutasi Detail Dari {$from} sd {$to}.xlsx";
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
