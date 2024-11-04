<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanFGINList;
use App\Exports\ExportLaporanFGINSummary;

class FinishGoodPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-90 days'));
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT
no_sb,
tgl_penerimaan,
concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
a.po,
a.barcode,
buyer,
ws,
color,
size,
a.qty,
m.dest,
a.no_carton,
a.notes,
a.created_at,
a.created_by
from fg_fg_in a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_penerimaan >= '$tgl_awal' and tgl_penerimaan <= '$tgl_akhir' and a.status = 'NORMAL'
order by a.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_po = DB::select("SELECT
concat(a.po,'_',a.dest) isi,
concat(a.po, ' - ',a.dest,' - ', m.buyer) tampil
from packing_master_packing_list a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
group by po
        ");


        return view(
            'finish_good.finish_good_penerimaan',
            [
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "finish_good_penerimaan",
                "subPage" => "finish_good_penerimaan",
                "data_po" => $data_po
            ]
        );
    }

    public function fg_in_getno_carton(Request $request)
    // SELECT
    // concat(a.no_carton,'_',a.notes)  isi,
    // concat(a.no_carton, ' ( ', coalesce(sum(b.total),0) - coalesce(sum(c.qty_fg),0), ' ) ', a.notes) tampil
    // from
    // (select id,po, no_carton, notes, qty_isi from packing_master_carton where po = '" . $request->cbopo . "') a
    // left join (
    // select count(barcode) total, po, barcode, dest, no_carton, notes from packing_packing_out_scan
    // where po = '" . $request->cbopo . "'
    // group by no_carton, po, barcode, dest
    // ) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
    // left join (
    // select sum(qty) qty_fg,po, barcode, no_carton, notes from fg_fg_in where po = '" . $request->cbopo . "' and status = 'NORMAL' group by barcode, po, no_carton, notes ) c
    // on a.po = c.po and a.no_carton = c.no_carton and a.notes = c.notes and b.barcode = c.barcode
    // where
    // (
    // case
    // when a.qty_isi is null then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    // when a.qty_isi = b.total then a.qty_isi - coalesce(c.qty_fg,0) != '0'
    // end
    // )
    // group by a.no_carton
    // order by a.no_carton asc


    // NEW
    // SELECT
    // concat(a.no_carton,'_',a.notes)  isi,
    // concat(a.no_carton, ' ( ', coalesce(sum(b.total),0) - coalesce(sum(c.qty_fg),0), ' ) ', a.notes) tampil
    //  from
    // (select id,po, no_carton, notes, qty_isi from packing_master_carton where po = '" . $request->cbopo . "') a
    // left join (
    // select count(barcode) total, po, barcode, dest, no_carton, notes from packing_packing_out_scan
    // where po = '" . $request->cbopo . "'
    // group by no_carton, po
    // ) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
    // left join (
    // select sum(qty) qty_fg,po, barcode, no_carton, notes from fg_fg_in where po = '" . $request->cbopo . "' and status = 'NORMAL' group by barcode, po, no_carton, notes ) c
    // on a.po = c.po and a.no_carton = c.no_carton and a.notes = c.notes and b.barcode = c.barcode
    // where
    // (
    //      case
    //      when a.qty_isi is null then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    //      when a.qty_isi = b.total then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    //      end
    //     )
    //      group by a.no_carton
    //      order by a.no_carton asc

    //     SELECT
    //     concat(no_carton,'_',notes)  isi,
    //     concat(no_carton, ' ( ', coalesce(sum(tot_scan),0) - coalesce(sum(qty_fg),0), ' ) ', notes) tampil
    //     from (
    //     select
    //     a.barcode, a.po, a.notes, a.no_carton,
    //     m.qty_isi,
    //     e.tot_isi,
    //     sum(tot_scan) tot_scan,
    //     sum(qty_fg) qty_fg
    //     from (
    //     select barcode, po, notes, no_carton, count(barcode)tot_scan, '0' qty_fg
    //     from packing_packing_out_scan where po = '" . $request->cbopo . "'
    //     group by barcode, po, notes, no_carton
    //     union
    //     select barcode, po, notes, no_carton,'0' tot_scan,sum(qty)qty_fg from fg_fg_in
    //     where po = '" . $request->cbopo . "' and status = 'NORMAL'
    //     group by barcode, po, notes, no_carton
    //     ) a
    //     left join (select * from packing_master_carton where po = '" . $request->cbopo . "') m
    //     on a.po = m.po and a.no_carton = m.no_carton and a.notes = m.notes
    //     left join (
    //     select count(barcode)tot_isi, po, notes, no_carton
    //     from packing_packing_out_scan where po = '" . $request->cbopo . "'
    //     group by po, notes, no_carton
    //     ) e
    //     on m.po = e.po and m.no_carton = e.no_carton and m.notes = e.notes
    //     group by barcode, po, notes, no_carton
    //     ) d
    //     where
    //     (
    //          case
    //          when d.qty_isi is null then coalesce(d.tot_scan,0) - coalesce(d.qty_fg,0) >= '1'
    //          when d.qty_isi = d.tot_isi then coalesce(d.tot_scan,0) - coalesce(d.qty_fg,0) >= '1'
    //          end
    //         )
    //             group by po, no_carton
    //  order by no_carton asc


    {
        $po_data_arr = $request->cbopo ? $request->cbopo : null;
        if ($po_data_arr) {
            $cekArray = explode('_', $po_data_arr);
            // Use null coalescing operator to safely assign values
            $po = isset($cekArray[0]) ? $cekArray[0] : null;
            $dest = isset($cekArray[1]) ? $cekArray[1] : null;
        } else {
            // Handle the case where $po_data_arr is null
            $po = null; // or set a default value
            $dest = null; // or set a default value
        }

        $data_no_carton = DB::select("SELECT
a.no_carton isi,
concat(a.no_carton,' (', sum(a.qty), ') ') tampil
FROM
(
select * from packing_master_packing_list where  po = '$po' and dest = '$dest'
) a
left join
(
select po, barcode, dest, no_carton, count(barcode)tot_scan from packing_packing_out_scan a
where po = '$po' and dest = '$dest'
group by po , barcode, dest, no_carton
) b on a.po = b.po and a.barcode = b.barcode and a.dest = b.dest and a.no_carton = b.no_carton
left join
(
select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in
where po = '$po' and dest = '$dest' and status = 'NORMAL'
GROUP BY po, barcode, dest, no_carton
) c on a.po = c.po and a.barcode = c.barcode and a.dest = c.dest and a.no_carton = c.no_carton
group by a.no_carton
having coalesce(sum(qty),0) = coalesce(sum(tot_scan),0) and coalesce(sum(tot_scan),0) - coalesce(sum(tot_fg),0) != '0'
        ");

        // where coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'

        $html = "<option value=''>Pilih No. Carton</option>";

        foreach ($data_no_carton as $datanocarton) {
            $html .= " <option value='" . $datanocarton->isi . "'>" . $datanocarton->tampil . "</option> ";
        }

        return $html;
    }

    public function show_preview_fg_in(Request $request)
    {
        $user = Auth::user()->name;

        $po_data_arr = $request->cbopo ? $request->cbopo : null;

        if ($po_data_arr) {
            $cekArray = explode('_', $po_data_arr);
            // Use null coalescing operator to safely assign values
            $po = isset($cekArray[0]) ? $cekArray[0] : null;
            $dest = isset($cekArray[1]) ? $cekArray[1] : null;
        } else {
            // Handle the case where $po_data_arr is null
            $po = null; // or set a default value
            $dest = null; // or set a default value
        }


        $no_carton = $request->cbo_no_carton;

        if ($request->ajax()) {

            $data_preview = DB::select("SELECT
a.id_so_det,
a.no_carton,
a.barcode,
a.po,
a.dest,
m.color,
m.size,
m.ws,
coalesce(b.tot_scan,0) - coalesce(tot_fg,0) qty,
'PCS' unit,
m.dest,
price,
m.curr,
a.id_ppic_master_so
FROM
(
select * from packing_master_packing_list where  po = '$po' and dest = '$dest' and no_carton = '$no_carton'
) a
left join
(
select po, barcode, dest, no_carton, count(barcode)tot_scan from packing_packing_out_scan a
where po = '$po' and dest = '$dest' and no_carton = '$no_carton'
group by po , barcode, dest, no_carton
) b on a.po = b.po and a.barcode = b.barcode and a.dest = b.dest and a.no_carton = b.no_carton
left join
(
select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in where po = '$po' and dest = '$dest' and no_carton = '$no_carton' and status = 'NORMAL'
) c on a.po = c.po and a.barcode = c.barcode and a.dest = c.dest and a.no_carton = c.no_carton
inner join master_sb_ws m on a.id_so_det = m.id_so_det
group by a.id_so_det
having coalesce(sum(a.qty),0) = coalesce(sum(tot_scan),0) and coalesce(sum(tot_scan),0) - coalesce(sum(tot_fg),0) != '0'
            ");

            return DataTables::of($data_preview)->toJson();
        }
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $no_carton = $request->cbo_no_carton;

        $poArray = explode('_', $_POST['cbopo']);
        $po = $poArray[0];
        $dest = $poArray[1];

        $cek_sb = DB::connection('mysql_sb')->select("select count(id) tot from bpb where bpbdate = '$tgl_skrg'
        and po_fg = '$po' and status_input = 'nds'");
        $data_cek_sb = $cek_sb[0]->tot;

        if ($data_cek_sb == '0') {

            $update_data_bpbno = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = 'fg'");
            $data_bpbno = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = 'fg'");
            $bpbno = $data_bpbno[0]->BPBNo;

            $tahun = date('Y', strtotime($timestamp));
            $kode = 'FG-IN-' . $tahun;
            $update_data_bpbno_int = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = '$kode'");
            $data_bpbno_int = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = '$kode '");
            $bpbno_int_no_tr = $data_bpbno_int[0]->BPBNo;
            $bpbno_int_no_tr_fix = sprintf("%05s", $bpbno_int_no_tr);
            $thn_bln_bpbno_int = date('my', strtotime($timestamp));
            $bpbno_int = 'FG/IN/' . $thn_bln_bpbno_int . '/' . $bpbno_int_no_tr_fix;
        } else {
            $cek_no_sb = DB::connection('mysql_sb')->select("select substring(bpbno,3)bpbno,bpbno_int from bpb where bpbdate = '$tgl_skrg'
            and po_fg = '$po' and status_input = 'nds' limit 1");
            $bpbno = $cek_no_sb[0]->bpbno;
            $bpbno_int = $cek_no_sb[0]->bpbno_int;
        }

        $JmlArray               = $_POST['txtqty'];
        $id_so_detArray         = $_POST['id_so_det'];
        $priceArray             = $_POST['price'];
        $currArray              = $_POST['curr'];
        $id_ppic_master_soArray = $_POST['id_ppic_master_so'];
        $barcodeArray           = $_POST['barcode'];
        $tgl_penerimaan         = date('Y-m-d');

        foreach ($JmlArray as $key => $value) {
            if ($value != '0' && $value != '') {
                $txtqty         = $JmlArray[$key];
                $id_so_det      = $id_so_detArray[$key];
                $price          = $priceArray[$key];
                $curr           = $currArray[$key];
                $id_ppic_master_so         = $id_ppic_master_soArray[$key];
                $barcode          = $barcodeArray[$key]; {
                    $cek = DB::connection('mysql_sb')->select("select count(id_so_det) cek from masterstyle where id_so_det = '$id_so_det'");
                    $cek_data = $cek[0]->cek;
                    if ($cek_data == '0') {
                        $ins_m_style = DB::connection('mysql_sb')->insert("insert into masterstyle
				(Styleno,Buyerno,DelDate,unit,itemname,Color,Size,id_so_det,KPNo,country,goods_code)
				select Styleno,so.Buyerno,DelDate_det,sod.unit,product_item,Color,Size,sod.id,KPNo,sod.dest,product_group from
				so_det sod inner join so on sod.id_so=so.id
				inner join act_costing ac on ac.id=so.id_cost
				inner join masterproduct mp on ac.id_product=mp.id
				where sod.cancel='N' and sod.id='$id_so_det'");
                        $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                        $id_item = $cek_id_item[0]->id_item;
                    } else {
                        $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                        $id_item = $cek_id_item[0]->id_item;
                    }

                    $cek_id_sb = DB::connection('mysql_sb')->select("select id from bpb where bpbdate = '$tgl_skrg'
                    and po_fg = '$po' and status_input = 'nds' and id_so_det = '$id_so_det' and id_item = '$id_item' ");
                    $id_sb = $cek_id_sb ? $cek_id_sb[0]->id : 0;

                    if ($id_sb == '0') {
                        $insert_fg_in_sb =  DB::connection('mysql_sb')->insert("insert into bpb(bpbno,bpbno_int,bpbdate,id_supplier,grade,invno,jenis_dok,id_item,id_so_det,qty,unit,price,curr,username,status_input,po_fg)
                        values('FG$bpbno','$bpbno_int','$tgl_penerimaan','435','GRADE A','-','INHOUSE','$id_item','$id_so_det','$txtqty','PCS','$price','$curr','$user','nds','$po') ");
                    } else {
                        $insert_fg_in_sb =  DB::connection('mysql_sb')->update("update bpb set qty = qty + $txtqty where id = '$id_sb' ");
                    }

                    $update_karton =  DB::update("
                    update packing_master_carton set status = 'transfer' where po = '$po' and no_carton = '$no_carton' ");

                    $insert_fg_in_nds =  DB::insert("
                    insert into fg_fg_in (no_sb,tgl_penerimaan,id_ppic_master_so,id_so_det,barcode,qty,po,no_carton,lokasi,notes,dest,status,created_by,updated_at,created_at)
                    values('$bpbno_int','$tgl_skrg','$id_ppic_master_so','$id_so_det','$barcode','$txtqty','$po','$no_carton','-','-','$dest','NORMAL','$user','$timestamp','$timestamp')
                    ");
                }
            }
        }

        if ($insert_fg_in_sb != '') {
            return array(
                "status" => 201,
                "message" => 'No Transaksi :
                 ' . $bpbno_int . '
                 Sudah Terbuat',
                "additional" => [],
                'table' => 'datatable_preview',
                "callback" => "getno_carton();dataTableReload();"
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }

    public function export_excel_fg_in_list(Request $request)
    {
        return Excel::download(new ExportLaporanFGINList($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
    public function export_excel_fg_in_summary(Request $request)
    {
        return Excel::download(new ExportLaporanFGINSummary($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
