<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MutMesin;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiMesinStok;
use App\Exports\ExportLaporanMutasiMesinStokDetail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use DB;

class MutasiMesinLaporanController extends Controller
{
    public function lap_stok_mesin(Request $request)
    {
        if ($request->ajax()) {

            $data_stok = DB::select("SELECT
        m.jenis_mesin, m.brand, count(m.id_qr) total, 'UNIT' satuan
        from master_mesin m
        left join
        (
        select * from mut_mesin_input group by id_qr
        ) mut on m.id_qr = mut.id_qr
        where mut.id_qr is not null
        group by  jenis_mesin, brand
        order by jenis_mesin asc, brand asc
            ");


            return DataTables::of($data_stok)->toJson();
        }
        return view('mut-mesin.lap_stok_mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'lap-mut-mesin', 'subPage' => 'lap_stok_mesin']);
    }

    public function export_excel_stok_mesin(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiMesinStok(), 'Laporan_Stok_mesin.xlsx');
    }

    public function lap_stok_detail_mesin(Request $request)
    {
        if ($request->ajax()) {

            $data_detail_mesin = DB::select("SELECT m.*, mut.tgl_pindah, mut.line
            from master_mesin m
            left join
            (
            select max(id),tgl_pindah,id_qr, line from mut_mesin_input group by id_qr
            ) mut on m.id_qr = mut.id_qr
            where mut.id_qr is not null
            order by jenis_mesin asc, brand asc, id_qr asc

            ");


            return DataTables::of($data_detail_mesin)->toJson();
        }
        return view('mut-mesin.lap_stok_detail_mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'lap-mut-mesin', 'subPage' => 'lap_stok_detail_mesin']);
    }

    public function export_excel_stok_detail_mesin(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiMesinStokDetail(), 'Laporan_Stok_Detail_Mesin.xlsx');
    }
}
