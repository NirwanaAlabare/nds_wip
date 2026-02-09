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

            ) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select id,kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select 'OMB' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out_sbl from whs_mut_lokasi a inner join (select no_barcode,kode_lok,id_item,id_jo,satuan FROM whs_lokasi_inmaterial GROUP BY no_barcode
            UNION
            select no_barcode,kode_lok,id_item,id_jo,unit satuan FROM whs_sa_fabric GROUP BY no_barcode) b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut < '" . $request->dateFrom . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
            UNION
            select 'OTB' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $request->dateFrom . "' group by no_rak, id_item, id_jo, satuan) a
            UNION
            select id,kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select 'OM' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a inner join (select no_barcode,kode_lok,id_item,id_jo,satuan FROM whs_lokasi_inmaterial GROUP BY no_barcode
            UNION
            select no_barcode,kode_lok,id_item,id_jo,unit satuan FROM whs_sa_fabric GROUP BY no_barcode) b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
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
    $sql = "select * from (select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc, color, size, satuan,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir from (select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok,a.id_jo,no_ws,styleno,buyer,a.id_item,mi.goods_code,mi.itemdesc, mi.color, mi.size ,a.satuan,sal_awal,qty_in,coalesce(qty_out_sbl,'0') qty_out_sbl,coalesce(qty_out,'0') qty_out from (select b.kode_lok,b.id_jo,b.no_ws,b.styleno,b.buyer,b.id_item,b.goods_code,b.itemdesc,b.satuan, sal_awal, qty_in from (select id_item,unit from whs_sa_fabric  group by id_item,unit
            UNION
            select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a left join
            (select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan, sum(sal_awal) sal_awal,sum(qty_in) qty_in from (select 'TR' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_lokasi_inmaterial a 
            inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
            inner join masteritem b on b.id_item = a.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok < '" . $from . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
            UNION
            select 'SAM' id,lk.kode_lok,lk.id_jo,lk.no_ws,jd.styleno,mb.supplier buyer,lk.id_item,b.goods_code,b.itemdesc,lk.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_mut_lokasi_h a 
            inner join whs_lokasi_inmaterial lk on lk.no_dok = a.no_mut
            inner join masteritem b on b.id_item = lk.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on lk.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where lk.status = 'Y' and a.tgl_mut < '" . $from . "' group by lk.kode_lok, lk.id_item, lk.id_jo, lk.satuan
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
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok BETWEEN '" . $from . "' and '" . $to . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
            UNION
            select 'TRM' id,lk.kode_lok,lk.id_jo,lk.no_ws,jd.styleno,mb.supplier buyer,lk.id_item,b.goods_code,b.itemdesc,lk.satuan, '0' sal_awal, sum(qty_sj) qty_in from whs_mut_lokasi_h a 
            inner join whs_lokasi_inmaterial lk on lk.no_dok = a.no_mut
            inner join masteritem b on b.id_item = lk.id_item
            inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on lk.id_jo = jd.id_jo
            inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where lk.status = 'Y' and a.tgl_mut BETWEEN '" . $from . "' and '" . $to . "' group by lk.kode_lok, lk.id_item, lk.id_jo, lk.satuan) a group by a.kode_lok, a.id_item, a.id_jo, a.satuan

            ) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select id,kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select 'OMB' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out_sbl from whs_mut_lokasi a inner join (select no_barcode,kode_lok,id_item,id_jo,satuan FROM whs_lokasi_inmaterial GROUP BY no_barcode
            UNION
            select no_barcode,kode_lok,id_item,id_jo,unit satuan FROM whs_sa_fabric GROUP BY no_barcode) b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut < '" . $from . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
            UNION
            select 'OTB' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $from . "' group by no_rak, id_item, id_jo, satuan) a
            UNION
            select id,kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select 'OM' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a inner join (select no_barcode,kode_lok,id_item,id_jo,satuan FROM whs_lokasi_inmaterial GROUP BY no_barcode
            UNION
            select no_barcode,kode_lok,id_item,id_jo,unit satuan FROM whs_sa_fabric GROUP BY no_barcode) b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut BETWEEN '" . $from . "' and '" . $to . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
            UNION
            select 'OT' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb BETWEEN '" . $from . "' and '" . $to . "' group by no_rak, id_item, id_jo, satuan) a) a group by kode_lok, id_item, id_jo, satuan) b on b.kode_lok = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and b.satuan = a.satuan INNER JOIN masteritem mi on mi.id_item = a.id_item) a) a where (sal_awal + qty_in) > 0";

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
