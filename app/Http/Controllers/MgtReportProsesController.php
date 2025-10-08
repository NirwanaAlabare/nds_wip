<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MgtReportProsesController extends Controller
{
    public function mgt_report_proses_daily_cost(Request $request)
    {
        $thn_view = $request->periode_tahun_view;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("WITH b AS (
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
                round(sum(projection / tot_working_days),2) AS tot_daily_cost
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
a.projection / $wd as daily_cost,
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
(projection / tot_working_days) AS daily_cost
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


    public function update_data_labor(Request $request)
    {
        $user = Auth::user() ? Auth::user()->name : $request->input('user');
        $timestamp = Carbon::now();
        $frequency = $request->input('frequency');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan'); // might be null
        $start_date = $request->input('start_date'); // might be null
        $end_date = $request->input('end_date'); // might be null

        $cond = "";
        $condDel = "";
        $condDesc = "";
        if ($frequency == 'daily') {
            $cond = "a.tanggal_berjalan BETWEEN '$start_date' AND '$end_date'";
            $condDel = "AND tanggal_berjalan BETWEEN '$start_date' AND '$end_date'";
            $condDesc = "Dari '$start_date' Sampai '$end_date'";
        } else if ($frequency == 'monthly') {
            $cond = "month(a.tanggal_berjalan) = '$bulan' AND year(a.tanggal_berjalan) = '$tahun'";
            $condDel = "AND month(tanggal_berjalan) = '$bulan' AND year(tanggal_berjalan) = '$tahun'";
            $condDesc = "Bulan '$bulan' Tahun '$tahun'";
        } else if ($frequency == 'yearly') {
            $cond = "year(tanggal_berjalan) = '$tahun'";
            $condDel = "AND year(tanggal_berjalan) = '$tahun'";
            $condDesc = "Tahun '$tahun'";
        } else {
            $condDel = "AND 1 = 0"; // fail-safe: delete nothing
        }

        $data_hris = DB::connection('mysql_hris')->select(
            "SELECT a.tanggal_berjalan ,if(emp_hist.status_staff is null,b.status_staff,emp_hist.status_staff) status_staff,if(emp_hist.department_id is null,b.department_id,emp_hist.department_id) department_id,if(emp_hist.department_name is null,b.department_name,emp_hist.department_name) department_name,if(emp_hist.department_name is null,b.sub_dept_id,emp_hist.sub_dept_id) sub_dept_id,if(emp_hist.sub_dept_name is null,b.sub_dept_name,emp_hist.sub_dept_name) sub_dept_name,a.group_department,COUNT(IF(CASE WHEN(absen_ijin.kode_ijin_payroll is null) THEN
        CASE WHEN(c.mulai_jam_kerja is not null and c.akhir_jam_kerja is not null) THEN
            CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
                CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
                WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
                WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
                ELSE 'OK'
                END
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null and c.status_absen='R') THEN 'R'
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is not null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            END
        ELSE
            CASE WHEN(c.status_absen='LN') THEN 'LBY'
            WHEN (c.status_absen='R') THEN 'R'
            ELSE 'LSM'
            END
        END
        WHEN(absen_ijin.kode_ijin_payroll='ITB') THEN
            CASE WHEN(c.status_absen='M')THEN 'M'
            WHEN(c.status_absen='IKS') THEN
                CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
                    CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
                    WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
                    WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
                    ELSE 'OK'
                    END
                ELSE 'OK'
                END
            WHEN(c.status_absen='LP') THEN 'LP'
            WHEN(c.status_absen='S') THEN 'S'
            ELSE 'ITB'
            END
        WHEN(absen_ijin.kode_ijin_payroll='IBY') THEN
            CASE WHEN(c.status_absen='DL') THEN 'DL'
            ELSE 'IBY'
            END
        ELSE absen_ijin.kode_ijin_payroll END in ('OK','DT','PC','DTPC','IBY','IKS'),1,null) OR (c.mulai_jam_kerja is null and c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)  ) man_power,
        SUM(CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null) THEN
            CASE WHEN(c.kode_hari not in (5,6))THEN
                CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
                    ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
                WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
                    ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
                END
            ELSE
                CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
                    ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
                WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
                    ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
                END
            END
        ELSE 0 END) absen_menit,
        c.mulai_jam_kerja,c.status_absen,c.absen_masuk_kerja,c.absen_pulang_kerja,c.kode_hari,sum(a.bruto) bruto,sum(rpl.total_lembur_rupiah) total_lembur_rupiah,sum(a.bpjs_tk_company) bpjs_tk,sum(a.bpjs_ks_company) bpjs_ks,sum(a.thr) thr from daily_labor_costs a
        inner join employee_atribut b on a.enroll_id=b.enroll_id
        inner join master_data_absen_kehadiran c on a.enroll_id=c.enroll_id and a.tanggal_berjalan=c.tanggal_berjalan
        left join data_lembur d on a.enroll_id=d.enroll_id and a.tanggal_berjalan=d.tanggal_berjalan
        left join rekap_perhitungan_lembur rpl on a.enroll_id=rpl.enroll_id and a.tanggal_berjalan=rpl.tanggal_berjalan
        left join ref_absen_ijin absen_ijin on c.status_absen=absen_ijin.kode_absen_ijin
        left join employee_atribut_histories emp_hist on a.enroll_id=emp_hist.enroll_id and a.tanggal_berjalan between SUBSTRING(emp_hist.periode_payroll,1,10) and SUBSTRING(emp_hist.periode_payroll,16,10)
        where $cond
        group by a.tanggal_berjalan,sub_dept_id"
        );

        // Delete The Current Data
        $deleteCurrentData = DB::connection('mysql_sb')->table('mgt_rep_labor')->whereRaw("tanggal_berjalan is not null ".$condDel."")->delete();

        // Insert New Data
        foreach ($data_hris as $row) {
            DB::connection('mysql_sb')->table('mgt_rep_labor')->updateOrInsert(
                [
                    'tanggal_berjalan' => $row->tanggal_berjalan,
                    'sub_dept_id'      => $row->sub_dept_id,
                    'group_department' => $row->group_department,
                ],
                [
                    'status_staff'        => $row->status_staff,
                    'department_id'       => $row->department_id,
                    'department_name'     => $row->department_name,
                    'sub_dept_name'       => $row->sub_dept_name,
                    'man_power'           => $row->man_power,
                    'absen_menit'         => $row->absen_menit,
                    'mulai_jam_kerja'     => $row->mulai_jam_kerja,
                    'status_absen'        => $row->status_absen,
                    'absen_masuk_kerja'   => $row->absen_masuk_kerja,
                    'absen_pulang_kerja'  => $row->absen_pulang_kerja,
                    'kode_hari'           => $row->kode_hari,
                    'bruto'               => $row->bruto,
                    'total_lembur_rupiah' => $row->total_lembur_rupiah,
                    'bpjs_tk'             => $row->bpjs_tk,
                    'bpjs_ks'             => $row->bpjs_ks,
                    'thr'                 => $row->thr,
                    'created_by'          => $user,
                    'created_at'          => $timestamp,
                ]
            );
        }

        Log::channel('updateHrisLabor')->info("Labor Processed ".(Carbon::now()->format('d-m-Y H:i:s'))." \n ".$condDesc."");
        Log::channel('updateHrisLabor')->info($data_hris);

        return array(
            "status" => 200,
            "message" => "Labor Processed ".(Carbon::now()->format('d-m-Y h:i:s')." \n ".$condDesc.""),
            "data" => $data_hris
        );
    }

    public function update_data_labor_new(Request $request)
    {
        $user = Auth::user() ? Auth::user()->name : $request->input('user');
        $timestamp = Carbon::now();
        $frequency = $request->input('frequency');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan'); // might be null
        $tanggal_awal = $request->input('start_date'); // might be null
        $tanggal_akhir = $request->input('end_date'); // might be null

        $cond = "";
        $condDel = "";
        $condDesc = "";
        if ($frequency == 'daily') {
            $cond = "a.tanggal_berjalan BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
            $condDel = "AND tanggal_berjalan BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
            $condDesc = "Dari '$tanggal_awal' Sampai '$tanggal_akhir'";
        } else if ($frequency == 'monthly') {
            $cond = "month(a.tanggal_berjalan) = '$bulan' AND year(a.tanggal_berjalan) = '$tahun'";
            $condDel = "AND month(tanggal_berjalan) = '$bulan' AND year(tanggal_berjalan) = '$tahun'";
            $condDesc = "Bulan '$bulan' Tahun '$tahun'";
        } else if ($frequency == 'yearly') {
            $cond = "year(tanggal_berjalan) = '$tahun'";
            $condDel = "AND year(tanggal_berjalan) = '$tahun'";
            $condDesc = "Tahun '$tahun'";
        } else {
            $condDel = "AND 1 = 0"; // fail-safe: delete nothing
        }

        // STAFF/NON STAFF
        $query=DB::connection("mysql_hris")->select("select a.tanggal_berjalan ,if(emp_hist.status_staff is null,b.status_staff,emp_hist.status_staff) status_staff,if(emp_hist.department_id is null,b.department_id,emp_hist.department_id) department_id,if(emp_hist.department_name is null,b.department_name,emp_hist.department_name) department_name,if(emp_hist.department_name is null,b.sub_dept_id,emp_hist.sub_dept_id) sub_dept_id,if(emp_hist.sub_dept_name is null,b.sub_dept_name,emp_hist.sub_dept_name) sub_dept_name,a.group_department,COUNT(IF(CASE WHEN(absen_ijin.kode_ijin_payroll is null) THEN
        CASE WHEN(c.mulai_jam_kerja is not null and c.akhir_jam_kerja is not null) THEN
            CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
                CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
                WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
                WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
                ELSE 'OK'
                END
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null and c.status_absen='R') THEN 'R'
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is not null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            END
        ELSE
            CASE WHEN(c.status_absen='LN') THEN 'LBY'
            WHEN (c.status_absen='R') THEN 'R'
            ELSE 'LSM'
            END
        END
        WHEN(absen_ijin.kode_ijin_payroll='ITB') THEN
            CASE WHEN(c.status_absen='M')THEN 'M'
            WHEN(c.status_absen='IKS') THEN
                CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
                    CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
                    WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
                    WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
                    ELSE 'OK'
                    END
                ELSE 'OK'
                END
            WHEN(c.status_absen='LP') THEN 'LP'
            WHEN(c.status_absen='S') THEN 'S'
            ELSE 'ITB'
            END
        WHEN(absen_ijin.kode_ijin_payroll='IBY') THEN
            CASE WHEN(c.status_absen='DL') THEN 'DL'
            ELSE 'IBY'
            END
        ELSE absen_ijin.kode_ijin_payroll END in ('OK','DT','PC','DTPC','IBY','IKS'),1,null) OR (c.mulai_jam_kerja is null and c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)  ) man_power,
        SUM(CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null) THEN
            CASE WHEN(c.kode_hari not in (5,6))THEN
                CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
                    ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
                WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
                    ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
                END
            ELSE
                CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
                    ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
                WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
                    ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
                END
            END
        ELSE 0 END) absen_menit,
        c.mulai_jam_kerja,c.status_absen,c.absen_masuk_kerja,c.absen_pulang_kerja,c.kode_hari,sum(a.bruto) bruto,sum(rpl.total_lembur_rupiah) total_lembur_rupiah,sum(a.bpjs_tk_company) bpjs_tk,sum(a.bpjs_ks_company) bpjs_ks,sum(a.thr) thr, sum(a.gaji_perhari) gaji_perhari from daily_labor_costs a inner join employee_atribut b on a.enroll_id=b.enroll_id inner join master_data_absen_kehadiran c on a.enroll_id=c.enroll_id and a.tanggal_berjalan=c.tanggal_berjalan left join data_lembur d on a.enroll_id=d.enroll_id and a.tanggal_berjalan=d.tanggal_berjalan left join rekap_perhitungan_lembur rpl on a.enroll_id=rpl.enroll_id and a.tanggal_berjalan=rpl.tanggal_berjalan left join ref_absen_ijin absen_ijin on c.status_absen=absen_ijin.kode_absen_ijin left join employee_atribut_histories emp_hist on a.enroll_id=emp_hist.enroll_id and a.tanggal_berjalan between SUBSTRING(emp_hist.periode_payroll,1,10) and SUBSTRING(emp_hist.periode_payroll,16,10) where a.tanggal_berjalan>='".$tanggal_awal."' and a.tanggal_berjalan<='".$tanggal_akhir."' group by a.tanggal_berjalan,sub_dept_id, b.status_staff");

        // STAFF
            // $queryStaff=DB::connection("mysql_hris")->select("select a.tanggal_berjalan ,if(emp_hist.status_staff is null,b.status_staff,emp_hist.status_staff) status_staff,if(emp_hist.department_id is null,b.department_id,emp_hist.department_id) department_id,if(emp_hist.department_name is null,b.department_name,emp_hist.department_name) department_name,if(emp_hist.department_name is null,b.sub_dept_id,emp_hist.sub_dept_id) sub_dept_id,if(emp_hist.sub_dept_name is null,b.sub_dept_name,emp_hist.sub_dept_name) sub_dept_name,a.group_department,COUNT(IF(CASE WHEN(absen_ijin.kode_ijin_payroll is null) THEN
            // CASE WHEN(c.mulai_jam_kerja is not null and c.akhir_jam_kerja is not null) THEN
            //     CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
            //         CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
            //         WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
            //         WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
            //         ELSE 'OK'
            //         END
            //     WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null and c.status_absen='R') THEN 'R'
            //     WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            //     WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is not null and c.status_absen!='R') THEN 'M'
            //     WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            //     WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen!='R') THEN 'M'
            //     WHEN(c.absen_masuk_kerja is null and c.absen_pulang_kerja is null and c.status_absen='R') THEN 'R'
            //     END
            // ELSE
            //     CASE WHEN(c.status_absen='LN') THEN 'LBY'
            //     WHEN (c.status_absen='R') THEN 'R'
            //     ELSE 'LSM'
            //     END
            // END
            // WHEN(absen_ijin.kode_ijin_payroll='ITB') THEN
            //     CASE WHEN(c.status_absen='M')THEN 'M'
            //     WHEN(c.status_absen='IKS') THEN
            //         CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)THEN
            //             CASE WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc=0) THEN 'DT'
            //             WHEN(c.jumlah_menit_absen_dt=0 and c.jumlah_menit_absen_pc!=0) THEN 'PC'
            //             WHEN(c.jumlah_menit_absen_dt!=0 and c.jumlah_menit_absen_pc!=0) THEN 'DTPC'
            //             ELSE 'OK'
            //             END
            //         ELSE 'OK'
            //         END
            //     WHEN(c.status_absen='LP') THEN 'LP'
            //     WHEN(c.status_absen='S') THEN 'S'
            //     ELSE 'ITB'
            //     END
            // WHEN(absen_ijin.kode_ijin_payroll='IBY') THEN
            //     CASE WHEN(c.status_absen='DL') THEN 'DL'
            //     ELSE 'IBY'
            //     END
            // ELSE absen_ijin.kode_ijin_payroll END in ('OK','DT','PC','DTPC','IBY','IKS'),1,null) OR (c.mulai_jam_kerja is null and c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null)  ) man_power,
            // SUM(CASE WHEN(c.absen_masuk_kerja is not null and c.absen_pulang_kerja is not null) THEN
            //     CASE WHEN(c.kode_hari not in (5,6))THEN
            //         CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
            //             ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
            //         WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
            //             ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
            //         END
            //     ELSE
            //         CASE WHEN(TIME_TO_SEC(c.absen_masuk_kerja)<TIME_TO_SEC(c.absen_pulang_kerja))THEN
            //             ((TIME_TO_SEC(c.absen_pulang_kerja)-TIME_TO_SEC(c.absen_masuk_kerja))/60)-60
            //         WHEN(TIME_TO_SEC(c.absen_pulang_kerja)<TIME_TO_SEC(c.absen_masuk_kerja))THEN
            //             ((TIME_TO_SEC(c.absen_masuk_kerja)-TIME_TO_SEC(c.absen_pulang_kerja))/60)-60
            //         END
            //     END
            // ELSE 0 END) absen_menit,
            // c.mulai_jam_kerja,c.status_absen,c.absen_masuk_kerja,c.absen_pulang_kerja,c.kode_hari,sum(a.bruto) bruto,sum(rpl.total_lembur_rupiah) total_lembur_rupiah,sum(a.bpjs_tk_company) bpjs_tk,sum(a.bpjs_ks_company) bpjs_ks,sum(a.thr) thr, sum(a.gaji_perhari) gaji_perhari from daily_labor_costs a inner join employee_atribut b on a.enroll_id=b.enroll_id inner join master_data_absen_kehadiran c on a.enroll_id=c.enroll_id and a.tanggal_berjalan=c.tanggal_berjalan left join data_lembur d on a.enroll_id=d.enroll_id and a.tanggal_berjalan=d.tanggal_berjalan left join rekap_perhitungan_lembur rpl on a.enroll_id=rpl.enroll_id and a.tanggal_berjalan=rpl.tanggal_berjalan left join ref_absen_ijin absen_ijin on c.status_absen=absen_ijin.kode_absen_ijin left join employee_atribut_histories emp_hist on a.enroll_id=emp_hist.enroll_id and a.tanggal_berjalan between SUBSTRING(emp_hist.periode_payroll,1,10) and SUBSTRING(emp_hist.periode_payroll,16,10) where a.tanggal_berjalan>='".$tanggal_awal."' and a.tanggal_berjalan<='".$tanggal_akhir."'  and (b.status_staff='STAFF' or emp_hist.status_staff='STAFF') group by a.tanggal_berjalan,sub_dept_id");

        $query_2=DB::connection("mysql_hris")->select("select tanggal_berjalan from daily_labor_costs where tanggal_berjalan>='".$tanggal_awal."' and tanggal_berjalan<='".$tanggal_akhir."' group by tanggal_berjalan order by tanggal_berjalan");
        $query_3=DB::connection("mysql_hris")->select("select a.department_id,a.department_name,a.sub_dept_id,a.sub_dept_name, b_master_cc.group2 group_department from (SELECT * from department_all WHERE status = 'AKTIF' and site_nirwana_id IN
        ('NAG','NAK')) a LEFT JOIN b_master_cc ON a.sub_dept_id = b_master_cc.no_cc group by a.sub_dept_id order by a.department_id, a.sub_dept_name");

        $dateRange=array_column($query_2,'tanggal_berjalan');

        // Z STAFF/NON STAFF
        $z=[];
        foreach($query as $valquery){
            array_push($z, [
                'tanggal_berjalan'=>$valquery->tanggal_berjalan,
                'status_staff'=>$valquery->status_staff,
                'department_id'=>$valquery->department_id,
                'department_name'=>$valquery->department_name,
                'sub_dept_id'=>$valquery->sub_dept_id,
                'sub_dept_name'=>$valquery->sub_dept_name,
                'group_department'=>$valquery->group_department,
                'man_power'=>$valquery->man_power,
                'working_min'=>$valquery->absen_menit,
                'total_lembur_rupiah'=> $valquery->total_lembur_rupiah,
                'bruto'=>$valquery->bruto + $valquery->total_lembur_rupiah,
                'bpjs_tk'=>$valquery->bpjs_tk,
                'bpjs_ks'=>$valquery->bpjs_ks,
                'thr'=>$valquery->thr,
                'gaji_perhari'=>$valquery->gaji_perhari,
                'total'=>$valquery->bruto+$valquery->bpjs_tk+$valquery->bpjs_ks+$valquery->thr+$valquery->total_lembur_rupiah
            ]);
        }

        //daterange harus nya diambil dari query lalu di group by tanggal_berjalan
        $x=[];
        foreach($query_3 as $value){
            $x[]=[
                'department_id'=>$value->department_id,
                'department_name'=>$value->department_name,
                'sub_dept_id'=>$value->sub_dept_id,
                'sub_dept_name'=>$value->sub_dept_name,
                'group_department'=>$value->group_department,
                'gaji'=>collect($z)->where('sub_dept_id',$value->sub_dept_id)->sortBy('tanggal_berjalan'),
            ];
        }

        // Delete The Current Data
        $deleteCurrentData = DB::connection('mysql_sb')->table('mgt_rep_labor')->whereRaw("tanggal_berjalan is not null ".$condDel."")->delete();

        // Insert New Data
        foreach ($x as $v) {
            foreach ($dateRange as $date_r) {
                $gajiData = $v['gaji']->where('tanggal_berjalan', $date_r);

                foreach($gajiData as $gajiD) {
                    DB::connection('mysql_sb')->table('mgt_rep_labor')->updateOrInsert(
                        [
                            'tanggal_berjalan' => $date_r,
                            'sub_dept_id'      => $v['sub_dept_id'],
                            'group_department' => $v['group_department'],
                            'status_staff'     => $gajiD['status_staff'] ?? null,
                        ],[
                            'department_id'       => $v['department_id'],
                            'department_name'     => $v['department_name'],
                            'sub_dept_name'       => $v['sub_dept_name'],
                            'man_power'           => $gajiD['man_power'] ?? 0,
                            'absen_menit'         => $gajiD['working_min'] ?? 0,
                            'mulai_jam_kerja'     => $gajiD['mulai_jam_kerja'] ?? null,
                            'status_absen'        => $gajiD['status_absen'] ?? null,
                            'absen_masuk_kerja'   => $gajiD['absen_masuk_kerja'] ?? null,
                            'absen_pulang_kerja'  => $gajiD['absen_pulang_kerja'] ?? null,
                            'kode_hari'           => $gajiD['kode_hari'] ?? null,
                            'bruto'               => $gajiD['bruto'] ?? 0,
                            'total_lembur_rupiah' => $gajiD['total_lembur_rupiah'] ?? 0,
                            'bpjs_tk'             => $gajiD['bpjs_tk'] ?? 0,
                            'bpjs_ks'             => $gajiD['bpjs_ks'] ?? 0,
                            'thr'                 => $gajiD['thr'] ?? 0,
                            'gaji_perhari'        => $gajiD['gaji_perhari'] ?? 0,
                            'created_by'          => $user ?? null,
                            'created_at'          => $timestamp ?? now(),
                        ]
                    );
                }
            }
        }

        $mgtRepLabor = DB::connection('mysql_sb')->table('mgt_rep_labor')->whereRaw("tanggal_berjalan is not null ".$condDel."")->get();

        Log::channel('updateHrisLabor')->info("Labor Processed ".(Carbon::now()->format('d-m-Y H:i:s'))." \n ".$condDesc."");
        Log::channel('updateHrisLabor')->info($mgtRepLabor);

        return array(
            "status" => 200,
            "message" => "Labor Processed ".(Carbon::now()->format('d-m-Y h:i:s')." \n ".$condDesc.""),
            "data" => $mgtRepLabor
        );
    }
}
