<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPackingMasterkarton;
use App\Exports\ExportDataPoUpload;
use App\Imports\UploadQtyKarton;

class PackingMasterKartonController extends Controller
{
    public function index(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+120 days"));
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_carton = DB::select("SELECT
a.po,
b.ws,
b.buyer,
b.styleno,
b.product_group,
b.product_item,
b.qty_po,
concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')) tgl_shipment_fix,
tot_karton,
tot_karton_isi,
tot_karton_kosong,
coalesce(s.tot_scan,0) tot_scan
from
  (
select a.po,
count(a.no_carton)tot_karton,
count(IF(b.no_carton is not null,1,null)) tot_karton_isi,
count(IF(b.no_carton is null,1,null)) tot_karton_kosong
from  packing_master_carton a
left join (
select no_carton, po from packing_packing_out_scan group by no_carton,po  ) b on
a.po = b.po and  a.no_carton = b.no_carton
group by a.po
) a
left join
(
select
p.po,
m.ws,
m.styleno,
tgl_shipment,
m.buyer,
m.product_group,
m.product_item,
sum(qty_po) qty_po
from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
group by po
) b on a.po = b.po
left join
(select po,count(barcode) tot_scan from packing_packing_out_scan group by po) s on a.po = s.po
where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
 order by tgl_shipment asc, po asc
          ");

            //   SELECT
            //   a.po,
            //   b.ws,
            //   b.buyer,
            //   b.styleno,
            //   b.product_group,
            //   b.product_item,
            //   concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')) tgl_shipment_fix,
            //   tot_carton,
            //   coalesce(s.tot_scan,0) tot_scan
            //   from
            //     (
            //      SELECT po,count(no_carton) tot_carton
            //      FROM `packing_master_carton`
            //      group by po) a
            //   left join (
            //   select
            //   p.po,
            //   m.ws,
            //   m.styleno,
            //   tgl_shipment,
            //   m.buyer,
            //   m.product_group,
            //   m.product_item
            //   from ppic_master_so p
            //   inner join master_sb_ws m on p.id_so_det = m.id_so_det
            //   ) b on a.po = b.po
            //   left join
            //   (select po,count(barcode) tot_scan from packing_packing_out_scan group by po) s on a.po = s.po
            //    where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
            //    group by po
            //   order by tgl_shipment asc, po asc


            return DataTables::of($data_carton)->toJson();
        }

        $data_po = DB::select("SELECT po isi, po tampil from ppic_master_so group by po order by po asc");


        return view(
            'packing.packing_master_karton',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-master-karton",
                "subPage" => "master-karton",
                "data_po" => $data_po,
                "user" => $user,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $po = $request->cbopo;
        $tot_skrg = $request->tot_skrg;
        $tot_skrg_hit = $tot_skrg + 1;
        $tot_input = $request->txtinput_carton;
        $total = $tot_skrg + $tot_input;
        $notes = $request->txtnotes;

        for ($i = $tot_skrg_hit; $i <= $total; $i++) {

            $cek = DB::select(
                "select count(id) id from packing_master_carton where po = '$po' and no_carton = '$i' and notes = '$notes'"
            );
            $cek_data = $cek[0]->id;
            if ($cek_data != '1') {
                $insert = DB::insert(
                    "insert into packing_master_carton
                        (po,no_carton,qty_isi,status,notes,created_at,updated_at,created_by) values
                        ('$po','$i','0','draft','$notes','$timestamp','$timestamp','$user')
                        "
                );
            } else {
                return array(
                    "status" => 201,
                    "message" => 'Data Sudah Ada',
                    "additional" => [],
                );
            }
        }

        if ($insert) {
            return array(
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [],
            );
        }

        // }
    }

    public function show_tot(Request $request)
    {
        $data_header = DB::select("
        SELECT coalesce(max(no_carton),0)tot_skrg
        FROM `packing_master_carton` where po = '$request->cbopo'
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }

    public function show_detail_karton(Request $request)
    {
        $po = $request->po;

        $data_det_karton = DB::select("SELECT
mc.no_carton,
mc.po,
mc.notes,
m.buyer,
dc.barcode,
m.ws,
m.color,
m.size,
p.dest,
p.desc,
m.styleno,
m.product_group,
m.product_item,
mc.qty_isi,
coalesce(dc.tot,'0') tot,
if (mc.po = dc.po,'isi','kosong')stat
from
(select * from packing_master_carton a where po = '$po')mc
left join
(
select count(barcode) tot, po, barcode, no_carton, notes from packing_packing_out_scan
where po = '$po'
group by po, no_carton, barcode, po, notes
) dc on mc.po = dc.po and mc.no_carton = dc.no_carton and mc.notes = dc.notes
left join ppic_master_so p on dc.po = p.po and dc.barcode = p.barcode
left join master_sb_ws m on p.id_so_det = m.id_so_det
order by no_carton asc
                    ");
        return DataTables::of($data_det_karton)->toJson();
    }

    public function getno_carton_hapus(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;
        $data_karton = DB::select("SELECT concat(p.no_carton,'_',p.notes) isi, concat (p.no_carton, ' ( ', coalesce(tot,0) , ' ) - ', p.notes) tampil
from
(
select * from packing_master_carton where po = '" . $request->txtmodal_h_po . "'
) p
left join
(
SELECT count(barcode) tot,po, no_carton, notes from packing_packing_out_scan where po = '" . $request->txtmodal_h_po . "'
group by po, no_carton, notes
) o on p.po = o.po and p.no_carton = o.no_carton and p.notes = o.notes
left join
(
select sum(qty) qty_fg, po, no_carton, notes  from fg_fg_in where po = '" . $request->txtmodal_h_po . "' and status = 'NORMAL' group by po, no_carton, notes
) f on p.po = f.po and p.no_carton = f.no_carton and p.notes = f.notes
 where f.po is null and f.no_carton is null and f.notes is null
 order by p.no_carton asc
        ");

        $html = "<option value=''>Pilih No Karton</option>";

        foreach ($data_karton as $datakarton) {
            $html .= " <option value='" . $datakarton->isi . "'>" . $datakarton->tampil . "</option> ";
        }

        return $html;
    }

    public function list_data_no_carton(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $po = $request->po;
        $cbo_no_carton = $request->no_carton;

        if ($cbo_no_carton != '') {
            $cekArray = explode('_', $cbo_no_carton);
            $no_carton = $cekArray[0];
            $notes = $cekArray[1];
        } else {
            $no_carton = '-';
            $notes = '-';
        }


        $data_list = DB::select("SELECT
a.id,
a.barcode,
a.po,
a.dest,
p.desc,
m.color,
m.size,
m.ws,
a.no_carton
from packing_packing_out_scan a
inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where a.po = '$po' and a.no_carton = '$no_carton' and a.notes = '$notes'
            ");

        return DataTables::of($data_list)->toJson();
    }

    public function hapus_master_karton_det(Request $request)
    {

        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $JmlArray                                   = $_POST['cek_data'];
        $po                                  = $_POST['txtmodal_h_po'];
        foreach ($JmlArray as $key => $value) {
            if ($value != '') {
                $txtid                          = $JmlArray[$key]; {

                    $del_history =  DB::delete("
                    delete from packing_packing_out_scan where id = '$txtid'");

                    //                     $cek_id = DB::select("select b.id id_fg_in, no_sb, id_so_det from
                    // (select * from packing_packing_out_scan where id = '$txtid')a
                    // inner join fg_fg_in b on a.barcode = b.barcode and a.po = b.po and a.no_carton = b.no_carton
                    //                     ");
                    //                     $no_sb = $cek_id ? $cek_id[0]->no_sb : 0;
                    //                     $id_so_det = $cek_id ? $cek_id[0]->id_so_det : 0;
                    //                     $id_fg_in = $cek_id ? $cek_id[0]->id_fg_in : 0;

                    // if ($no_sb != '0') {
                    //     $update_sb = DB::connection('mysql_sb')->update("
                    //     update bpb set qty = qty - 1 where bpbno_int = '$no_sb'  and id_so_det = '$id_so_det' and status_input = 'nds' ");

                    //     $update_nds = DB::update("
                    //     update fg_fg_in set qty = qty  - 1 where id = '$id_fg_in ' ");

                    //     $ins_history =  DB::insert("
                    //     insert into packing_packing_out_scan_log (id_packing_Packing_out_scan, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by)
                    //     SELECT id, tgl_trans, barcode, po, no_carton,created_at, '$timestamp', '$user'  FROM `packing_packing_out_scan` where id = '$txtid'");

                    //     $del_history =  DB::delete("
                    //     delete from packing_packing_out_scan where id = '$txtid'");
                    // } else {
                    //     $del_history =  DB::delete("
                    //     delete from packing_packing_out_scan where id = '$txtid'");
                    // }
                }
            }
        }
        return array(
            "status" => 201,
            "message" => 'Data Sudah di Hapus',
            "additional" => [],
            "redirect" => '',
            "table" => 'datatable_hapus',
            "callback" => "show_data_edit_h(`$po`)"
        );

        // return array(
        //     "status" => 202,
        //     "message" => 'No Form Berhasil Di Update',
        //     "additional" => [],
        //     "redirect" => '',
        //     "callback" => "getdetail(`$no_form_modal`,`$txtket_modal_input`)"

        // );
    }

    public function getno_carton_tambah(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;
        $data_karton = DB::select("SELECT concat(p.no_carton,'_',p.notes) isi, concat (p.no_carton, ' ( ', coalesce(tot,0) , ' )') tampil
from
(
select * from packing_master_carton where po = '" . $request->txtmodal_p_po . "'
) p
left join
(
SELECT count(barcode) tot,po, no_carton, notes from packing_packing_out_scan where po = '" . $request->txtmodal_p_po . "' group by po, no_carton
) o on p.po = o.po and p.no_carton = o.no_carton and p.notes = o.notes
        ");

        $html = "<option value=''>Pilih No Karton</option>";

        foreach ($data_karton as $datakarton) {
            $html .= " <option value='" . $datakarton->isi . "'>" . $datakarton->tampil . "</option> ";
        }

        return $html;
    }

    public function getbarcode_tambah(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;
        $data_barcode = DB::select("SELECT p.id isi, concat(barcode,' - ', color, ' - ', size,' - ', m.dest) tampil
        from ppic_master_so p
        left join master_sb_ws m on p.id_so_det = m.id_so_det
        where po = '" . $request->txtmodal_p_po . "'
        ");

        $html = "<option value=''>Pilih Barcode / Item </option>";

        foreach ($data_barcode as $databarcode) {
            $html .= " <option value='" . $databarcode->isi . "'>" . $databarcode->tampil . "</option> ";
        }

        return $html;
    }

    public function list_data_no_carton_tambah(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $po = $request->po;
        $cbo_no_carton = $request->no_carton;

        if ($cbo_no_carton != '') {
            $cekArray = explode('_', $cbo_no_carton);
            $no_carton = $cekArray[0];
            $notes = $cekArray[1];
        } else {
            $no_carton = '-';
            $notes = '-';
        }

        $data_list = DB::select("SELECT
a.*,
m.color,
m.size,
m.ws
from
(
SELECT barcode, po, dest, count(barcode)tot
from packing_packing_out_scan where po = '$po' and no_carton = '$no_carton' and notes = '$notes'
group by barcode, po, dest
) a
inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join master_sb_ws m on p.id_so_det = m.id_so_det
            ");

        return DataTables::of($data_list)->toJson();
    }

    public function store_tambah_data_karton_det(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tgl_skrg = date('Y-m-d');

        $po = $request->txtmodal_p_po;
        $barcode = $request->cbomodal_p_barcode;
        $qty = $request->cbomodal_p_qty;
        $dest = $request->cbomodal_p_dest;
        $cbo_no_carton = $request->cbomodal_p_no_karton;
        $cekArray = explode('_', $cbo_no_carton);
        $no_carton = $cekArray[0];
        $notes = $cekArray[1];

        $stok = $request->cbomodal_p_qty_stok;



        $validatedRequest = $request->validate([
            "cbomodal_p_barcode" => "required",
            "cbomodal_p_qty" => "required",
            "cbomodal_p_dest" => "required",
            "cbomodal_p_no_karton" => "required",
        ]);

        if ($stok >= $qty) {
            for ($i = 1; $i <= $qty; $i++) {
                $insert = DB::insert("
                insert into packing_packing_out_scan
                (tgl_trans,barcode,po,dest,no_carton,notes,created_by,created_at,updated_at)
                values
                (
                    '$tgl_skrg',
                    '$barcode',
                    '$po',
                    '$dest',
                    '$no_carton',
                    '$notes',
                    '$user',
                    '$timestamp',
                    '$timestamp'
                )
                ");
            }
            return array(
                'icon' => 'benar',
                'msg' => 'Data Sudah Terupdate',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        }
    }

    public function get_data_stok_packing_in(Request $request)
    {
        $cek_stok = DB::select("
        select coalesce(pack_in.tot_in,0)  - coalesce(pack_out.tot_out,0) tot_s
        from ppic_master_so p
        left join
        (
            select sum(qty) tot_in, id_ppic_master_so from packing_packing_in
            where barcode = '$request->barcode' and po = '$request->po' and dest = '$request->dest'
            group by id_ppic_master_so
        ) pack_in on p.id = pack_in.id_ppic_master_so
        left join
        (
            select count(p.barcode) tot_out, p.id
            from packing_packing_out_scan a
            inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
            where p.barcode = '$request->barcode' and p.po = '$request->po' and p.dest = '$request->dest'
            group by a.barcode, a.po
        ) pack_out on p.id = pack_out.id
        where p.barcode = '$request->barcode' and p.po = '$request->po' and dest = '$request->dest'
        ");
        return json_encode($cek_stok ? $cek_stok[0] : null);
    }

    public function simpan_short_karton(Request $request)
    {

        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $no_carton                           = $_POST['txtmodal_s_no_carton'];
        $po                                  = $_POST['txtmodal_s_po'];
        $hsl_short                           = $_POST['txtmodal_s_hsl_short'];

        $update_sb = DB::connection('mysql_sb')->update("
        update bpb set qty = '0' where po_fg = '$po' and status_input = 'nds' ");

        $del_nds = DB::delete("
        delete from fg_fg_in where po = '$po'");

        $del_nds_output_scan = DB::delete("
        delete from packing_packing_out_scan where po = '$po'");

        $del_nds_master_carton = DB::delete("
        delete from packing_master_carton where po = '$po'");

        for ($i = 1; $i <= $hsl_short; $i++) {
            $insert = DB::insert("
            insert into packing_master_carton
            (po,no_carton,notes,status,created_by,created_at,updated_at)
            values
            (
                '$po',
                '$i',
                '-',
                'draft',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            ");
        }

        return array(
            "status" => 201,
            "message" => 'Data Sudah di Update',
            "additional" => [],
            "redirect" => '',
            "table" => 'datatable',
            "callback" => "show_data_edit_h(`$po`,`$no_carton`)"
        );

        // return array(
        //     "status" => 202,
        //     "message" => 'No Form Berhasil Di Update',
        //     "additional" => [],
        //     "redirect" => '',
        //     "callback" => "getdetail(`$no_form_modal`,`$txtket_modal_input`)"

        // );
    }


    public function show_data_upload_karton(Request $request)
    {
        $po = $request->po;

        $data_upload = DB::select("SELECT a.id, a.po , a.no_carton, a.notes, if(b.qty_isi is null , a.qty_isi , b.qty_isi) qty_isi
        from packing_master_carton a
        left join packing_master_carton_upload_qty b on a.id = b.id
        where a.po = '$po'
        order by a.no_carton asc
                    ");
        return DataTables::of($data_upload)->toJson();
    }



    public function export_excel_packing_master_carton(Request $request)
    {
        return Excel::download(new ExportLaporanPackingMasterkarton($request->from, $request->to), 'Laporan_Hasil_Scan.xlsx');
    }

    public function export_data_po_upload(Request $request)
    {
        return Excel::download(new ExportDataPoUpload($request->po), 'Laporan_Hasil_Scan.xlsx');
    }

    public function upload_qty_karton(Request $request)
    {
        // validasi
        $po = $request->modal_upload_po;
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = $file->getClientOriginalName();
        $nama_file_without_extension = substr($nama_file, 0, strrpos($nama_file, '.'));
        // $nama_file_base = preg_replace('/^.+ |\(.*\)/', '', $nama_file_without_extension);
        // $nama_file_base = trim($nama_file_base);

        $ponew = str_replace("/", "_", $po);

        // dd($ponew, $nama_file_without_extension, str_contains($nama_file_without_extension, $ponew));
        // dd($nama_file_base === $po);
        // $nama_file_base = substr($nama_file, strpos($nama_file, ' ') + 1, strrpos($nama_file, '(') - strpos($nama_file, ' ') - 1);
        // $nama_file_base = str_replace('_', '/', $nama_file_base);
        // dd($nama_file_base, $po);

        // if (str_contains($po, $nama_file)) {

        // dd($nama_file_base, $po);
        // dd($nama_file_base === $po);
        // dd($nama_file_without_extension);
        
        if (str_contains($nama_file_without_extension, $ponew)) {
            $file->move('file_upload', $nama_file);
            Excel::import(new UploadQtyKarton, public_path('/file_upload/' . $nama_file));
            return array(
                "status" => 201,
                "message" => 'Data Berhasil Di Upload',
                'table' => 'datatable_upload',
                "additional" => [],
                // "redirect" => url('in-material/upload-lokasi')
            );
        } 
        else {
            return array(
                "status" => 202,
                "message" => 'Data Gagal Di Upload',
                'table' => 'datatable_upload',
                "additional" => [],
                // "redirect" => url('in-material/upload-lokasi')
            );
        }


        // $file->move('file_upload', $nama_file);

        // Excel::import(new UploadQtyKarton, public_path('/file_upload/' . $nama_file));

        // return array(
        //     "status" => 201,
        //     "message" => 'Data Berhasil Di Upload',
        //     'table' => 'datatable_upload',
        //     "additional" => [],
        //     // "redirect" => url('in-material/upload-lokasi')
        // );

        // return array(
        //     "status" => 201,
        //     "message" => 'Data Berhasil Di Upload',
        //     'table' => 'datatable_upload',
        //     "additional" => [],
        //     // "redirect" => url('in-material/upload-lokasi')
        // );
    }

    public function delete_upload_po_karton(Request $request)
    {
        $user = Auth::user()->name;
        $po = $request->po;

        $delete =  DB::delete(
            "DELETE FROM packing_master_carton_upload_qty where po = '$po'"
        );
    }

    public function store_upload_qty_karton(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $po = $request->po;

        $update = DB::update(
            "UPDATE packing_master_carton a
inner join packing_master_carton_upload_qty b on a.id = b.id
SET a.qty_isi = b.qty_isi
where a.po = '$po'
            "
        );
        if ($update) {
            return array(
                'icon' => 'benar',
                'msg' => 'Transaksi Sudah Terbuat',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan, Periksa Data Lagi',
            );
        }
    }

    public function list_data_no_carton_hapus(Request $request)
    {
        $user = Auth::user()->name;
        $po = $request->po;

        $data_list_karton = DB::select("SELECT
a.*, coalesce(b.tot_out,0) tot_out
from
(select * from packing_master_carton where po = '$po') a
left join
(
select po, no_carton, notes, count(barcode)tot_out from packing_packing_out_scan where po = '$po' group by po, no_carton) b
on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
order by no_carton asc
            ");

        return DataTables::of($data_list_karton)->toJson();
    }

    public function hapus_master_karton(Request $request)
    {

        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $po = $request->txtmodal_h_po_karton;
        $JmlArray                                   = $_POST['cek_data'];
        foreach ($JmlArray as $key => $value) {
            if ($value != '') {
                $txtid                          = $JmlArray[$key]; {

                    $del_ctn =  DB::delete("
                        delete from packing_master_carton where id = '$txtid'");
                }
            }
        }
        return array(
            "status" => 201,
            "message" => 'Data Sudah di Hapus',
            "additional" => [],
            "redirect" => '',
            "table" => 'datatable_hapus_karton',
            "callback" => "show_data_edit_h(`$po`)"
        );

        // return array(
        //     "status" => 202,
        //     "message" => 'No Form Berhasil Di Update',
        //     "additional" => [],
        //     "redirect" => '',
        //     "callback" => "getdetail(`$no_form_modal`,`$txtket_modal_input`)"

        // );
    }
}
