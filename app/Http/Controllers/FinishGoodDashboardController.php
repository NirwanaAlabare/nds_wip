<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodDashboardController extends Controller
{
    public function dashboard_finish_good(Request $request)
    {
        $tgl_filter = $request->dateFilter;
        $tgl_shipment = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT
a.created_by line,
m.ws,
m.color,
m.size,
m.dest,
sum(qty_p_line) tot_qty_p_line,
list_po
from
(
select created_by,so_det_id,count(so_det_id) qty_p_line from output_rfts_packing
where created_at >= '$tgl_filter 00:00:00' and created_at <= '$tgl_filter 23:59:59'
group by created_by, so_det_id
) a
left join master_sb_ws m on a.so_det_id = m.id_so_det
left join master_size_new msn on m.size = msn.size
left join (select id_so_det, group_concat(distinct(po)) list_po from ppic_master_so where tgl_shipment >= '$tgl_shipment' and tgl_shipment <= '$tgl_shipment' group by id_so_det) b on a.so_det_id = b.id_so_det
group by a.created_by, m.ws, m.color, m.size, m.dest
order by a.created_by asc, ws asc, color asc, dest asc, urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }
        $data_buyer = DB::select("SELECT buyer isi, buyer tampil from
(
select * from fg_fg_in where status = 'NORMAL'
) a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
inner join master_sb_ws m on a.id_so_det = m.id_so_det
where b.id_fg_in is null
group by buyer
order by buyer asc");

        return view('finish_good.dashboard_finish_good', [
            'page' => 'dashboard_finish_good',
            "data_buyer" => $data_buyer
        ]);
    }


    public function get_data_dashboard_fg_ekspedisi(Request $request)
    {
        $data_dash = DB::select("SELECT kode_lok x, coalesce(tot_karton,0)y from fg_fg_master_lok a
left join
(
select lokasi,count(no_carton) tot_karton from
(
select * from (select * from fg_fg_in where status  = 'NORMAL') a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
where b.id_fg_in is null
group by a.no_carton, a.po
)
b
group by lokasi
) b on a.kode_lok = b.lokasi
order by coalesce(tot_karton,0) desc
limit 10");
        return json_encode($data_dash);
    }


    public function show_tot_dash_fg_ekspedisi(Request $request)
    {
        $data_header = DB::select("
SELECT
    SUM(lokasi = '-') AS tot_karton_non,
    SUM(lokasi != '-') AS tot_karton_lok,
    COUNT(*) AS tot_karton
FROM (
    SELECT a.lokasi
    FROM fg_fg_in a
    LEFT JOIN fg_fg_out b ON a.id = b.id_fg_in AND b.status = 'NORMAL'
    WHERE a.status = 'NORMAL'
        AND b.id_fg_in IS NULL
        AND MONTH(a.tgl_penerimaan) = MONTH(CURDATE())
        AND YEAR(a.tgl_penerimaan) = YEAR(CURDATE())
    GROUP BY a.po, a.no_carton, a.lokasi
) t
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }


    public function getws_dashboard_ekspedisi(Request $request)
    {
        $data_ws = DB::select("SELECT m.ws isi, m.ws tampil
        FROM fg_fg_in a
        LEFT JOIN fg_fg_out b ON a.id = b.id_fg_in AND b.status = 'NORMAL'
        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
        WHERE a.status = 'NORMAL' AND b.id_fg_in IS NULL AND m.buyer = ?
        GROUP BY m.ws
        ORDER BY m.ws ASC
        ", [$request->cbobuyer]);

        $html = "<option value=''>Pilih WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }

    public function getpo_dashboard_ekspedisi(Request $request)
    {
        $data_po = DB::select("SELECT a.po isi, a.po tampil
        FROM fg_fg_in a
        LEFT JOIN fg_fg_out b ON a.id = b.id_fg_in AND b.status = 'NORMAL'
        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
        WHERE a.status = 'NORMAL' AND b.id_fg_in IS NULL AND m.buyer = ?
        GROUP BY a.po
        ORDER BY a.po ASC
        ", [$request->cbobuyer]);

        $html = "<option value=''>Pilih PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_detail_dashboard_ekspedisi(Request $request)
    {
        $buyer = $request->buyer;
        $po = $request->po;
        $ws = $request->ws;
        $no_karton = str_replace(' ', '', $request->no_karton);

        if ($request->ajax()) {
            $bindings = [];
            $where = "a.status = 'NORMAL' AND b.id_fg_in IS NULL AND m.buyer = ?";
            $bindings[] = $buyer;

            if (!empty($ws)) {
                $where .= " AND m.ws = ?";
                $bindings[] = $ws;
            }

            if (!empty($po)) {
                $where .= " AND a.po = ?";
                $bindings[] = $po;
            }

            if (strpos($no_karton, '-') !== false) {
                $parts = explode('-', $no_karton);
                $where .= " AND CAST(a.no_carton AS UNSIGNED) BETWEEN ? AND ?";
                $bindings[] = (int) $parts[0];
                $bindings[] = (int) $parts[1];
            } elseif (!empty($no_karton)) {
                $where .= " AND CAST(a.no_carton AS UNSIGNED) = ?";
                $bindings[] = (int) $no_karton;
            }

            $data_det = DB::select("SELECT
m.buyer, m.ws, m.color, m.size, m.dest,
a.po, a.no_carton, a.notes, a.qty, a.lokasi, p.tgl_shipment
FROM fg_fg_in a
LEFT JOIN fg_fg_out b ON a.id = b.id_fg_in AND b.status = 'NORMAL'
LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
LEFT JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
WHERE $where
ORDER BY m.ws ASC, a.po ASC, m.color ASC, m.size ASC
            ", $bindings);
            return DataTables::of($data_det)->toJson();
        }
    }
}
