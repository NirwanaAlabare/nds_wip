<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPenerimaanFGStokBPB;

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
            concat((DATE_FORMAT(tgl_terima,  '%d')), '-', left(DATE_FORMAT(tgl_terima,  '%M'),3),'-',DATE_FORMAT(tgl_terima,  '%Y')
            ) tgl_terima_fix,
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
            sumber_pemasukan,
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
        $no = date('my', strtotime($tglterima));
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
            "cbosumber" => "required",

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
                (no_trans,tgl_terima,id_so_det,qty,grade,no_carton,lokasi,sumber_pemasukan,mutasi,cancel,created_by,created_at,updated_at)
                SELECT '$kode_trans','$tglterima',id_so_det,qty,grade,no_carton,'" . $validatedRequest['cbolok'] . "','" . $validatedRequest['cbosumber'] . "','N','N','$user','$timestamp','$timestamp'
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

        $data_lok = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok where cancel = 'N'");

        $data_terima = DB::select("SELECT sumber isi, sumber tampil  FROM `fg_stok_master_sumber_penerimaan` where cancel = 'N'");

        $data_buyer = DB::select("select buyer isi, buyer tampil from master_sb_ws
        group by buyer
        order by buyer asc");

        $data_grade = DB::select("select grade isi , grade tampil from fg_stok_master_grade");

        return view('fg-stock.create_bpb_fg_stock', [
            'page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock",
            "data_lok" => $data_lok, "data_buyer" => $data_buyer, "data_grade" => $data_grade, "data_terima" => $data_terima, "user" => $user
        ]);
    }

    public function getno_ws(Request $request)
    {
        $data_ws = DB::select("
        select a.ws isi, a.ws tampil
        from master_sb_ws a where a.buyer = '" . $request->cbobuyer . "'
        group by ws
        order by ws desc
        ");

        $html = "<option value=''>Pilih No WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }

    public function getcolor(Request $request)
    {
        $data_color = DB::select("select a.color isi, a.color tampil
        from master_sb_ws a where a.ws = '" . $request->cbows . "'
group by color
order by color desc");

        $html = "<option value=''>Pilih Color</option>";

        foreach ($data_color as $datacolor) {
            $html .= " <option value='" . $datacolor->isi . "'>" . $datacolor->tampil . "</option> ";
        }

        return $html;
    }

    public function getsize(Request $request)
    {
        $data_size = DB::select("select a.size isi, a.size tampil
        from master_sb_ws a
        where a.ws = '" . $request->cbows . "' and a.color = '" . $request->cbocolor . "'
        group by a.size");

        $html = "<option value=''>Pilih Size</option>";

        foreach ($data_size as $datasize) {
            $html .= " <option value='" . $datasize->isi . "'>" . $datasize->tampil . "</option> ";
        }

        return $html;
    }

    public function getproduct(Request $request)
    {
        $data_product = DB::select("select a.id_so_det isi, concat(ws,' - ', color,' - ',size) tampil
        from master_sb_ws a
        where a.ws= '" . $request->cbows . "' and a.color like '%" . $request->cbocolor . "%'
        and a.size like '%" . $request->cbosize . "%'");

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

            $data_list = DB::select("
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

            return DataTables::of($data_list)->toJson();
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

    public function getdet_carton(Request $request)
    {
        $det_carton = DB::select(
            "select lokasi,
            no_carton,
            s.id_so_det,
            ws,
            sum(s.qty_in) - sum(s.qty_out) saldo,
            m.buyer,
            m.color,
            m.size,
            m.styleno,
            m.brand,
            s.grade
            from
            (
            select lokasi,no_carton,a.id_so_det,sum(a.qty) qty_in, '0' qty_out,grade  from fg_stok_bpb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where lokasi = '" . $request->lokasi . "' and no_carton = '" . $request->karton . "'
            group by no_carton, a.id_so_det, a.grade
            union
            select lokasi,no_carton,a.id_so_det,'0' qty_in,sum(a.qty_out) qty_out,grade  from fg_stok_bppb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where lokasi = '" . $request->lokasi . "' and no_carton = '" . $request->karton . "'
            group by no_carton, a.id_so_det, a.grade
            )
            s
            inner join master_sb_ws m on s.id_so_det = m.id_so_det
            group by no_carton, s.id_so_det, s.grade
            having sum(s.qty_in) - sum(s.qty_out) != '0'"
        );

        return DataTables::of($det_carton)->toJson();
    }

    public function hapus_data_temp_bpb_fg_stok(Request $request)
    {
        $id = $request->id;

        $del_tmp =  DB::delete("
        delete from fg_tmp_stok_bpb where id = '$id'");
    }


    public function export_excel_bpb_fg_stok(Request $request)
    {
        return Excel::download(new ExportLaporanPenerimaanFGStokBPB($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
