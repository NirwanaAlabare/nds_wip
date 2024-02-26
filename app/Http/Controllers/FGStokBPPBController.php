<?php

namespace App\Http\Controllers;

use App\Models\FGStokbppb;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPengeluaranFGStokBPPB;


class FGStokBPPBController extends Controller
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
            no_trans_out,
            tgl_pengeluaran,
            concat((DATE_FORMAT(tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(tgl_pengeluaran,  '%Y')
            ) tgl_pengeluaran_fix,
            buyer,
            ws,
            brand,
            styleno,
            color,
            size,
            a.qty_out,
            a.grade,
            no_carton,
            lokasi,
            tujuan,
            tujuan_pengeluaran,
            a.created_by,
            created_at
            from fg_stok_bppb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where tgl_pengeluaran >= '$tgl_awal' and tgl_pengeluaran <= '$tgl_akhir'
            order by tgl_pengeluaran desc,substr(no_trans_out,14) desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.bppb_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bppb", "subPage" => "bppb-fg-stock"]);
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $JmlArray         = $_POST['txtqty'];
        $id_so_detArray         = $_POST['id_so_det'];
        $no_cartonArray         = $_POST['no_carton'];
        $gradeArray         = $_POST['grade'];
        $lokasi             = $request->cbolok;
        $tgl_pengeluaran = $request->tgl_pengeluaran;

        $validatedRequest = $request->validate([
            "cbotuj" => "required",
            "cbotuj_pengeluaran" => "required",
        ]);

        $tahun = date('Y', strtotime($tgl_pengeluaran));
        $no = date('ym', strtotime($tgl_pengeluaran));
        $kode = 'FGS/OUT/';
        $cek_nomor = DB::select("
        select max(right(no_trans_out,5))nomor from fg_stok_bppb where year(tgl_pengeluaran) = '" . $tahun . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%05s", $urutan);

        $kode_trans = $kode . $no . '/' . $kodepay;


        foreach ($JmlArray as $key => $value) {
            if ($value != '0' && $value != '') {
                $txtqty         = $JmlArray[$key];
                $txtid_so_det   = $id_so_detArray[$key];
                $txtno_carton   = $no_cartonArray[$key];
                $txtgrade       = $gradeArray[$key]; {
                    $insert_bppb =  DB::insert("
                insert into fg_stok_bppb(no_trans_out,tgl_pengeluaran,id_so_det,qty_out,grade,no_carton,lokasi,tujuan,tujuan_pengeluaran,mutasi,cancel,created_by,created_at,updated_at)
                values('$kode_trans','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$txtno_carton','$lokasi','" . $validatedRequest['cbotuj'] . "','" . $validatedRequest['cbotuj_pengeluaran'] . "','N','N','$user','$timestamp','$timestamp')");
                }
            }
        }
        if ($insert_bppb != '') {
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



        // $JmlArray               = $_POST['txtqty'];
        // $id_so_detArray         = $_POST['id_so_det'];
        // $no_cartonArray         = $_POST['no_carton'];

        // foreach ($JmlArray as $key => $value) {
        //     $txtqty         = $JmlArray[$key];
        //     $txtid_so_det   = $id_so_detArray[$key];
        //     $txtno_carton   = $no_cartonArray[$key]; {
        //         DB::insert("
        //      insert into fg_stok_bppb(id_so_det,qty_out,no_carton)
        //      values('$txtid_so_det','$txtqty','$txtno_carton')");
        //     }
        // }
        // return array(
        //     "status" => 200,
        //     "message" => '',
        //     "additional" => [],
        // );

        // $inmaterialDetailData = [];
        // for ($i = 0; $i < 4; $i++) {
        //     // dd($detdata);
        //     // dd($request);
        //     array_push($inmaterialDetailData, [
        //         "qty_out" => $request["txtqty"][$i],
        //     ]);
        // }
        // $inmaterialDetailStore = FGStokbppb::insert($inmaterialDetailData);





        // $user = Auth::user()->name;
        // dd($request["txtqty"][0]);

        // // $JmlArray         = $request['txtqty'];
        // $inmaterialDetailData = [];
        // for ($i = 0; $i < 2; $i++) {

        //     $qty = $request["txtqty"][$i];
        //     DB::insert("
        //     insert into fg_stok_bppb('qty_out')
        //     values($qty)");
        // }



        // foreach ($JmlArray as $key => $value) {
        //     if ($value != "0") {
        //         $txtqty         = $JmlArray[$key]; {
        //             DB::insert("
        // insert into fg_stok_bppb('qty_out')
        // values('$txtqty')");
        //             return array(
        //                 "status" => 200,
        //                 "message" => '',
        //                 "additional" => [],
        //             );
        //         }
        //     }
        // }


        // $totalQty = 0;

        // $JmlArray         = $request['txtqty'];

        // DB::insert("
        // insert into fg_stok_bppb('qty_out')
        // values('$JmlArray')");
        // return array(
        //     "status" => 200,
        //     "message" => '',
        //     "additional" => [],
        // );

        //     foreach ($JmlArray as $key => $value) {
        //         $txtqty = $JmlArray[$key];
        //         DB::insert("
        //    insert into fg_stok_bppb('qty_out')
        //    values('$txtqty')");
        //         return array(
        //             "status" => 200,
        //             "message" => '',
        //             "additional" => [],
        //         );
        //     }

        // foreach ($request["cut_qty"] as $qty) {
        //     $totalQty += $qty;
        // }
        // if ($totalQty > 0) {
        //     $timestamp = Carbon::now();
        //     $markerDetailData = [];
        //     for ($i = 0; $i < 2; $i++) {
        //         array_push($markerDetailData, [
        //             "id_so_det" => $request["id_so_det"][$i],
        //             "qty_out" => $request["txtqty"][$i],
        //         ]);
        //     }

        //     $markerDetailStore = FGStokbppb::insert($markerDetailData);

        //     return array(
        //         "status" => 200,
        //         "message" => '',
        //         "additional" => [],
        //     );
        // }
    }
    public function create(Request $request)
    {
        $user = Auth::user()->name;
        $data_lok = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok where cancel = 'N'");
        $data_out = DB::select("select tujuan isi , tujuan tampil from fg_stok_master_tujuan where cancel = 'N'");

        $data_buyer = DB::connection('mysql_sb')->select("select ms.supplier  isi, ms.supplier tampil
        from act_costing ac
        inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
		inner join so on ac.id = so.id_cost
		inner join so_det sd on so.id = sd.id_so
        where sd.cancel = 'N'
        group by id_buyer
        order by supplier asc");

        $data_grade = DB::select("select grade isi , grade tampil from fg_stok_master_grade");

        return view('fg-stock.create_bppb_fg_stock', [
            'page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bppb", "subPage" => "bppb-fg-stock",
            "data_lok" => $data_lok, "data_buyer" => $data_buyer, "data_grade" => $data_grade, "data_out" => $data_out, "user" => $user
        ]);
    }

    public function getws(Request $request)
    {
        $data_ws = DB::select("
        select ws isi, concat (ws,' || ',sum(qty_in) - sum(qty_out), ' PCS ') tampil from
        (
        select no_carton,ws,sum(a.qty) qty_in, '0' qty_out  from fg_stok_bpb a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        where lokasi = '" . $request->cbolok . "'
        group by ws	, no_carton
        union
        select no_carton,ws,'0' qty_in, sum(a.qty_out) qty_out  from fg_stok_bppb a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        where lokasi = '" . $request->cbolok . "'
        group by ws	, no_carton
        )
        saldo
        group by ws
        having sum(qty_in) - sum(qty_out) != '0'");

        $html = "<option value=''>Pilih No WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }


    public function show_det(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_det = DB::select("
            select lokasi,
            no_carton,
            s.id_so_det,
            ws,
            sum(s.qty_in) - sum(s.qty_out) saldo,
            m.buyer,
            m.color,
            m.size,
            m.styleno,
            m.brand,
            s.grade,
            concat(s.id_so_det,'_',no_carton,'_',grade) kode
            from
            (
            select lokasi,no_carton,a.id_so_det,sum(a.qty) qty_in, '0' qty_out,grade  from fg_stok_bpb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where lokasi = '" . $request->cbolok . "'
            group by no_carton, a.id_so_det, a.grade
            union
            select lokasi,no_carton,a.id_so_det,'0' qty_in,sum(a.qty_out) qty_out,grade  from fg_stok_bppb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where lokasi = '" . $request->cbolok . "'
            group by no_carton, a.id_so_det, a.grade
            )
            s
            inner join master_sb_ws m on s.id_so_det = m.id_so_det
            group by no_carton, s.id_so_det, s.grade
            having sum(s.qty_in) - sum(s.qty_out) != '0'
            ");

            return DataTables::of($data_det)->toJson();
        }
    }

    public function getstok(Request $request)
    {
        $det_stok = DB::select(
            "
            select lokasi,
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
            group by no_carton, a.id_so_det, a.grade, a.lokasi
            union
            select lokasi,no_carton,a.id_so_det,'0' qty_in,sum(a.qty_out) qty_out,grade  from fg_stok_bppb a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            group by no_carton, a.id_so_det, a.grade, a.lokasi
            )
            s
            inner join master_sb_ws m on s.id_so_det = m.id_so_det
            group by no_carton, s.id_so_det, s.grade
            having sum(s.qty_in) - sum(s.qty_out) != '0'
			order by lokasi asc, no_carton asc
            "
        );

        return DataTables::of($det_stok)->toJson();
    }


    public function export_excel_bppb_fg_stok(Request $request)
    {
        return Excel::download(new ExportLaporanPengeluaranFGStokBPPB($request->from, $request->to), 'Laporan_Pengeluaran FG_Stok.xlsx');
    }
}
