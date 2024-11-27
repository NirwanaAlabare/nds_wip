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
use App\Imports\UploadPackingListKarton;
use App\Imports\UploadPackingListHeader;
use App\Imports\UploadPackingListKartonVertical;
use App\Exports\ExportDataTemplatePackingListHorizontal;
use App\Exports\ExportDataTemplatePackingListVertical;


class PackingReportController extends Controller
{
    public function packing_rep_packing_line_sum(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+90 days"));
        $tgl_awal_fix = date('Y-m-d', strtotime("-90 days"));
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_pl = DB::select("SELECT
            UPPER(REPLACE(a.created_by, '_', ' ')) sew_line,
            a.created_by,
            m.buyer,
            m.ws,
            m.color,
            m.size,
            a.qty
            from
            (
            select
            so_det_id,
            count(so_det_id) qty,
            created_by
            from output_rfts_packing where date(updated_at) >= '$tgl_awal' and date(updated_at) <= '$tgl_akhir'
            group by so_det_id, created_by
            ) a
            inner join master_sb_ws m on a.so_det_id = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            where created_by is not null
            order by a.created_by asc,ws asc, color asc, urutan asc
          ");

            return DataTables::of($data_pl)->toJson();
        }


        return view(
            'packing.packing_rep_packing_line',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-report",
                "subPage" => "packing_rep_packing_line_sum",
                "user" => $user,
                "tgl_awal_fix" => $tgl_awal_fix,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }
}
