<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutGlobal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use App\Imports\ImportLokasiMaterial;
use DB;
use QrCode;
use DNS1D;
use PDF;

class LapMutasiGlobalController extends Controller
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

            $data_mutasi = DB::connection('mysql_sb')->select("select id_item,goods_code,itemdesc, color, size, unit,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir from (select id_item,goods_code,itemdesc, color, size, unit,SUM(sal_awal) sal_awal,SUM(qty_in) qty_in,SUM(qty_out_sbl) qty_out_sbl,SUM(qty_out) qty_out,SUM(fil) fil from (select a.id_item,a.goods_code,a.itemdesc, color, size, a.unit,COALESCE(sal_awal,0) sal_awal,COALESCE(qty_in,0) qty_in,COALESCE(qty_out_sbl,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (COALESCE(sal_awal,0) + COALESCE(qty_in,0)) fil from (
            select a.id_item,a.unit,b.goods_code,b.itemdesc, b.color, b.size from (select id_item,unit from whs_sa_fabric  group by id_item,unit
            UNION
            select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a inner join masteritem b on b.id_item = a.id_item group by id_item,unit) a left join
            (select id_item,unit, sum(sal_awal) sal_awal from (select 'tr' id,id_item,unit, sum(qty_good) sal_awal from whs_inmaterial_fabric_det where tgl_dok < '" . $request->dateFrom . "' and status = 'Y' GROUP BY id_item,unit union select 'sa' id,id_item,unit, round(sum(qty),2) sal_awal from whs_sa_fabric GROUP BY id_item,unit) a  GROUP BY id_item,unit) b on b.id_item = a.id_item and b.unit = a.unit left join
            (select id_item,unit, sum(qty_in) qty_in from (select 'T' id,id_item,unit, sum(qty_good) qty_in from whs_inmaterial_fabric_det where tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and status = 'Y' GROUP BY id_item,unit
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_in from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by a.id_item,satuan) a group by id_item,unit) c on c.id_item = a.id_item and c.unit = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $request->dateFrom . "' and a.status = 'Y' GROUP BY id_item,satuan) d on d.id_item = a.id_item and d.satuan = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out from (select 'T' id,id_item,satuan, sum(qty_out) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' GROUP BY id_item,satuan
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by a.id_item,satuan) a group by id_item,satuan
) e on e.id_item = a.id_item and e.satuan = a.unit) a GROUP BY a.id_item,a.unit) a where fil != 0");


            return DataTables::of($data_mutasi)->toJson();
        }

        return view("lap-mutasi-global.lap_mutasi_global", ["page" => "dashboard-warehouse"]);
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


    // public function export_excel_mut_global(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutGlobal($request->from, $request->to), 'Laporan_mutasi_global_fabric.xlsx');
    // }

    public function export_excel_mut_global(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "select id_item,goods_code,itemdesc, color, size, unit,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir from (select id_item,goods_code,itemdesc, color, size, unit,SUM(sal_awal) sal_awal,SUM(qty_in) qty_in,SUM(qty_out_sbl) qty_out_sbl,SUM(qty_out) qty_out,SUM(fil) fil from (select a.id_item,a.goods_code,a.itemdesc, color, size, a.unit,COALESCE(sal_awal,0) sal_awal,COALESCE(qty_in,0) qty_in,COALESCE(qty_out_sbl,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (COALESCE(sal_awal,0) + COALESCE(qty_in,0)) fil from (
            select a.id_item,a.unit,b.goods_code,b.itemdesc, b.color, b.size from (select id_item,unit from whs_sa_fabric  group by id_item,unit
            UNION
            select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a inner join masteritem b on b.id_item = a.id_item group by id_item,unit) a left join
            (select id_item,unit, sum(sal_awal) sal_awal from (select 'tr' id,id_item,unit, sum(qty_good) sal_awal from whs_inmaterial_fabric_det where tgl_dok < '" . $from . "' and status = 'Y' GROUP BY id_item,unit union select 'sa' id,id_item,unit, round(sum(qty),2) sal_awal from whs_sa_fabric GROUP BY id_item,unit) a  GROUP BY id_item,unit) b on b.id_item = a.id_item and b.unit = a.unit left join
            (select id_item,unit, sum(qty_in) qty_in from (select 'T' id,id_item,unit, sum(qty_good) qty_in from whs_inmaterial_fabric_det where tgl_dok BETWEEN '" . $from . "' and '" . $to . "' and status = 'Y' GROUP BY id_item,unit
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_in from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $from . "' and '" . $to . "' group by a.id_item,satuan) a group by id_item,unit) c on c.id_item = a.id_item and c.unit = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $from . "' and a.status = 'Y' GROUP BY id_item,satuan) d on d.id_item = a.id_item and d.satuan = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out from (select 'T' id,id_item,satuan, sum(qty_out) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $from . "' and '" . $to . "' and a.status = 'Y' GROUP BY id_item,satuan
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $from . "' and '" . $to . "' group by a.id_item,satuan) a group by id_item,satuan
) e on e.id_item = a.id_item and e.satuan = a.unit) a GROUP BY a.id_item,a.unit) a where fil != 0";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('Mutasi Global');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Mutasi Global'])->applyFontStyleBold()->applyFontSize(16);
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:K1');
    $sheet->writeRow(['']);


    // HEADER
    $sheet->writeRow([
        'No',
        'Id Item',
        'Kode Barang',
        'Nama Barang',
        'Warna',
        'Ukuran',
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
        $r['id_item'] ?? '',
        $r['goods_code'] ?? '',
        $r['itemdesc'] ?? '',
        $r['color'] ?? '',
        $r['size'] ?? '',
        $r['unit'] ?? '',
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
    $filename = "Laporan Mutasi Global Dari {$from} sd {$to}.xlsx";
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
