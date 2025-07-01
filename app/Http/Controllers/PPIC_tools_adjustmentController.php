<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UploadPPICAdjOutput;


class PPIC_tools_adjustmentController extends Controller
{
    public function ppic_tools_adj_mut_output(Request $request)
    {

        $tgl_akhir_fix = date('Y-m-d', strtotime("+90 days"));
        $tgl_awal_fix = date('Y-m-d', strtotime("-90 days"));
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $filter = $request->filter;


        if ($request->ajax()) {
            $data_input = DB::select("WITH mb AS (
            SELECT ac.kpno, ac.styleno, sd.color, sd.size, ms.supplier AS buyer, sd.id AS id_so_det
            FROM signalbit_erp.act_costing ac
            INNER JOIN signalbit_erp.so so ON ac.id = so.id_cost
            INNER JOIN signalbit_erp.so_det sd ON so.id = sd.id_so
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            WHERE ac.aktif = 'Y' AND so.cancel_h = 'N' AND sd.cancel = 'N'
        )

        SELECT
		    mb.*,
            a.id,
            a.tgl_adj,
			sa_sewing,
            sa_steam,
            sa_def_sewing,
            sa_def_spotcleaning,
            sa_def_mending,
            sa_def_pck_sewing,
            sa_def_pck_spotcleaning,
            sa_def_pck_mending,
            sa_pck_line,
            sa_trf_gmt,
            sa_pck_central,
            created_at,
            updated_at,
            created_by
        FROM laravel_nds.report_output_adj a
        LEFT JOIN mb on  a.id_so_det = mb.id_so_det
        left join master_size_new msn on mb.size = msn.size
        WHERE a.tgl_adj >= '$tgl_awal' and a.tgl_adj <= '$tgl_akhir'
        ORDER by buyer asc,
        color asc,
        msn.urutan asc

            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'ppic.adj_mut_output',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic_tools",
                "subPage" => "ppic_tools_adj_mut_output",
                "containerFluid" => true,
                "tgl_awal_fix" => $tgl_awal_fix,
                "tgl_akhir_fix" => $tgl_akhir_fix,
                "user" => $user
            ]
        );
    }

    public function contoh_upload_adj_mut_output()
    {
        $path = public_path('storage/contoh_upload_Report_output.xlsx');
        return response()->download($path);
    }

    public function upload_adj_mut_output(Request $request)
    {
        $this->validate($request, [
            'file_tmbh' => 'required|mimes:csv,xls,xlsx'
        ]);

        try {
            $file = $request->file('file_tmbh');
            $nama_file = rand() . '_' . $file->getClientOriginalName();

            $file->move(public_path('file_upload'), $nama_file);

            Excel::import(new UploadPPICAdjOutput, public_path('/file_upload/' . $nama_file));

            return response()->json([
                'success' => true,
                'message' => 'Data Berhasil Di Upload',
                'table' => 'datatable_upload',
                'additional' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data Gagal Di Upload: ' . $e->getMessage(),
                'table' => 'datatable_upload',
                'additional' => [],
            ]);
        }
    }



    public function show_datatable_upload_adj_mut_output(Request $request)
    {
        $user = Auth::user()->name;
        $data_upload = DB::select("WITH mb as (
select ac.kpno, ac.styleno, sd.color, sd.size, ms.supplier buyer, sd.id id_so_det from signalbit_erp.act_costing ac
inner join signalbit_erp.so so on ac.id = so.id_cost
inner join signalbit_erp.so_det sd on so.id = sd.id_so
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
where ac.aktif = 'Y' and so.cancel_h = 'N' and sd.cancel = 'N'
group by kpno, styleno, color, size, buyer
)

SELECT
tmp.*,
if (mb.kpno is null, 'invalid','ok') status,
mb.id_so_det
FROM laravel_nds.report_output_adj_tmp tmp
LEFT JOIN mb
    ON tmp.ws = mb.kpno
    AND tmp.buyer = mb.buyer
    AND tmp.style = mb.styleno
    AND tmp.color = mb.color
    AND tmp.size = mb.size
WHERE tmp.created_by = '$user';
");

        $status_counts = [
            'ok' => 0,
            'invalid' => 0
        ];

        foreach ($data_upload as $row) {
            if ($row->status === 'ok') {
                $status_counts['ok']++;
            } elseif ($row->status === 'invalid') {
                $status_counts['invalid']++;
            }
        }

        // Return both the data and counts
        return DataTables::of(collect($data_upload))
            ->with([
                'status_counts' => $status_counts
            ])
            ->toJson();
    }


    public function undo_upload_adj_mut_output(Request $request)
    {
        $user = Auth::user()->name;

        $deletedRows = DB::table('report_output_adj_tmp')
            ->where('created_by', $user)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => "$deletedRows record(s) deleted."
        ]);
    }

    public function store_upload_adj_mut_output(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        // Step 1: Count invalid rows for this user
        $invalidCount = DB::table('report_output_adj_tmp AS tmp')
            ->leftJoin(DB::raw("(
            SELECT
                ac.kpno, ac.styleno, sd.color, sd.size, ms.supplier AS buyer, sd.id AS id_so_det
            FROM signalbit_erp.act_costing ac
            INNER JOIN signalbit_erp.so so ON ac.id = so.id_cost
            INNER JOIN signalbit_erp.so_det sd ON so.id = sd.id_so
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            WHERE ac.aktif = 'Y' AND so.cancel_h = 'N' AND sd.cancel = 'N'
        ) AS mb"), function ($join) {
                $join->on('tmp.ws', '=', 'mb.kpno')
                    ->on('tmp.buyer', '=', 'mb.buyer')
                    ->on('tmp.style', '=', 'mb.styleno')
                    ->on('tmp.color', '=', 'mb.color')
                    ->on('tmp.size', '=', 'mb.size');
            })
            ->where('tmp.created_by', $user)
            ->whereNull('mb.id_so_det')
            ->count();

        // Step 2: Stop if there are invalids
        if ($invalidCount > 0) {
            return response()->json([
                'icon' => 'salah',
                'msg' => "$invalidCount data masih invalid. Silakan perbaiki sebelum menyimpan.",
            ]);
        }

        // Step 3: Perform insert (only valid rows remain)
        $insert = DB::insert("INSERT INTO report_output_adj (
        tgl_adj,
        id_so_det,
        sa_sewing,
        sa_steam,
        sa_def_sewing,
        sa_def_spotcleaning,
        sa_def_mending,
        sa_def_pck_sewing,
        sa_def_pck_spotcleaning,
        sa_def_pck_mending,
        sa_pck_line,
        sa_trf_gmt,
        sa_pck_central,
        created_at,
        updated_at,
        created_by
    )
    SELECT
        tmp.tgl_adj,
        mb.id_so_det,
        tmp.sa_sewing,
        tmp.sa_steam,
        tmp.sa_def_sewing,
        tmp.sa_def_spotcleaning,
        tmp.sa_def_mending,
        tmp.sa_def_pck_sewing,
        tmp.sa_def_pck_spotcleaning,
        tmp.sa_def_pck_mending,
        tmp.sa_pck_line,
        tmp.sa_trf_gmt,
        tmp.sa_pck_central,
        ?, ?, ?
    FROM report_output_adj_tmp tmp
    LEFT JOIN (
        SELECT
            ac.kpno, ac.styleno, sd.color, sd.size, ms.supplier AS buyer, sd.id AS id_so_det
        FROM signalbit_erp.act_costing ac
        INNER JOIN signalbit_erp.so so ON ac.id = so.id_cost
        INNER JOIN signalbit_erp.so_det sd ON so.id = sd.id_so
        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
        WHERE ac.aktif = 'Y' AND so.cancel_h = 'N' AND sd.cancel = 'N'
        group by kpno, buyer, styleno, color, size
    ) mb ON tmp.ws = mb.kpno
         AND tmp.buyer = mb.buyer
         AND tmp.style = mb.styleno
         AND tmp.color = mb.color
         AND tmp.size = mb.size
    WHERE tmp.created_by = ? AND mb.id_so_det IS NOT NULL", [
            $timestamp,
            $timestamp,
            $user,
            $user
        ]);

        if ($insert) {
            DB::delete("DELETE FROM report_output_adj_tmp WHERE created_by = ?", [$user]);
            return response()->json([
                'icon' => 'benar',
                'msg' => 'Transaksi berhasil disimpan.',
            ]);
        } else {
            return response()->json([
                'icon' => 'salah',
                'msg' => 'Tidak ada data yang disimpan.',
            ]);
        }
    }



    public function delete_upload_adj_mut_output(Request $request)
    {
        $ids = $request->input('ids');

        try {
            DB::table('report_output_adj')->whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ]);
        }
    }
}
