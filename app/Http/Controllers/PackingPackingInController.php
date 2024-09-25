<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPackingIn;

class PackingPackingInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
            select
            a.no_trans,
            concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
            b.no_trans no_trf_garment,
            b.line,
            p.barcode,
            p.po,
            p.dest,
            a.qty,
            m.ws,
            m.color,
            m.size,
            a.created_at,
            a.created_by
            from packing_packing_in a
            inner join packing_trf_garment b on a.id_trf_garment = b.id
            inner join ppic_master_so p on a.id_ppic_master_so = p.id
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
                where a.tgl_penerimaan >= '$tgl_awal' and a.tgl_penerimaan <= '$tgl_akhir' and sumber = 'Sewing'
            union
            select
            a.no_trans,
            concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
            b.no_trans no_trf_garment,
            'Temporary' line,
            p.barcode,
            p.po,
            p.dest,
            a.qty,
            m.ws,
            m.color,
            m.size,
            a.created_at,
            a.created_by
            from packing_packing_in a
            inner join packing_trf_garment_out_temporary b on a.id_trf_garment = b.id
            inner join ppic_master_so p on a.id_ppic_master_so = p.id
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
                where a.tgl_penerimaan >= '$tgl_awal' and a.tgl_penerimaan <= '$tgl_akhir' and sumber = 'Temporary' and a.line = 'Temporary'
order by created_at desc

            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_no_trans = DB::select("
        select data_cek.no_trans isi , data_cek.no_trans tampil
        from
            (
            SELECT
            a.id,
            a.no_trans,
            a.qty,
            b.qty_in
            from packing_trf_garment a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
                 where a.tujuan = 'Packing'
            having a.qty - coalesce(b.qty_in,0) > '0'
            union
            SELECT
            a.id,
            a.no_trans,
            a.qty,
            b.qty_in
            from packing_trf_garment_out_temporary a
		    left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
            having a.qty - coalesce(b.qty_in,0) > '0'
            ) data_cek
            group by data_cek.no_trans
            order by id desc, no_trans asc
        ");
        return view(
            'packing.packing_in',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-in",
                "subPage" => "packing-in",
                "data_no_trans" => $data_no_trans
            ]
        );
    }


    public function show_preview_packing_in(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_preview = DB::select("
            SELECT
            a.id,
            a.line,
			a.qty,
            b.qty_in,
			m.ws,
			m.color,
			m.size,
			p.barcode,
			p.dest,
		    p.po,
            'PCS' unit
            from packing_trf_garment a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in where sumber != 'Temporary'
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
						inner join ppic_master_so  p on a.id_ppic_master_so = p.id
						inner join master_sb_ws m on p.id_so_det = m.id_so_det
						where a.no_trans = '" . $request->cbono . "'
            having a.qty - coalesce(b.qty_in,0) != '0'
			union
            SELECT
            a.id,
            'Temporary' line,
			a.qty,
            b.qty_in,
			m.ws,
			m.color,
			m.size,
			p.barcode,
			p.dest,
			p.po,
            'PCS' unit
            from packing_trf_garment_out_temporary a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in where sumber = 'Temporary'
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
						inner join ppic_master_so  p on a.id_ppic_master_so = p.id
						inner join master_sb_ws m on p.id_so_det = m.id_so_det
						where a.no_trans = '" . $request->cbono . "'
            having a.qty - coalesce(b.qty_in,0) != '0'
            ");

            return DataTables::of($data_preview)->toJson();
        }
    }


    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword where groupp = 'sewing' order by isi asc");

        return view('packing.create_packing_transfer_garment', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function get_po(Request $request)
    {
        $data_po = DB::select("
        SELECT p.po isi,p.po tampil FROM output_rfts_packing a
        left join ppic_master_so p on a.so_det_id = p.id_so_det
        left join master_sb_ws m on a.so_det_id = m.id_so_det
        where sewing_line = '" . $request->cbo_line . "'
        group by po
        having po is not null
        order by po asc");

        $html = "<option value=''>Pilih PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_garment(Request $request)
    {
        $data_garment = DB::select("
        SELECT p.id isi,
        concat(m.ws, ' - ', m.color, ' - ', m.size, ' => ', count(so_det_id) - coalesce(tmp.tot_tmp,0) - coalesce(ptg.tot_in,0) , ' PCS' ) tampil
        FROM output_rfts_packing a
        left join ppic_master_so p on a.so_det_id = p.id_so_det
        left join master_sb_ws m on a.so_det_id = m.id_so_det
        left join
            (
                select sum(qty_tmp_trf_garment) tot_tmp,id_ppic_master_so from packing_trf_garment_tmp
            ) tmp on p.id = tmp.id_ppic_master_so
        left join
            (
                select sum(qty) tot_in,id_ppic_master_so from packing_trf_garment
            ) ptg on p.id = ptg.id_ppic_master_so
        where sewing_line = '" . $request->cbo_line . "' and p.po = '" . $request->cbo_po . "'
        group by a.so_det_id, p.po, p.barcode
        having po is not null");

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment as $datagarment) {
            $html .= " <option value='" . $datagarment->isi . "'>" . $datagarment->tampil . "</option> ";
        }

        return $html;
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $JmlArray               = $_POST['txtqty'];
        $id_trf_garmentArray    = $_POST['id_trf_garment'];
        $status              = implode(',', $_POST['status']);
        $tgl_penerimaan = date('Y-m-d');

        $tahun = date('Y', strtotime($tgl_penerimaan));
        $no = date('my', strtotime($tgl_penerimaan));
        $kode = 'PCK/IN/';
        $cek_nomor = DB::select("
        select max(cast(SUBSTR(no_trans,13,5) as int))nomor from packing_packing_in where year(tgl_penerimaan) = '" . $tahun . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kode_cek = $urutan++;
        $kodepay = sprintf("%05s", $kode_cek);

        $kode_trans = $kode . $no . '/' . $kodepay;

        if ($status != 'Temporary') {
            foreach ($JmlArray as $key => $value) {
                if ($value != '0' && $value != '') {
                    $txtqty         = $JmlArray[$key];
                    $txtid_trf_garment   = $id_trf_garmentArray[$key]; {

                        $cek = DB::select("select * from packing_trf_garment where id = '$txtid_trf_garment'");
                        $id_ppic_master_so = $cek[0]->id_ppic_master_so;
                        $id_so_det = $cek[0]->id_so_det;
                        $line = $cek[0]->line;
                        $po = $cek[0]->po;
                        $barcode = $cek[0]->barcode;
                        $dest = $cek[0]->dest;

                        $insert_penerimaan =  DB::insert("
        insert into packing_packing_in
        (id_trf_garment,no_trans,tgl_penerimaan,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,sumber,created_by,created_at,updated_at)
        values('$txtid_trf_garment','$kode_trans','$tgl_penerimaan','$id_ppic_master_so','$id_so_det','$txtqty','$line','$po','$barcode','$dest','Sewing','$user','$timestamp','$timestamp')");
                    }
                }
            }
        } else  if ($status == 'Temporary') {
            foreach ($JmlArray as $key => $value) {
                if ($value != '0' && $value != '') {
                    $txtqty         = $JmlArray[$key];
                    $txtid_trf_garment   = $id_trf_garmentArray[$key]; {

                        $cek = DB::select("select * from packing_trf_garment_out_temporary where id = '$txtid_trf_garment'");
                        $id_ppic_master_so = $cek[0]->id_ppic_master_so;
                        $id_so_det = $cek[0]->id_so_det;
                        $line = 'Temporary';
                        $po = $cek[0]->po;
                        $barcode = $cek[0]->barcode;
                        $dest = $cek[0]->dest;

                        $insert_penerimaan =  DB::insert("
        insert into packing_packing_in
        (id_trf_garment,no_trans,tgl_penerimaan,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,sumber,created_by,created_at,updated_at)
        values('$txtid_trf_garment','$kode_trans','$tgl_penerimaan','$id_ppic_master_so','$id_so_det','$txtqty','$line','$po','$barcode','$dest','Temporary','$user','$timestamp','$timestamp')");
                    }
                }
            }
        }

        if ($insert_penerimaan != '') {
            return array(
                "status" => 900,
                "message" => 'No Transaksi :
                 ' . $kode_trans . '
                 Sudah Terbuat',
                "additional" => [],
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }

    public function undo(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
        );

        if ($undo) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data berhasil diundo',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang diundo',
            );
        }
    }

    public function reset(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
        );
    }


    // public function gettipe_garment(Request $request)
    // {
    //     // $data_ws = DB::connection('mysql_sb')->select("
    //     //     select so_det_id isi,
    //     //         concat(ac.kpno,' - ', ac.styleno,' - ', sd.color,' - ', sd.size, ' - > ',count(so_det_id)) tampil
    //     //     from output_rfts_packing a
    //     //         inner join master_plan mp on a.master_plan_id = mp.id
    //     //         inner join act_costing ac on mp.id_ws = ac.id
    //     //         inner join so_det sd on a.so_det_id = sd.id
    //     //         left join master_size_new msn on sd.size = msn.size
    //     //     where sewing_line = '" . $request->cbo_line . "'
    //     //     group by so_det_id
    //     //     having count(so_det_id) != '0'
    //     //     order by ac.kpno asc, sd.color asc, styleno asc, msn.urutan asc
    //     // ");

    //     $data_ws = PPICMasterSo::all();

    //     $html = "<option value=''>Pilih Garment</option>";

    //     foreach ($data_ws as $dataws) {
    //         if ($dataws->outputPacking) {
    //             // $res = $dataws->outputPacking->ppicOutput($request->cbo_line)->get();
    //             $res = $dataws->outputPacking->ppicOutput($request->cbo_line);

    //             foreach ($res as $r) {
    //                 $html .= " <option value='" . $r->isi . "'>" . $dataws->po . " - " . $r->tampil . "</option> ";
    //             }
    //         }
    //     }

    //     return $html;
    // }



    public function export_excel_packing_in(Request $request)
    {
        return Excel::download(new ExportLaporanPackingIn($request->from, $request->to), 'Laporan_Packing_In.xlsx');
    }
}
