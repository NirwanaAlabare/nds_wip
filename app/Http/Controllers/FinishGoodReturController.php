<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanFGReturList;
use App\Exports\ExportLaporanFGReturSummary;

class FinishGoodReturController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::connection('mysql_sb')->select("SELECT
a.id,
no_sb,
tgl_pengeluaran,
concat((DATE_FORMAT(a.tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(a.tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(a.tgl_pengeluaran,  '%Y')) tgl_pengeluaran_fix,
a.po,
a.barcode,
ac.kpno,
sd.color,
sd.size,
no_carton,
a.qty,
a.notes,
a.buyer,
invno,
remark,
jenis_dok,
a.created_at,
a.created_by
from laravel_nds.fg_fg_out a
inner join signalbit_erp.so_det sd on a.id_so_det = sd.id
inner join signalbit_erp.so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join laravel_nds.ppic_master_so p on a.id_ppic_master_so = p.id
left join signalbit_erp.master_size_new msn on sd.size = msn.size
where tgl_pengeluaran >='$tgl_awal' and tgl_pengeluaran <= '$tgl_akhir' and a.status = 'RETUR'
order by tgl_pengeluaran desc , po asc, color asc, msn.urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'finish_good.finish_good_retur',
            [
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "finish_good_retur",
                "subPage" => "finish_good_retur"
            ]
        );
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_buyer = DB::select("SELECT buyer isi, buyer tampil from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
inner join (select * from fg_fg_in where status = 'NORMAL' group by id_so_det) f on p.id_so_det = f.id_so_det
group by buyer");

        $data_dok = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil
        from masterpilihan where
         kode_pilihan='Status KB Out' order by nama_pilihan");


        return view('finish_good.create_finish_good_retur', [
            'page' => 'dashboard_finish_good',
            "subPageGroup" => "finish_good_retur",
            "subPage" => "finish_good_retur",
            "data_buyer" => $data_buyer,
            "data_dok" => $data_dok,
            "user" => $user
        ]);
    }

    public function getpo_fg_retur(Request $request)
    {
        $user = Auth::user()->name;
        $data_po = DB::select("SELECT p.po isi, p.po tampil
        from fg_fg_in a
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
				left join fg_fg_out b on a.id = b.id_fg_in
        where m.buyer = '" . $request->cbobuyer . "' and a.status = 'NORMAL' and b.id_fg_in is null
        group by p.po
        order by p.po asc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function getcarton_notes_fg_retur(Request $request)
    {
        $user = Auth::user()->name;
        $data_notes = DB::select("SELECT a.dest isi, a.dest tampil
        from fg_fg_in a
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where buyer = '" . $request->cbobuyer . "' and a.po = '" . $request->cbopo . "' and a.status = 'NORMAL'
        group by a.dest
        order by a.dest asc
        ");

        $html = "<option value=''>Pilih Dest</option>";

        foreach ($data_notes as $datanotes) {
            $html .= " <option value='" . $datanotes->isi . "'>" . $datanotes->tampil . "</option> ";
        }

        return $html;
    }

    public function show_number_carton_fg_retur(Request $request)
    {
        $datanumber_carton = DB::select("SELECT
        min(no_carton) min ,max(no_carton) max from packing_master_packing_list
        where po = '$request->cbopo' and dest = '$request->cbonotes'");
        return json_encode($datanumber_carton[0]);
    }


    public function insert_tmp_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');

        $buyer = $request->cbobuyer;
        $po = $request->cbopo;
        $dest = $request->cbonotes;
        $ctn_awal = $request->txtctn_awal;
        $ctn_akhir = $request->txtctn_akhir;
        $ins_tmp_fg =  DB::insert("INSERT into fg_fg_retur_tmp
        (id_fg_in,buyer,po,notes,no_carton,dest,created_at,updated_at,created_by)
select
b.id id_fg_in,
a.buyer,
a.po,
b.notes,
a.no_carton,
a.dest,
'$timestamp',
'$timestamp',
'$user'
from
(
select
m.buyer,a.po,a.barcode, a.no_carton, a.dest, a.id_so_det, a.qty
from packing_master_packing_list	a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer' and a.po = '$po' and a.dest = '$dest' and cast(a.no_carton as int) >= '$ctn_awal' and cast(a.no_carton as int) <= '$ctn_akhir'
)a
left join
(
select id,po,barcode, no_carton, dest, id_so_det, sum(qty) qty_fg, notes from fg_fg_in where po = '$po' and dest = '$dest' and cast(no_carton as int) >= '$ctn_awal' and cast(no_carton as int) <= '$ctn_akhir' and status = 'NORMAL'
group by no_carton, barcode, po, dest
) b on a.po = b.po and a.barcode = b.barcode and a.no_carton = b.no_carton and a.id_so_det = b.id_so_det
left join
(
select po,barcode, no_carton, dest, id_so_det, sum(qty) qty_fg_out from fg_fg_out where po = '$po' and dest = '$dest' and cast(no_carton as int) >= '$ctn_awal' and cast(no_carton as int) <= '$ctn_akhir' and status = 'NORMAL'
group by no_carton, barcode, po, dest
) c on a.po = c.po and a.barcode = c.barcode and a.no_carton = c.no_carton and a.id_so_det = c.id_so_det
where qty = coalesce(qty_fg,0) and coalesce(qty_fg_out,0) = '0'
order by po asc, no_carton asc ");
    }


    public function show_det_karton_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');

        if ($request->ajax()) {

            $data_preview = DB::select("SELECT
        tmp.po,
        tmp.no_carton,
        m.ws,
        m.color,
        m.size,
        a.qty,
        m.dest,
        tmp.id
        from fg_fg_retur_tmp tmp
        inner join fg_fg_in a on tmp.id_fg_in = a.id
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where tmp.created_by = '$user'
        order by po asc, cast(tmp.no_carton as int) asc, color asc, urutan asc
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }

    public function show_summary_karton_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        if ($request->ajax()) {

            $data_summary = DB::select("SELECT
        m.id_so_det,
        m.ws,
        m.color,
        m.size,
        m.price,
        m.dest,
        sum(a.qty) qty,
        m.curr
        from fg_fg_retur_tmp tmp
        inner join fg_fg_in a on tmp.id_fg_in = a.id
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where tmp.created_by = '$user'
        group by id_so_det
        order by ws asc, color asc, msn.urutan asc

            ");


            return DataTables::of($data_summary)->toJson();
        }
    }


    public function show_delete_karton_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_preview = DB::select("SELECT
        tmp.id,
        tmp.po,
        tmp.no_carton,
        m.ws,
        m.color,
        m.size,
        a.qty,
        tmp.id
        from fg_fg_retur_tmp tmp
        inner join fg_fg_in a on tmp.id_fg_in = a.id
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where tmp.created_by = '$user' and a.status = 'NORMAL'
        order by po asc, cast(tmp.no_carton as int) asc, color asc, urutan asc
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }



    public function delete_karton_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user               = Auth::user()->name;

        $JmlArray           = $_POST['cek_data'];

        if ($JmlArray != '') {
            foreach ($JmlArray as $key => $value) {
                if ($value != '') {
                    $id         = $JmlArray[$key]; {
                        $del =  DB::delete("delete from fg_fg_retur_tmp where id = '$id'");
                    }
                }
            }

            return array(
                "status" => 201,
                "message" => 'Data Sudah di Hapus',
                "additional" => [],
                "redirect" => '',
                "table" => 'datatable_delete',
                "callback" => "dataTableDetKartonReload();dataTableSummaryReload();"
            );
        } else {
            return array(
                "status" => 400,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }

    public function clear_tmp_fg_retur(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $clear_tmp_fg =  DB::insert("delete from fg_fg_retur_tmp where created_by = '$user' ");
    }

    public function store(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $tgl_skrg   = date('Y-m-d');
        $buyer      = $_POST['cbobuyer'];

        $data_buyer = DB::connection('mysql_sb')->select("select * from mastersupplier where supplier = '$buyer' and tipe_sup ='C'");
        $id_buyer   = $data_buyer[0]->Id_Supplier;

        $jns_dok    = $_POST['cbotipe_doc'];
        $inv        = $_POST['txtinv'];

        $update_data_bpbno = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = 'O.FG'");
        $data_bppbno = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = 'O.FG'");
        $bppbno = $data_bppbno[0]->BPBNo;

        $tahun = date('Y', strtotime($timestamp));
        $kode = 'FG-OUT-' . $tahun;
        $update_data_bppbno_int = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = '$kode'");
        $data_bppbno_int = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = '$kode '");
        $bppbno_int_no_tr = $data_bppbno_int[0]->BPBNo;
        $bppbno_int_no_tr_fix = sprintf("%05s", $bppbno_int_no_tr);
        $thn_bln_bppbno_int = date('my', strtotime($timestamp));
        $bppbno_int = 'FG/RO/' . $thn_bln_bppbno_int . '/' . $bppbno_int_no_tr_fix;

        $id_so_detArray         = $_POST['id_so_det'];
        $qtyArray               = $_POST['qty'];
        $priceArray             = $_POST['price'];
        $currArray              = $_POST['curr'];
        $destArray              = $_POST['dest'];
        $tgl_pengeluaran        = date('Y-m-d');
        foreach ($id_so_detArray as $key => $value) {
            $id_so_det      = $id_so_detArray[$key];
            $qty            = $qtyArray[$key];
            $price          = $priceArray[$key];
            $curr           = $currArray[$key];
            $dest          = $destArray[$key]; {

                $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                $id_item = $cek_id_item ? $cek_id_item[0]->id_item : null;

                $insert_fg_out_sb =  DB::connection('mysql_sb')->insert("INSERT into
                bppb(bppbno,bppbno_int,bppbdate,id_item,id_so_det,qty,curr,price,username,unit,invno,id_supplier,print,status_retur,jenis_dok,confirm,dateinput,cancel,grade,stat_inv,status_input,id_buyer,remark)
        values('SJ-FG$bppbno','$bppbno_int','$tgl_pengeluaran','$id_item','$id_so_det','$qty','$curr','$price','$user','PCS','$inv','1384','N','Y','$jns_dok','N','$timestamp','N','GRADE A','0','NDS','1384','RETUR') ");

                // $update_karton =  DB::update("
                //     update packing_master_carton set status = 'transfer' where po = '$po' and no_carton = '$no_carton' ");

            }
        }

        $insert_fg_out_nds = DB::insert("INSERT into fg_fg_out
        (no_sb,tgl_pengeluaran,buyer,id_ppic_master_so,id_so_det,barcode,qty,po,no_carton,lokasi,notes,dest,id_fg_in,jenis_dok,invno,remark,status,created_at,updated_at,created_by)
select '$bppbno_int','$tgl_skrg',buyer,id_ppic_master_so,id_so_det,barcode,qty,a.po,a.no_carton,lokasi,a.notes,'$dest',a.id_fg_in,'$jns_dok','$inv','-','RETUR','$timestamp','$timestamp','$user'
from fg_fg_retur_tmp a
inner join fg_fg_in b on a.id_fg_in = b.id
where a.created_by = '$user'");

        $update_fg_in =  DB::update("UPDATE fg_fg_in a
inner join fg_fg_retur_tmp b on a.po = b.po and a.no_carton = b.no_carton
set a.status = 'RETUR'
where b.created_by = '$user'");

        $ins_history =  DB::insert("
insert into packing_packing_out_scan_log (id_packing_Packing_out_scan, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by)
SELECT p.id, p.tgl_trans, p.barcode, p.po, p.no_carton,p.created_at, p.updated_at, p.created_by  FROM `packing_packing_out_scan` p
INNER JOIN fg_fg_retur_tmp tmp ON p.po = tmp.po and p.no_carton = tmp.no_carton and p.notes = tmp.notes
WHERE tmp.created_by ='$user'");

        $delete_fg_in =  DB::delete("DELETE p
FROM packing_packing_out_scan p
INNER JOIN fg_fg_retur_tmp tmp ON p.po = tmp.po and p.no_carton = tmp.no_carton and p.notes = tmp.notes
WHERE tmp.created_by ='$user'");




        if ($insert_fg_out_sb != '') {
            return array(
                "status" => 200,
                "message" => 'No Transaksi :
        ' . $bppbno_int . '
        Sudah Terbuat',
                "additional" => [],
                // "redirect" => url('in-material/upload-lokasi')
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }

    public function edit_fg_out($id)
    {
        $user = Auth::user()->name;

        $data_fg_out = DB::select("SELECT * from fg_fg_out where id = '$id'");
        $buyer      = $data_fg_out[0]->buyer;
        $dok      = $data_fg_out[0]->jenis_dok;
        $inv      = $data_fg_out[0]->invno;
        $no_sb      = $data_fg_out[0]->no_sb;

        $data_dok = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil
        from masterpilihan where
         kode_pilihan='Status KB Out' order by nama_pilihan");


        return view('finish_good.edit_finish_good_pengeluaran', [
            'page' => 'dashboard_finish_good',
            "subPageGroup" => "finish_good_pengeluaran",
            "subPage" => "finish_good_pengeluaran",
            "id" => $id,
            "buyer" => $buyer,
            "data_dok" => $data_dok,
            "dok" => $dok,
            "inv" => $inv,
            "no_sb" => $no_sb,
            "user" => $user
        ]);
    }

    public function show_det_karton_fg_out_terinput(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $bppbno = $request->bppbno;

        if ($request->ajax()) {

            $data_preview = DB::select("select
po,
no_carton,
m.ws,
m.color,
m.size,
a.qty
from fg_fg_out a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
where no_sb = '$bppbno'
order by po asc, no_carton asc, color asc, urutan asc
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }

    public function show_summary_karton_fg_out_terinput(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $bppbno = $request->bppbno;

        if ($request->ajax()) {

            $data_summary = DB::select("SELECT
        m.id_so_det,
        m.ws,
        m.color,
        m.size,
        m.price,
        sum(a.qty) qty,
        m.curr
        from fg_fg_out a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where a.no_sb = '$bppbno'
        group by id_so_det
        order by ws asc, color asc, msn.urutan asc

            ");


            return DataTables::of($data_summary)->toJson();
        }
    }

    public function edit_store_fg_out(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $tgl_skrg   = date('Y-m-d');
        $bppbno     = $_POST['bppbno'];

        $data_sb = DB::connection('mysql_sb')->select("select * from bppb where bppbno_int = '$bppbno'");
        $id_buyer   = $data_sb[0]->id_supplier;
        $jns_dok    = $_POST['cbotipe_doc'];
        $inv        = $_POST['txtinv'];
        $bppbno_int_fix  = $data_sb[0]->bppbno_int;
        $bppbno_fix  = $data_sb[0]->bppbno;

        $id_so_detArray         = $_POST['id_so_det'];
        $qtyArray               = $_POST['qty'];
        $priceArray             = $_POST['price'];
        $currArray              = $_POST['curr'];
        $tgl_pengeluaran        = date('Y-m-d');
        foreach ($id_so_detArray as $key => $value) {
            $id_so_det      = $id_so_detArray[$key];
            $qty            = $qtyArray[$key];
            $price          = $priceArray[$key];
            $curr           = $currArray[$key]; {

                $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                $id_item = $cek_id_item ? $cek_id_item[0]->id_item : null;

                $insert_fg_out_sb =  DB::connection('mysql_sb')->insert("INSERT into
                bppb(bppbno,bppbno_int,bppbdate,id_item,id_so_det,qty,curr,price,username,unit,invno,id_supplier,print,status_retur,jenis_dok,confirm,dateinput,cancel,grade,stat_inv,status_input,id_buyer)
        values('$bppbno_fix','$bppbno_int_fix','$tgl_pengeluaran','$id_item','$id_so_det','$qty','$curr','$price','$user','PCS','$inv','$id_buyer','N','N','$jns_dok','N','$timestamp','N','GRADE A','1','NDS','$id_buyer') ");
            }
        }

        $insert_fg_out_nds = DB::insert("INSERT into fg_fg_out
        (no_sb,tgl_pengeluaran,buyer,id_ppic_master_so,id_so_det,barcode,qty,po,no_carton,lokasi,notes,id_fg_in,jenis_dok,invno,remark,created_at,updated_at,created_by)
select '$bppbno_int_fix','$tgl_skrg',buyer,id_ppic_master_so,id_so_det,barcode,qty,a.po,a.no_carton,lokasi,a.notes,a.id_fg_in,'$jns_dok','$inv','-','$timestamp','$timestamp','$user'
from fg_fg_out_tmp	a
inner join fg_fg_in b on a.id_fg_in = b.id
where a.created_by = '$user'");

        $update_karton =  DB::update("
update packing_master_carton a
inner join fg_fg_out_tmp b on a.po = b.po and a.no_carton = b.no_carton
set a.status = 'terkirim'
where b.created_by = '$user'");

        if ($insert_fg_out_sb != '') {
            return array(
                "status" => 200,
                "message" => 'No Transaksi :
        ' . $bppbno_int_fix . '
        Sudah Terbuat',
                "additional" => [],
                // "redirect" => url('in-material/upload-lokasi')
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }
    public function export_excel_fg_retur_list(Request $request)
    {
        return Excel::download(new ExportLaporanFGReturList($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
    public function export_excel_fg_retur_summary(Request $request)
    {
        return Excel::download(new ExportLaporanFGReturSummary($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
