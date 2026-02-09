<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPengeluaranRoll;
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

class LapDetPengeluaranRollController extends Controller
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

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_bppb >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_bppb <= '" . $request->dateTo . "' ";
            }


            $data_pemasukan = DB::connection('mysql_sb')->select("select * from (select br.idws_act,ac.styleno,a.no_bppb,a.tgl_bppb,a.no_req,a.tujuan,b.id_roll no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_out,4) qty_out, b.satuan unit,b.id_item,b.id_jo,ac.kpno ws,goods_code, itemdesc,s.color,s.size,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by, CONCAT(no_rak,' FABRIC WAREHOUSE RACK') rak, b.no_roll_buyer
from whs_bppb_h a 
inner join whs_bppb_det b on b.no_bppb = a.no_bppb
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
left join so on tmpjod.id_so=so.id 
left join act_costing ac on so.id_cost=ac.id  
where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY b.id order by a.no_bppb) a
UNION
select * from (select '' ws_aktual, ac.styleno,a.no_mut no_bppb,a.tgl_mut tgl_bppb,'' no_req,'Mutasi Lokasi' tujuan,b.idbpb_det no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_mutasi,4) qty_out, b.unit,b.id_item,b.id_jo,ac.kpno ws,goods_code, itemdesc,s.color,s.size,a.deskripsi remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by, CONCAT(b.
rak_asal,' FABRIC WAREHOUSE RACK') rak, b.no_roll_buyer
from whs_mut_lokasi_h a 
inner join whs_mut_lokasi b on b.no_mut = a.no_mut
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join so on tmpjod.id_so=so.id 

left join act_costing ac on so.id_cost=ac.id  
where b.status != 'N' and a.status != 'cancel' and a.tgl_mut BETWEEN  '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY b.id order by a.no_mut) a");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pengeluaran.lap_pengeluaran_roll", ["page" => "dashboard-warehouse"]);
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


    // public function export_excel_roll(Request $request)
    // {
    //     return Excel::download(new ExportLaporanPengeluaranRoll($request->from, $request->to), 'Laporan_pengeluaran_fabric_barcode.xlsx');
    // }


    public function export_excel_roll(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "select * from (select br.idws_act,ac.styleno,a.no_bppb,a.tgl_bppb,a.no_req,a.tujuan,b.id_roll no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_out,4) qty_out, b.satuan unit,b.id_item,b.id_jo,ac.kpno ws,goods_code, itemdesc,s.color,s.size,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by, CONCAT(no_rak,' FABRIC WAREHOUSE RACK') rak, b.no_roll_buyer
from whs_bppb_h a 
inner join whs_bppb_det b on b.no_bppb = a.no_bppb
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
left join so on tmpjod.id_so=so.id 
left join act_costing ac on so.id_cost=ac.id  
where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $from . "' and '" . $to . "' GROUP BY b.id order by a.no_bppb) a
UNION
select * from (select '' ws_aktual, ac.styleno,a.no_mut no_bppb,a.tgl_mut tgl_bppb,'' no_req,'Mutasi Lokasi' tujuan,b.idbpb_det no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_mutasi,4) qty_out, b.unit,b.id_item,b.id_jo,ac.kpno ws,goods_code, itemdesc,s.color,s.size,a.deskripsi remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by, CONCAT(b.
rak_asal,' FABRIC WAREHOUSE RACK') rak, b.no_roll_buyer
from whs_mut_lokasi_h a 
inner join whs_mut_lokasi b on b.no_mut = a.no_mut
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join so on tmpjod.id_so=so.id 

left join act_costing ac on so.id_cost=ac.id  
where b.status != 'N' and a.status != 'cancel' and a.tgl_mut BETWEEN  '" . $from . "' and '" . $to . "' GROUP BY b.id order by a.no_mut) a";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('Out Barcode');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Pengeluaran Detail Roll'])->applyFontStyleBold()->applyFontSize(16);
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:X1');
    $sheet->writeRow(['']);


    // HEADER
    $sheet->writeRow([
        'No',
        'No Bppb',
        'Tgl Bppb',
        'No Req',
        'Tujuan',
        'No barcode',
        'No Roll',
        'No Roll Buyer',
        'No Lot',
        'Lokasi',
        'Qty Out',
        'Unit',
        'Id Item',
        'Id Jo',
        'No WS',
        'No WS Aktual',
        'No Style',
        'Kode Barang',
        'Nama Barang',
        'Warna',
        'Ukuran',
        'Keterangan',
        'Nama User',
        'Approve By',
    ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
    $sheet->mergeCells('A2:X2');
    // DATA
    $maxLen = [];
    $no = 1;

foreach ($rows as $r) {
    $rowData = [
        $no++,
        $r['no_bppb'] ?? '',
        $r['tgl_bppb'] ?? '',
        $r['no_req'] ?? '',
        $r['tujuan'] ?? '',
        $r['no_barcode'] ?? '',
        $r['no_roll'] ?? '',
        $r['no_roll_buyer'] ?? '',
        $r['no_lot'] ?? '',
        $r['rak'] ?? '',
        round($r['qty_out'] ?? 0, 2),
        $r['unit'] ?? '',
        $r['id_item'] ?? '',
        $r['id_jo'] ?? '',
        $r['ws'] ?? '',
        $r['idws_act'] ?? '',
        $r['styleno'] ?? '',
        $r['goods_code'] ?? '',
        $r['itemdesc'] ?? '',
        $r['color'] ?? '',
        $r['size'] ?? '',
        $r['remark'] ?? '',
        $r['username'] ?? '',
        $r['confirm_by'] ?? '',
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
    $filename = "Laporan Pengeluaran Detail Roll Dari {$from} sd {$to}.xlsx";
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
