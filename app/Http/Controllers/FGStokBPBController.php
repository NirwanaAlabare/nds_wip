<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class FGStokBPBController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("
            select
            a.id,
            no_trans,
            tgl_terima,
            buyer,
            ws,
            brand,
            styleno,
            color,
            size,
            a.qty,
            a.grade,
            no_carton,
            lokasi,
            a.created_by,
            created_at
            from fg_stok_bpb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where tgl_terima >= '$tgl_awal' and tgl_terima <= '$tgl_akhir'
            order by substr(no_trans,13) desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $sql_temp = DB::select("select * from fg_tmp_stok_bpb where created_by = '$user' group by created_by");
        $cek_temp = $sql_temp ? $sql_temp[0]->id : null;


        return view('fg-stock.bpb_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock", "cek_temp" => $cek_temp]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tglterima = $request->tgl_terima;
        $tahun = date('Y', strtotime($tglterima));
        $no = date('ym', strtotime($tglterima));
        $kode = 'FGS/IN/';
        $cek_nomor = DB::select("
        select max(right(no_trans,5))nomor from fg_stok_bpb where year(tgl_terima) = '" . $tahun . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%05s", $urutan);

        $kode_trans = $kode . $no . '/' . $kodepay;

        $validatedRequest = $request->validate([
            "cbolok" => "required",
            "tgl_terima" => "required",

        ]);

        $cek = DB::select("select * from fg_tmp_stok_bpb where created_by = '$user'");

        $cekinput = $cek[0]->id_so_det;

        if ($cekinput == '') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        } else {
            $insert = DB::insert(
                "insert into fg_stok_bpb
                (no_trans,tgl_terima,id_so_det,qty,grade,no_carton,lokasi,cancel,created_by,created_at,updated_at)
                SELECT '$kode_trans','$tglterima',id_so_det,qty,grade,no_carton,'" . $validatedRequest['cbolok'] . "','N','$user','$timestamp','$timestamp'
                from fg_tmp_stok_bpb
                where created_by = '$user'
                "
            );

            if ($insert) {
                $delete =  DB::delete(
                    "DELETE FROM fg_tmp_stok_bpb where created_by = '$user'"
                );
                return array(
                    'icon' => 'benar',
                    'msg' => 'No Transaksi ' . $kode_trans . ' Sudah Terbuat',
                );
            }
        }
    }

    public function undo(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM fg_tmp_stok_bpb where created_by = '$user'"
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

    public function create(Request $request)
    {
        $user = Auth::user()->name;
        $data_lok = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok");

        $data_buyer = DB::connection('mysql_sb')->select("select id_buyer isi, ms.supplier tampil
        from act_costing ac
        inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
		inner join so on ac.id = so.id_cost
		inner join so_det sd on so.id = sd.id_so
        where sd.cancel = 'N'
        group by id_buyer
        order by supplier asc");

        $data_grade = DB::select("select grade isi , grade tampil from fg_stok_master_grade");

        return view('fg-stock.create_bpb_fg_stock', [
            'page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock",
            "data_lok" => $data_lok, "data_buyer" => $data_buyer, "data_grade" => $data_grade, "user" => $user
        ]);
    }

    public function getno_ws(Request $request)
    {
        $data_ws = DB::connection('mysql_sb')->select("select ac.kpno isi, ac.kpno tampil
        from so_det sd
        inner join so on so.id = sd.id_so
        inner join act_costing ac on so.id_cost = ac.id
        where id_buyer = '" . $request->cbobuyer . "' and sd.cancel = 'N'
        group by ac.kpno
        order by ac.kpno desc");

        $html = "<option value=''>Pilih No WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }

    public function getcolor(Request $request)
    {
        $data_color = DB::connection('mysql_sb')->select("select sd.color isi, sd.color tampil
        from so_det sd
        inner join so on so.id = sd.id_so
        inner join act_costing ac on so.id_cost = ac.id
        where ac.kpno = '" . $request->cbows . "' and sd.cancel = 'N'
        group by sd.color	");

        $html = "<option value=''>Pilih Color</option>";

        foreach ($data_color as $datacolor) {
            $html .= " <option value='" . $datacolor->isi . "'>" . $datacolor->tampil . "</option> ";
        }

        return $html;
    }

    public function getsize(Request $request)
    {
        $data_size = DB::connection('mysql_sb')->select("select sd.size isi, sd.size tampil
        from so_det sd
        inner join so on so.id = sd.id_so
        inner join act_costing ac on so.id_cost = ac.id
        where ac.kpno = '" . $request->cbows . "' and sd.color = '" . $request->cbocolor . "'  and sd.cancel = 'N'
        group by sd.size");

        $html = "<option value=''>Pilih Size</option>";

        foreach ($data_size as $datasize) {
            $html .= " <option value='" . $datasize->isi . "'>" . $datasize->tampil . "</option> ";
        }

        return $html;
    }

    public function getproduct(Request $request)
    {
        $data_product = DB::connection('mysql_sb')->select("select sd.id isi, concat(ac.kpno,' - ', color,' - ',size) tampil
        from so_det sd
        inner join so on so.id = sd.id_so
        inner join act_costing ac on so.id_cost = ac.id
        where ac.kpno = '" . $request->cbows . "' and color like '%" . $request->cbocolor . "%'
        and size like '%" . $request->cbosize . "%' and sd.cancel = 'N'");

        $html = "<option value=''>Pilih Product</option>";

        foreach ($data_product as $dataproduct) {
            $html .= " <option value='" . $dataproduct->isi . "'>" . $dataproduct->tampil . "</option> ";
        }

        return $html;
    }

    public function store_tmp(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cboproduct" => "required",
            "qty" => "required",
            "no_carton" => "required",
            "grade" => "required",
        ]);

        // $cek_data = DB::select("
        // select sd.color from so_det sd
        // where id = '" . $validatedRequest['cboproduct'] . "'
        // ");

        // $color = $cek_data[0]->color;

        $insert_tmp = DB::insert("
            insert into fg_tmp_stok_bpb
            (id_so_det,qty,no_carton,grade,created_by,created_at,updated_at)
            values
            (
                '" . $validatedRequest['cboproduct'] . "',
                '" . $validatedRequest['qty'] . "',
                '" . $validatedRequest['no_carton'] . "',
                '" . $validatedRequest['grade'] . "',
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

    public function show_tmp(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_tmp = DB::select("
            select
            tmp.id,
            tmp.id_so_det,
            tmp.qty,
            tmp.grade,
            tmp.no_carton,
            m.color,
            m.size,
            m.ws,
            m.styleno,
            m.brand
            from fg_tmp_stok_bpb tmp
            inner join master_sb_ws m on tmp.id_so_det = m.id_so_det
            where tmp.created_by = '$user'
            order by tmp.id desc
            ");

            return DataTables::of($data_tmp)->toJson();
        }
    }

    public function show_lok(Request $request)
    {

        if ($request->ajax()) {

            $data_list_lok = DB::select("
            select lokasi,no_carton,sum(qty_in) - sum(qty_out) qty_akhir
            from
            (
            SELECT no_carton,sum(qty) qty_in,'0' qty_out,grade,lokasi FROM `fg_stok_bpb`
            where lokasi = '" . $request->cbolok . "'
            group by no_carton
            union
            SELECT no_carton,'0' qty_in,sum(qty_out) qty_out,grade,lokasi FROM `fg_stok_bppb`
            where lokasi = '" . $request->cbolok . "'
            group by no_carton
            )
            mut_lok
            group by no_carton
            ");

            return DataTables::of($data_list_lok)->toJson();
        }
    }


    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
