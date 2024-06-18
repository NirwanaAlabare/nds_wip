<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PPICMasterSo;
use App\Models\OutputPacking;

class PackingTransferGarmentController extends Controller
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
                a.id
                from packing_trf_garment a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in group by 							id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
                order by a.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('packing.packing_transfer_garment', ['page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment", "subPage" => "transfer-garment"]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword where groupp = 'sewing' order by isi asc");

        return view('packing.create_packing_transfer_garment', [
            'page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function get_po(Request $request)
    {
        $data_po = DB::select("
select p.po isi,p.po tampil from
(
select so_det_id from output_rfts_packing a
where sewing_line = '" . $request->cbo_line . "'
group by so_det_id
) a
left join ppic_master_so p on a.so_det_id = p.id_so_det
left join master_sb_ws m on a.so_det_id = m.id_so_det
group by po
having po is not null
order by po asc


        ");

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
                select sum(qty_tmp_trf_garment) tot_tmp,id_ppic_master_so from packing_trf_garment_tmp group by id_ppic_master_so
            ) tmp on p.id = tmp.id_ppic_master_so
        left join
            (
                select sum(qty) tot_in,id_ppic_master_so from packing_trf_garment group by id_ppic_master_so
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

    public function store_tmp_trf_garment(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cboline" => "required",
            "cbopo" => "required",
            "cbogarment" => "required",
            "txtqty" => "required",
        ]);

        // $cek_data = DB::select("
        // select * from ppic_master_so p
        // where id = '" . $validatedRequest['cbogarment'] . "'
        // ");

        // $barcode = $cek_data[0]->barcode;
        // $id_so_det = $cek_data[0]->id_so_det;

        $insert_tmp = DB::insert("
            insert into packing_trf_garment_tmp
            (id_ppic_master_so,qty_tmp_trf_garment,line,created_by,created_at,updated_at)
            values
            (
                '" . $validatedRequest['cbogarment'] . "',
                '" . $validatedRequest['txtqty'] . "',
                '" . $validatedRequest['cboline'] . "',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            ");

        if ($insert_tmp) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data Produk Berhasil Ditambahkan',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang ditambahkan',
            );
        }
    }

    public function show_tmp_trf_garment(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_list = DB::select("
            select
            a.id_tmp_trf_garment,
            line,
            po,
            qty_tmp_trf_garment,
            m.ws,
            m.color,
            m.size
            from packing_trf_garment_tmp a
            inner join ppic_master_so b on a.id_ppic_master_so = b.id
            inner join master_sb_ws m on b.id_so_det = m.id_so_det
            where a.created_by = '$user'
            ");

            return DataTables::of($data_list)->toJson();
        }
    }

    public function hapus_tmp_trf_garment(Request $request)
    {
        $id = $request->id;

        $del_tmp =  DB::delete("
        delete from packing_trf_garment_tmp where id_tmp_trf_garment = '$id'");
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tgltrans = date('Y-m-d');
        $tahun = date('Y', strtotime($tgltrans));
        $bulan = date('m', strtotime($tgltrans));
        $tgl = date('d', strtotime($tgltrans));
        $no = date('dmy', strtotime($tgltrans));
        $kode = 'SEW/OUT/';
        $cek_nomor = DB::select("
        select max(right(no_trans,1))nomor from packing_trf_garment where year(tgl_trans) = '" . $tahun . "'
        and month(tgl_trans) = '" . $bulan . "'
        and day(tgl_trans) = '" . $tgl . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%01s", $urutan);

        $kode_trans = $kode . $no . '/' . $kodepay;

        $cek = DB::select("select * from packing_trf_garment_tmp where created_by = '$user'");

        $cekinput = $cek[0]->id_tmp_trf_garment;

        if ($cekinput == '') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        } else {
            $insert = DB::insert(
                "
                insert into packing_trf_garment
                (no_trans,tgl_trans,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,created_by,created_at,updated_at)
                SELECT '$kode_trans','$tgltrans',
                a.id_ppic_master_so,
                p.id_so_det,
                a.qty_tmp_trf_garment,
                a.line,
                p.po,
                p.barcode,
                p.dest,
                '$user',
                '$timestamp',
                '$timestamp'
                from packing_trf_garment_tmp a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where a.created_by = '$user'
                "
            );
            if ($insert) {
                $delete =  DB::delete(
                    "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
                );
                return array(
                    'icon' => 'benar',
                    'title' => $kode_trans,
                    'msg' => 'No Transaksi Sudah Terbuat',
                );
            }
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



    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
