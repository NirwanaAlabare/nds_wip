<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPemasukanRoll;
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

class LapDetPemasukanRollController extends Controller
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
                $additionalQuery .= " and a.tgl_dok >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_dok <= '" . $request->dateTo . "' ";
            }


            $data_pemasukan = DB::connection('mysql_sb')->select("select *, IF(rate is null,1,rate) rates, round(price * IF(rate is null,1,rate),2) price_idr, IFNULL(idws_act,'-') ws_aktual from (select a.no_dok,a.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_roll_buyer,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,c.id_item,c.id_jo,kpno no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,no_invoice,no_po,styleno from whs_lokasi_inmaterial c inner join (select no_dok,tgl_dok,supplier,deskripsi,created_by,created_at,approved_by,approved_date,no_invoice,no_po from whs_inmaterial_fabric
        UNION
        select a.no_mut,a.tgl_mut,b.namasupp,a.deskripsi,a.created_by,a.created_at,a.approved_by,a.approved_date,c.no_invoice,no_po from whs_mut_lokasi_h a inner join whs_mut_lokasi b on a .no_mut = b.no_mut left JOIN whs_inmaterial_fabric c on c.no_dok = b.no_bpb GROUP BY a.no_mut) a on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=c.id_jo where c.status = 'Y' and a.tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by c.id) a left join (select no_barcode, curr, price from whs_inmaterial_fabric_det a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok and b.id_jo = a.id_jo and b.id_item = a.id_item where tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY no_barcode) b on b.no_barcode = a.barcode left join (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tgl_dok and cr.curr_rate = b.curr LEFT JOIN (select bppbno, idws_act from bppb_req where bppbdate >= '2025-01-01' and bppbno like '%RQ-F%' and idws_act is not null GROUP BY bppbno) ws on ws.bppbno = a.no_invoice");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pemasukan.lap_pemasukan_roll", ["page" => "dashboard-warehouse"]);
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
    //     return Excel::download(new ExportLaporanPemasukanRoll($request->from, $request->to), 'Laporan_pemasukan_fabric_barcode.xlsx');
    // }


    public function export_excel_roll(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "select *, IF(rate is null,1,rate) rates, round(price * IF(rate is null,1,rate),2) price_idr, IFNULL(idws_act,'-') ws_aktual from (select a.no_dok,a.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_roll_buyer,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,c.id_item,c.id_jo,kpno no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,no_invoice,no_po,styleno from whs_lokasi_inmaterial c inner join (select no_dok,tgl_dok,supplier,deskripsi,created_by,created_at,approved_by,approved_date,no_invoice,no_po from whs_inmaterial_fabric
        UNION
        select a.no_mut,a.tgl_mut,b.namasupp,a.deskripsi,a.created_by,a.created_at,a.approved_by,a.approved_date,c.no_invoice,no_po from whs_mut_lokasi_h a inner join whs_mut_lokasi b on a .no_mut = b.no_mut left JOIN whs_inmaterial_fabric c on c.no_dok = b.no_bpb GROUP BY a.no_mut) a on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=c.id_jo where c.status = 'Y' and a.tgl_dok BETWEEN '" .$from. "' and '" .$to. "' group by c.id) a left join (select no_barcode, curr, price from whs_inmaterial_fabric_det a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok and b.id_jo = a.id_jo and b.id_item = a.id_item where tgl_dok BETWEEN '" .$from. "' and '" .$to. "' GROUP BY no_barcode) b on b.no_barcode = a.barcode left join (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tgl_dok and cr.curr_rate = b.curr LEFT JOIN (select bppbno, idws_act from bppb_req where bppbdate >= '2025-01-01' and bppbno like '%RQ-F%' and idws_act is not null GROUP BY bppbno) ws on ws.bppbno = a.no_invoice";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('In Barcode');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Penerimaan Detail Roll'])->applyFontStyleBold()->applyFontSize(16);
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:AC1');
    $sheet->writeRow(['']);


    // HEADER
    $sheet->writeRow([
        'No',
        'No BPB',
        'Tgl BPB',
        'Supplier',
        'No SJ',
        'No PO',
        'Styleno',
        'Rak',
        'No Barcode',
        'No Roll',
        'No Roll Buyer',
        'No Lot',
        'Qty BPB',
        'Qty Mutasi',
        'Unit',
        'Id Item',
        'Id Jo',
        'No WS',
        'No WS Aktual',
        'Kode Barang',
        'Nama Barang',
        'Warna',
        'Ukuran',
        'Curr',
        'Price',
        'Rate',
        'Price IDR',
        'Keterangan',
        'Nama User',
        'Approve By',
    ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
    $sheet->mergeCells('A2:AC2');
    // DATA
    $maxLen = [];
    $no = 1;

foreach ($rows as $r) {
    $rowData = [
        $no++,
        $r['no_dok'] ?? '',
        $r['tgl_dok'] ?? '',
        $r['supplier'] ?? '',
        $r['no_invoice'] ?? '',
        $r['no_po'] ?? '',
        $r['styleno'] ?? '',
        $r['rak'] ?? '',
        $r['barcode'] ?? '',
        $r['no_roll'] ?? '',
        $r['no_roll_buyer'] ?? '',
        $r['no_lot'] ?? '',
        round($r['qty'] ?? 0, 2),
        round($r['qty_mut'] ?? 0, 2),
        $r['satuan'] ?? '',
        $r['id_item'] ?? '',
        $r['id_jo'] ?? '',
        $r['no_ws'] ?? '',
        $r['ws_aktual'] ?? '',
        $r['goods_code'] ?? '',
        $r['itemdesc'] ?? '',
        $r['color'] ?? '',
        $r['size'] ?? '',
        $r['curr'] ?? '',
        round($r['price'] ?? 0, 2),
        round($r['rates'] ?? 0, 2),
        round($r['price_idr'] ?? 0, 2),
        $r['deskripsi'] ?? '',
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
    $filename = "Laporan Pemasukan Detail Roll Dari {$from} sd {$to}.xlsx";
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
