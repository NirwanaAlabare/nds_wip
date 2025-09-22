<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class MgtReportProsesController extends Controller
{
    public function mgt_report_proses_daily_cost(Request $request)
    {
        $thn_view = $request->periode_tahun_view;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("
            WITH b AS (
                SELECT
                    a.bulan,
                    a.nama_bulan,
                    CAST(a.tahun AS UNSIGNED) AS tahun,
                    COUNT(tanggal) AS tot_working_days
                FROM dim_date a
                LEFT JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
                WHERE status_prod = 'KERJA'
                    AND (status_absen != 'LN' OR status_absen IS NULL)
                    AND tahun >= '2025' AND tahun <= '2030'
                GROUP BY bulan, tahun
                ORDER BY
                    CAST(a.tahun AS UNSIGNED) ASC,
                    CAST(a.bulan AS UNSIGNED) ASC
            )

            SELECT
                b.bulan,
                nama_bulan,
                b.tahun,
                SUM(ROUND(projection / tot_working_days, 2)) AS tot_daily_cost
            FROM mgt_rep_daily_cost a
            LEFT JOIN b ON a.bulan = b.bulan AND a.tahun = b.tahun
            WHERE a.tahun >= ?
            GROUP BY b.bulan, b.nama_bulan, b.tahun
            ORDER BY
                CAST(b.tahun AS UNSIGNED) ASC,
                CAST(b.bulan AS UNSIGNED) ASC
        ", [$thn_view]);

            return response()->json([
                'data' => $data_input // ✅ simplified response
            ]);
        }

        // For non-AJAX (initial page load)
        return view('management_report.proses_daily_cost', [
            'page' => 'dashboard-mgt-report',
            'subPageGroup' => 'mgt-report-proses',
            'subPage' => 'mgt-report-proses-daily-cost',
            'containerFluid' => true,
            'user' => $user,
        ]);
    }


    public function mgt_report_proses_daily_cost_show_working_days(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $data_working_days = DB::connection('mysql_hris')->select("SELECT
        count(tanggal) tot_working_days from dim_date a
left join ref_hari_libur b on a.tanggal = b.tanggal_libur
where  bulan = '$bulan' and tahun = '$tahun' and status_prod = 'KERJA'  AND (status_absen != 'LN' OR status_absen IS NULL)
            ");

        $tot_working_days = $data_working_days[0]->tot_working_days ?? 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'tot_working_days'   => $tot_working_days,
            ]
        ]);
    }

    public function contoh_upload_daily_cost()
    {
        $path = public_path('storage/contoh_upload_daily_cost.xlsx');
        return response()->download($path);
    }


    public function upload_excel_daily_cost(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand() . $file->getClientOriginalName();

        $file->move('file_upload', $nama_file);

        Excel::import(new ImportDailyCost, public_path('/file_upload/' . $nama_file));

        return array(
            "status" => 200,
            "message" => 'Data Berhasil Di Upload',
            "additional" => [],
            // "redirect" => url('in-material/upload-lokasi')
        );
    }

    public function mgt_report_proses_daily_cost_show_preview(Request $request)
    {
        $wd = !empty($request->working_days) ? $request->working_days : 0;
        $bulan = !empty($request->bulan) ? $request->bulan : 0;
        $tahun = !empty($request->tahun) ? $request->tahun : 0;
        $user = Auth::user()->name;

        $data_preview = DB::connection('mysql_sb')->select("SELECT
a.no_coa,
a.projection,
b.nama_coa,
round(a.projection / $wd,2) as daily_cost,
IF(b.nama_coa IS NULL, 'N', 'Y') AS cek_valid,
$bulan as bulan,
$tahun as tahun
FROM mgt_rep_daily_cost_tmp a
left join mastercoa_v2 b on a.no_coa = b.no_coa
where a.created_by = '$user'
            ");

        return DataTables::of($data_preview)->toJson();
    }


    public function save_tmp_upload_daily_cost(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $bulanText = $request->bulanText;
        $tahunText = $request->tahunText;
        $wd = $request->working_days;

        $isDuplicate = DB::connection('mysql_sb')->select("
        SELECT *
        FROM mgt_rep_daily_cost
        WHERE bulan = ? AND tahun = ? LIMIT 1", [$bulan, $tahun]);

        if ($isDuplicate) {
            return response()->json([
                'status' => 'duplicate',
            ]);
        }

        DB::connection('mysql_sb')->insert(
            "INSERT INTO mgt_rep_daily_cost (no_coa, projection,bulan, tahun, created_by,created_at,updated_at)
            SELECT
            a.no_coa,
            a.projection,
            $bulan as bulan,
            $tahun as tahun,
            '$user',
            a.updated_at,
            '$timestamp'
            FROM mgt_rep_daily_cost_tmp a
            where a.created_by = '$user'"
        );

        // Delete from temporary table
        DB::connection('mysql_sb')->delete(
            "DELETE FROM mgt_rep_daily_cost_tmp WHERE created_by = ?",
            [$user]
        );

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Daily Cost berhasil disimpan.',
            'data' => [
                'bulanText' => $bulanText,
                'tahunText' => $tahunText
            ]
        ]);
    }

    public function delete_tmp_upload_daily_cost(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        // Delete from temporary table
        DB::connection('mysql_sb')->delete(
            "DELETE FROM mgt_rep_daily_cost_tmp WHERE created_by = ?",
            [$user]
        );

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Daily Cost berhasil dihapus.'
        ]);
    }

    public function show_mgt_report_det_daily_cost(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $user = Auth::user()->name;

        $data_input = DB::connection('mysql_sb')->select("WITH b as (
select
a.bulan,
a.nama_bulan,
CAST(a.tahun AS UNSIGNED) as tahun,
count(tanggal) tot_working_days from dim_date a
left join mgt_rep_hari_libur b on a.tanggal = b.tanggal_libur
where  status_prod = 'KERJA'  AND (status_absen != 'LN' OR status_absen IS NULL)
and tahun >= '2025' and tahun <= '2030'
group by bulan, tahun
order by
CAST(a.tahun AS UNSIGNED) asc,
CAST(a.bulan AS UNSIGNED) asc
)

select
a.no_coa,
c.nama_coa,
b.bulan,
nama_bulan,
b.tahun,
projection,
ROUND(projection / tot_working_days, 2) AS daily_cost
from mgt_rep_daily_cost a
left join b on a.bulan = b.bulan and a.tahun = b.tahun
left join mastercoa_v2 c on a.no_coa = c.no_coa
where a.bulan = '$bulan' and a.tahun = '$tahun'
order by
no_coa asc


            ");

        return DataTables::of($data_input)->toJson();
    }


    public function delete_daily_cost(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $bulan = $request->bulan;
        $nama_bulan = $request->nama_bulan;
        $tahun = $request->tahun;
        // Delete from temporary table
        DB::connection('mysql_sb')->delete(
            "DELETE FROM mgt_rep_daily_cost WHERE bulan = ? and tahun = ?",
            [$bulan, $tahun]
        );

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Daily Cost berhasil dihapus.',
            'data' => [
                'nama_bulan' => $nama_bulan,
                'tahunText' => $tahun
            ]
        ]);
    }
}
