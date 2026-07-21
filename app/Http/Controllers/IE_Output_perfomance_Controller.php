<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class IE_Output_perfomance_Controller extends Controller
{
    public function IE_output_performance(Request $request)
    {
        $user = Auth::user()->name;
        $data = DB::connection('mysql_sb')->select("WITH summary AS (
    SELECT
        tgl_trans,
        UPPER(name) AS sewing_line,
        man_power,
        smv,
        styleno,
        SUM(jam_kerja_act) AS jam_kerja_act,
        SUM(tot_rfts) AS tot_rfts,
        SUM(mins_avail) AS mins_avail,
        SUM(mins_prod) AS mins_prod,
        ROUND((SUM(mins_prod)/SUM(mins_avail))*100,2) AS eff
    FROM mgt_rep_tmp_earn
		inner join user_sb_wip u on mgt_rep_tmp_earn.sewing_line = u.username
    WHERE styleno = ?
    GROUP BY
        tgl_trans,
        sewing_line,
        man_power,
        smv,
        styleno
)

SELECT
    tgl_trans,
    sewing_line,
    man_power,
    smv,
    styleno,
    jam_kerja_act,
    tot_rfts,
    eff,

    CASE
        WHEN ROW_NUMBER() OVER (ORDER BY tot_rfts DESC, eff DESC) = 1
        THEN 'Y'
        ELSE NULL
    END AS top_rfts,

    CASE
        WHEN ROW_NUMBER() OVER (ORDER BY eff DESC, tot_rfts DESC) = 1
        THEN 'Y'
        ELSE NULL
    END AS top_eff

FROM summary;
        ", [$request->input('styleno')]);

        $lastUpdated = DB::connection('mysql_sb')->selectOne("SELECT updated_at
            FROM mgt_rep_tmp_earn
            WHERE styleno = ?
            ORDER BY tgl_trans DESC, updated_at DESC
            LIMIT 1", [$request->input('styleno')]);

        // For non-AJAX (initial page load)
        return view('IE.output_perfomance', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-laporan',
            'subPage' => 'IE-laporan-output-performance',
            'containerFluid' => true,
            'user' => $user,
            'data' => $data,
            'styleno' => $request->input('styleno'),
            'lastUpdated' => $lastUpdated->updated_at ?? null,
        ]);
    }

    public function styleno_suggest(Request $request)
    {
        $term = $request->input('q');

        $rows = DB::connection('mysql_sb')->select("SELECT DISTINCT styleno, buyer
            FROM mgt_rep_tmp_earn
            WHERE styleno LIKE ?
            ORDER BY styleno ASC
            LIMIT 20", ['%' . $term . '%']);

        $results = collect($rows)->map(function ($row) {
            return [
                'id' => $row->styleno,
                'text' => $row->styleno . ' - ' . $row->buyer,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
