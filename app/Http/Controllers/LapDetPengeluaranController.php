<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPengeluaran;
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

class LapDetPengeluaranController extends Controller
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

            // $keywordQuery = "";
            // if ($request->search["value"]) {
            //     $keywordQuery = "
            //         and (
            //             act_costing_ws like '%" . $request->search["value"] . "%' OR
            //             DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
            //         )
            //     ";
            // }

            $data_pemasukan = DB::connection('mysql_sb')->select("select a.*, b.styleno style_aktual, cp.nama_panel,cp.color_gmt from ((select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item, s.id_gen, goods_code, itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,IF(a.jenis_pengeluaran is null,'-',a.jenis_pengeluaran) jenis_pengeluaran, b.id_jo
        from whs_bppb_h a
        inner join whs_bppb_det b on b.no_bppb = a.no_bppb
        inner join masteritem s on b.id_item=s.id_item
        left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo
        left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req
        left join so on tmpjod.id_so=so.id
        left join act_costing ac on so.id_cost=ac.id
        where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY b.id_jo,b.id_item,b.satuan,b.no_bppb order by a.no_bppb)
        UNION
        (select a.no_mut bpbno,'' bppbno_req,a.tgl_mut bpbdate,a.no_invoice invno,'INHOUSE' jenis_dok,right(a.no_aju,6) no_aju,a.tgl_aju, lpad(a.no_daftar,6,'0') bcno,a.tgl_daftar bcdate,'Mutasi Lokasi' supplier,a.id_item, s.id_gen, goods_code, itemdesc,s.color,s.size, qty,'0' qty_good, qty_reject, a.unit,'' berat_bersih,a.deskripsi remark,a.username,a.confirm_by,tmpjo.kpno ws,tmpjo.styleno,a.curr,if(z.tipe_com !='Regular','0',a.price)price, tmpjo.kpno idws_act,'' jenis_trans, 'Mutasi Lokasi', a.id_jo from (select mut.no_ws,a.no_mut,a.tgl_mut,c.type_bc,c.no_aju,c.tgl_aju, c.no_daftar,c.tgl_daftar,GROUP_CONCAT(DISTINCT a.namasupp) supplier, c.no_po,c.no_invoice,a.id_item, sum(qty_mutasi) qty,sum(qty_mutasi) as qty_good,'0' as qty_reject, a.unit,mut.deskripsi,CONCAT(mut.created_by,' (',mut.created_at, ') ') username,CONCAT(mut.approved_by,' (',mut.approved_date, ') ') confirm_by,'IDR' curr,0 price, c.type_pch,a.id_jo from whs_mut_lokasi a
        inner join whs_mut_lokasi_h mut on mut.no_mut = a.no_mut
        left join whs_inmaterial_fabric c on c.no_dok = a.no_bpb
        where a.status = 'Y' GROUP BY a.no_mut,id_item, id_jo,unit) a
        inner join masteritem s on a.id_item=s.id_item
        left join (select no_dok no_mut,id_jo,id_item, GROUP_CONCAT(DISTINCT CONCAT(kode_lok,' FABRIC WAREHOUSE RACK')) rak from whs_lokasi_inmaterial  where status = 'Y' and no_mut is not null group by no_dok) lr on a.no_mut = lr.no_mut
        left join po_header po on po.pono = a.no_po
        left join po_header_draft z on z.id = po.id_draft
        left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo
        where a.tgl_mut BETWEEN  '" . $request->dateFrom . "' and '" . $request->dateTo . "')) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo left join (select id_jo,a.id_item,group_concat(distinct(nama_panel)) nama_panel, group_concat(distinct(color)) color_gmt from bom_jo_item a left join masterpanel mp on a.id_panel = mp.id left join so_det sd on a.id_so_det = sd.id where status = 'M' and a.cancel = 'N' group by id_item, id_jo) cp on a.id_gen = cp.id_item and a.id_jo = cp.id_jo");



//             $data_pemasukan = DB::connection('mysql_sb')->select("select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt
// from whs_bppb_h a
// inner join whs_bppb_det b on b.no_bppb = a.no_bppb
// inner join masteritem s on b.id_item=s.id_item
// left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo
// left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req
// left join so on tmpjod.id_so=so.id
// left join act_costing ac on so.id_cost=ac.id
// left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and b.id_jo = cp.id_jo
// left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and b.id_jo = cc.id_jo
// where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel'  " . $additionalQuery . " and matclass= 'FABRIC' GROUP BY b.id_item,b.no_bppb order by a.no_bppb");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pengeluaran.lap_pengeluaran", ["page" => "dashboard-warehouse"]);
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


    // public function export_excel_pengeluaran(Request $request)
    // {
    //     return Excel::download(new ExportLaporanPengeluaran($request->from, $request->to), 'Laporan_pengeluaran_fabric.xlsx');
    // }

    public function export_excel_pengeluaran(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "select a.*, b.styleno style_aktual, cp.nama_panel,cp.color_gmt from ((select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item, s.id_gen, goods_code, itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,IF(a.jenis_pengeluaran is null,'-',a.jenis_pengeluaran) jenis_pengeluaran, b.id_jo
        from whs_bppb_h a
        inner join whs_bppb_det b on b.no_bppb = a.no_bppb
        inner join masteritem s on b.id_item=s.id_item
        left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo
        left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req
        left join so on tmpjod.id_so=so.id
        left join act_costing ac on so.id_cost=ac.id
        where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $from . "' and '" . $to . "' GROUP BY b.id_jo,b.id_item,b.satuan,b.no_bppb order by a.no_bppb)
        UNION
        (select a.no_mut bpbno,'' bppbno_req,a.tgl_mut bpbdate,a.no_invoice invno,'INHOUSE' jenis_dok,right(a.no_aju,6) no_aju,a.tgl_aju, lpad(a.no_daftar,6,'0') bcno,a.tgl_daftar bcdate,'Mutasi Lokasi' supplier,a.id_item, s.id_gen, goods_code, itemdesc,s.color,s.size, qty,'0' qty_good, qty_reject, a.unit,'' berat_bersih,a.deskripsi remark,a.username,a.confirm_by,tmpjo.kpno ws,tmpjo.styleno,a.curr,if(z.tipe_com !='Regular','0',a.price)price, tmpjo.kpno idws_act,'' jenis_trans, 'Mutasi Lokasi', a.id_jo from (select mut.no_ws,a.no_mut,a.tgl_mut,c.type_bc,c.no_aju,c.tgl_aju, c.no_daftar,c.tgl_daftar,GROUP_CONCAT(DISTINCT a.namasupp) supplier, c.no_po,c.no_invoice,a.id_item, sum(qty_mutasi) qty,sum(qty_mutasi) as qty_good,'0' as qty_reject, a.unit,mut.deskripsi,CONCAT(mut.created_by,' (',mut.created_at, ') ') username,CONCAT(mut.approved_by,' (',mut.approved_date, ') ') confirm_by,'IDR' curr,0 price, c.type_pch,a.id_jo from whs_mut_lokasi a
        inner join whs_mut_lokasi_h mut on mut.no_mut = a.no_mut
        left join whs_inmaterial_fabric c on c.no_dok = a.no_bpb
        where a.status = 'Y' GROUP BY a.no_mut,id_item, id_jo,unit) a
        inner join masteritem s on a.id_item=s.id_item
        left join (select no_dok no_mut,id_jo,id_item, GROUP_CONCAT(DISTINCT CONCAT(kode_lok,' FABRIC WAREHOUSE RACK')) rak from whs_lokasi_inmaterial  where status = 'Y' and no_mut is not null group by no_dok) lr on a.no_mut = lr.no_mut
        left join po_header po on po.pono = a.no_po
        left join po_header_draft z on z.id = po.id_draft
        left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo
        where a.tgl_mut BETWEEN  '" . $from . "' and '" . $to . "')) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo left join (select id_jo,a.id_item,group_concat(distinct(nama_panel)) nama_panel, group_concat(distinct(color)) color_gmt from bom_jo_item a left join masterpanel mp on a.id_panel = mp.id left join so_det sd on a.id_so_det = sd.id where status = 'M' and a.cancel = 'N' group by id_item, id_jo) cp on a.id_gen = cp.id_item and a.id_jo = cp.id_jo";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('Out Item');
    $sheet = $excel->getSheet();

    // Judul (tanpa merge & tanpa style)
    $sheet->writeRow(['Laporan Pengeluaran Detail Item'])->applyFontStyleBold()->applyFontSize(16);
    $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
    $sheet->writeRow([]); // kosong
    $sheet->mergeCells('A1:AF1');
    $sheet->writeRow(['']);


    // HEADER
    $sheet->writeRow([
        'No',
        'No BPPB',
        'No Req',
        'Tgl BPPB',
        'Inv #',
        'Jenis Dok',
        'Jenis Pengeluaran',
        'Nomor Aju',
        'Tgl Aju',
        'Nomor Daftar',
        'Tgl Daftar',
        'Tujuan',
        'Id Item',
        'Kode Barang',
        'Nama Barang',
        'Warna',
        'Ukuran',
        'Qty BPB',
        'Qty Good',
        'Qty Reject',
        'Satuan',
        'Berat Bersih',
        'Keterangan',
        'Nama User',
        'Approve By',
        'WS #',
        'Style #',
        'Curr',
        'Price',
        'Ws Actual',
        'Panel',
        'Color Garment',
    ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
    $sheet->mergeCells('A2:AF2');
    // DATA
    $maxLen = [];
    $no = 1;

foreach ($rows as $r) {
    $rowData = [
        $no++,
        $r['bppbno'] ?? '',
        $r['bppbno_req'] ?? '',
        $r['bppbdate'] ?? '',
        $r['invno'] ?? '',
        $r['jenis_dok'] ?? '',
        $r['jenis_pengeluaran'] ?? '',
        $r['no_aju'] ?? '',
        $r['tanggal_aju'] ?? '',
        $r['bcno'] ?? '',
        $r['bcdate'] ?? '',
        $r['supplier'] ?? '',
        $r['id_item'] ?? '',
        $r['goods_code'] ?? '',
        $r['itemdesc'] ?? '',
        $r['color'] ?? '',
        $r['size'] ?? '',
        round($r['qty'] ?? 0, 2),
        round($r['qty_good'] ?? 0, 2),
        round($r['qty_reject'] ?? 0, 2),
        $r['unit'] ?? '',
        $r['berat_bersih'] ?? '',
        $r['remark'] ?? '',
        $r['username'] ?? '',
        $r['confirm_by'] ?? '',
        $r['ws'] ?? '',
        $r['styleno'] ?? '',
        $r['curr'] ?? '',
        round($r['price'] ?? 0, 2),
        $r['idws_act'] ?? '',
        $r['nama_panel'] ?? '',
        $r['color_gmt'] ?? '',
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
    $filename = "Laporan Pengeluaran Detail Item Dari {$from} sd {$to}.xlsx";
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
