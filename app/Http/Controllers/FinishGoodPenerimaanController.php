<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                a.line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                a.tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Sewing'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
            union
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                'Temporary' line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                'Packing' tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment_out_temporary a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Temporary'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
								order by created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_po = DB::select("SELECT a.po isi, concat(a.po, ' - ', p.buyer) tampil from
(select * from packing_master_carton where status = 'draft') a
inner join
(
select a.po, m.buyer from ppic_master_so a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
group by po, buyer
 ) p on a.po = p.po
 group by a.po
 order by p.buyer asc, a.po asc
        ");


        return view(
            'finish_good.finish_good_penerimaan',
            [
                'page' => 'dashboard-finish-good',
                "subPageGroup" => "finish_good_penerimaan",
                "subPage" => "finish_good_penerimaan",
                "data_po" => $data_po
            ]
        );
    }

    public function fg_in_getno_carton(Request $request)
    {
        $data_no_carton = DB::select("SELECT
        a.no_carton isi,
        concat(a.no_carton, ' ( ', coalesce(b.total,0), ' )') tampil
        from
        (select id,po, no_carton from packing_master_carton where po = '" . $request->cbopo . "' and status = 'draft') a
        left join (
        select count(barcode) total, po, barcode, dest, no_carton from packing_packing_out_scan
        where po = '" . $request->cbopo . "'
        group by no_carton, po, barcode, dest
        ) b on a.po = b.po and a.no_carton = b.no_carton
        ");

        $html = "<option value=''>Pilih No. Carton</option>";

        foreach ($data_no_carton as $datanocarton) {
            $html .= " <option value='" . $datanocarton->isi . "'>" . $datanocarton->tampil . "</option> ";
        }

        return $html;
    }

    public function show_preview_fg_in(Request $request)
    {
        $user = Auth::user()->name;
        $po = $request->cbopo;
        $cbo_no_carton = $request->cbo_no_carton;
        if ($request->ajax()) {

            $data_preview = DB::select("SELECT
        m.id_so_det,
        a.no_carton,
        p.barcode,
        p.po,
        p.dest,
        m.color,
        m.size,
        m.ws,
        a.qty,
        'PCS' unit,
        price,
        m.curr,
        p.id id_ppic_master_so
        from
        (
        select count(barcode)qty, barcode, po, dest, no_carton from packing_packing_out_scan
        where po = '$po' and no_carton = '$cbo_no_carton'
        group by po, barcode, dest
        ) a
        inner join ppic_master_so p on a.po = p.po and  a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
            ");


            return DataTables::of($data_preview)->toJson();
        }
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $po                     = $_POST['cbopo'];
        $no_carton              = $_POST['cbo_no_carton'];

        $cek_sb = DB::connection('mysql_sb')->select("select count(id) tot from bpb where bpbdate = '$tgl_skrg'
        and po_fg = '$po' and status_input = 'nds' ");
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
        $tgl_penerimaan         = date('Y-m-d');

        foreach ($JmlArray as $key => $value) {
            if ($value != '0' && $value != '') {
                $txtqty         = $JmlArray[$key];
                $id_so_det      = $id_so_detArray[$key];
                $price          = $priceArray[$key];
                $curr          = $currArray[$key]; {
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

                    // $insert_fg_in_nds =  DB::insert("
                    // insert into fg_fg_in (no_sb,tgl_penerimaan,id_ppic_master_so,id_so_det,qty,po,no_carton,lokasi) ");
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
                "callback" => "getno_carton()"
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
