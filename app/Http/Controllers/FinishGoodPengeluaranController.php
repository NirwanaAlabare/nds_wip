<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT
            a.id,
no_sb,
tgl_pengeluaran,
concat((DATE_FORMAT(a.tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(a.tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(a.tgl_pengeluaran,  '%Y')) tgl_pengeluaran_fix,
group_concat(distinct(po)) list_po,
count(no_carton) tot_karton,
sum(qty) tot_qty,
group_concat(notes) list_notes,
buyer,
invno,
remark,
jenis_dok,
a.created_at,
a.created_by
from fg_fg_out a
where tgl_pengeluaran >='$tgl_awal' and tgl_pengeluaran <= '$tgl_akhir'
group by no_sb
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'finish_good.finish_good_pengeluaran',
            [
                'page' => 'dashboard-finish-good',
                "subPageGroup" => "finish_good_pengeluaran",
                "subPage" => "finish_good_pengeluaran"
            ]
        );
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_buyer = DB::select("SELECT buyer isi, buyer tampil from
        (select id_so_det from ppic_master_so group by po) p
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        group by buyer");

        $data_dok = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil
        from masterpilihan where
         kode_pilihan='Status KB Out' order by nama_pilihan");


        return view('finish_good.create_finish_good_pengeluaran', [
            'page' => 'dashboard-finish-good',
            "subPageGroup" => "finish_good_pengeluaran",
            "subPage" => "finish_good_pengeluaran",
            "data_buyer" => $data_buyer,
            "data_dok" => $data_dok,
            "user" => $user
        ]);
    }

    public function getpo_fg_out(Request $request)
    {
        $user = Auth::user()->name;
        $data_po = DB::select("SELECT p.po isi, p.po tampil
        from fg_fg_in a
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where buyer = '" . $request->cbobuyer . "'
        group by p.po
        order by p.po asc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function getcarton_notes_fg_out(Request $request)
    {
        $user = Auth::user()->name;
        $data_notes = DB::select("SELECT a.notes isi, a.notes tampil
        from fg_fg_in a
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where buyer = '" . $request->cbobuyer . "' and a.po = '" . $request->cbopo . "'
        group by p.po
        order by p.po asc
        ");

        $html = "<option value=''>Pilih Notes</option>";

        foreach ($data_notes as $datanotes) {
            $html .= " <option value='" . $datanotes->isi . "'>" . $datanotes->tampil . "</option> ";
        }

        return $html;
    }

    public function show_number_carton_fg_out(Request $request)
    {
        $datanumber_carton = DB::select("SELECT
        min(no_carton) min ,max(no_carton) max from packing_master_carton
        where po = '$request->cbopo' and notes = '$request->cbonotes'");
        return json_encode($datanumber_carton[0]);
    }


    public function insert_tmp_fg_out(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');

        $buyer = $request->cbobuyer;
        $po = $request->cbopo;
        $notes = $request->cbonotes;
        $ctn_awal = $request->txtctn_awal;
        $ctn_akhir = $request->txtctn_akhir;


        $ins_tmp_fg =  DB::insert("INSERT into fg_fg_out_tmp (id_fg_in,buyer,po,notes,no_carton,created_at,updated_at,created_by)
        select a.id, m.buyer, a.po, a.notes, a.no_carton, '$timestamp','$timestamp','$user' from fg_fg_in a
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        inner join packing_master_carton pc on a.po = pc.po and a.no_carton = pc.no_carton and a.notes = pc.notes
        left join fg_fg_out_tmp b on a.id = b.id_fg_in
        where m.buyer = '$buyer' and a.po = '$po' and a.notes = '$notes' and a.no_carton >= '$ctn_awal' and a.no_carton <= '$ctn_akhir'
        and b.id_fg_in is null and pc.status != 'terkirim'
        order by a.po asc, a.no_carton asc ");
    }


    public function show_det_karton_fg_out(Request $request)
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
        tmp.id
        from fg_fg_out_tmp tmp
        inner join fg_fg_in a on tmp.id_fg_in = a.id
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where tmp.created_by = '$user'
        order by po asc, no_carton asc, color asc, urutan asc
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }

    public function show_summary_karton_fg_out(Request $request)
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
        sum(a.qty) qty,
        m.curr,
        m.price
        from fg_fg_out_tmp tmp
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


    public function show_delete_karton_fg_out(Request $request)
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
        from fg_fg_out_tmp tmp
        inner join fg_fg_in a on tmp.id_fg_in = a.id
        inner join ppic_master_so p on a.id_ppic_master_so = p.id
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where tmp.created_by = '$user'
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }



    public function delete_karton_fg_out(Request $request)
    {
        $timestamp = Carbon::now();
        $user               = Auth::user()->name;

        $JmlArray           = $_POST['cek_data'];

        if ($JmlArray != '') {
            foreach ($JmlArray as $key => $value) {
                if ($value != '') {
                    $id         = $JmlArray[$key]; {
                        $del =  DB::delete("delete from fg_fg_out_tmp where id = '$id'");
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

    public function clear_tmp_fg_out(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $clear_tmp_fg =  DB::insert("delete from fg_fg_out_tmp where created_by = '$user' ");
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
        $bppbno_int = 'FG/OUT/' . $thn_bln_bppbno_int . '/' . $bppbno_int_no_tr_fix;

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
        values('SJ-FG$bppbno','$bppbno_int','$tgl_pengeluaran','$id_item','$id_so_det','$qty','$curr','$price','$user','PCS','$inv','$id_buyer','N','N','$jns_dok','N','$timestamp','N','GRADE A','1','NDS','$id_buyer') ");

                // $update_karton =  DB::update("
                //     update packing_master_carton set status = 'transfer' where po = '$po' and no_carton = '$no_carton' ");

            }
        }

        $insert_fg_out_nds = DB::insert("INSERT into fg_fg_out
        (no_sb,tgl_pengeluaran,buyer,id_ppic_master_so,id_so_det,barcode,qty,po,no_carton,lokasi,notes,id_fg_in,jenis_dok,invno,remark,created_at,updated_at,created_by)
select '$bppbno_int','$tgl_skrg',buyer,id_ppic_master_so,id_so_det,barcode,qty,a.po,a.no_carton,lokasi,a.notes,a.id_fg_in,'$jns_dok','$inv','-','$timestamp','$timestamp','$user'
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
            'page' => 'dashboard-finish-good',
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
}
