<?php

namespace App\Http\Controllers;

use App\Exports\DC\Export_excel_rep_packing_mutasi as DCExport_excel_rep_packing_mutasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Export_excel_rep_packing_line_sum_range;
use App\Exports\Export_excel_rep_packing_line_sum_buyer;
use App\Exports\Export_excel_rep_packing_mutasi;
use App\Exports\ExportDataTemplatePackingListVertical;


class PackingReportController extends Controller
{
    public function packing_rep_packing_line_sum(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+90 days"));
        $tgl_awal_fix = date('Y-m-d', strtotime("-90 days"));
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;

        $data_tipe = DB::select("SELECT 'RANGE' isi , 'RANGE' tampil
        UNION
        SELECT 'BUYER' isi , 'BUYER' tampil
        ");

        $data_po = DB::select("SELECT buyer isi, buyer tampil from ppic_master_so p
        inner join master_sb_ws m on  p.id_so_det = m.id_so_det
        group by buyer
        order by buyer asc
        ");



        return view(
            'packing.packing_rep_packing_line',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-report",
                "subPage" => "packing_rep_packing_line_sum",
                "data_tipe" => $data_tipe,
                "data_po" => $data_po,
                "user" => $user,
                "tgl_skrg" => $tgl_skrg,
                "tgl_awal_fix" => $tgl_awal_fix,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }

    public function packing_rep_packing_line_sum_range(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        if ($request->ajax()) {
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
    }

    public function packing_rep_packing_line_sum_buyer(Request $request)
    {
        $user = Auth::user()->name;
        $buyer = $request->cbobuyer;

        if ($request->ajax()) {
            $data_pl = DB::select("SELECT
			buyer,
            so_det_id,
			ws,
			color,
			b.size,
            count(so_det_id) qty
            from output_rfts_packing a
			inner join
			(
			select buyer,p.id_so_det, ws, color, size from ppic_master_so p
			inner join master_sb_ws m on p.id_so_det = m.id_so_det
			where m.buyer = '$buyer'
			) b on a.so_det_id = b.id_so_det
		    inner join master_size_new msn on b.size = msn.size
             group by so_det_id
			order by ws asc, color asc, urutan asc
              ");

            return DataTables::of($data_pl)->toJson();
        }
    }

    public function export_excel_rep_packing_line_sum_range(Request $request)
    {
        return Excel::download(new Export_excel_rep_packing_line_sum_range($request->from, $request->to), 'Laporan_Packing_In.xlsx');
    }

    public function export_excel_rep_packing_line_sum_buyer(Request $request)
    {
        return Excel::download(new Export_excel_rep_packing_line_sum_buyer($request->buyer), 'Laporan_Packing_In.xlsx');
    }


    public function packing_rep_packing_mutasi(Request $request)
    {
        return view(
            'packing.packing_rep_packing_mutasi',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-report",
                "subPage" => "packing_rep_packing_mutasi",
                "containerFluid" => true,
            ]
        );
    }

    public function packing_rep_packing_mutasi_load(Request $request)
    {
        ini_set('memory_limit', '1024M');
        // if ($request->ajax()) {
        $data_mut = DB::select("WITH Totals AS (
    SELECT
        po,
        barcode,
        no_carton,
        COUNT(barcode) AS tot_scan
    FROM packing_packing_out_scan
    GROUP BY po, barcode, no_carton
),
FgIn AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_in,
        lokasi
    FROM fg_fg_in
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton, lokasi
),
FgOut AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_out
    FROM fg_fg_out
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton
)

SELECT
    p.po,
    m.buyer,
    m.ws,
    m.color,
    m.size,
    a.dest,
    a.barcode,
    a.no_carton,
    a.qty AS qty_pl,
    COALESCE(b.tot_scan, 0) AS tot_scan,
    COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
    COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
    c.lokasi,
    COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
FROM packing_master_packing_list a
LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
LEFT JOIN master_size_new msn ON m.size = msn.size
ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;

      ");

        return DataTables::of($data_mut)->toJson();
        // }
    }


    public function export_excel_rep_packing_mutasi(Request $request)
    {
        // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');

        $data = DB::select("WITH Totals AS (
            SELECT
                po,
                barcode,
                no_carton,
                COUNT(barcode) AS tot_scan
            FROM packing_packing_out_scan
            GROUP BY po, barcode, no_carton
        ),
        FgIn AS (
            SELECT
                po,
                barcode,
                no_carton,
                SUM(qty) AS qty_fg_in,
                lokasi
            FROM fg_fg_in
            WHERE status = 'NORMAL'
            GROUP BY po, barcode, no_carton, lokasi
        ),
        FgOut AS (
            SELECT
                po,
                barcode,
                no_carton,
                SUM(qty) AS qty_fg_out
            FROM fg_fg_out
            WHERE status = 'NORMAL'
            GROUP BY po, barcode, no_carton
        )

        SELECT
            p.po,
            m.buyer,
            m.ws,
            m.color,
            m.size,
            a.dest,
            a.barcode,
            a.no_carton,
            a.qty AS qty_pl,
            COALESCE(b.tot_scan, 0) AS tot_scan,
            COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
            COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
            c.lokasi,
            COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
        FROM packing_master_packing_list a
        LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
        LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
        LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
        INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
        INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
        LEFT JOIN master_size_new msn ON m.size = msn.size
        ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;

              ");

        return response()->json($data);
    }


    // public function packing_rep_packing_mutasi_wip(Request $request)
    // {
    //     ini_set('memory_limit', '2048M');

    //     $tgl_awal  = $request->dateFrom;
    //     $tgl_akhir = $request->dateTo;


    //     $tanggal_saldo_awal = '2026-03-01';

    //     $tgl_saldo_akhir = date(
    //         'Y-m-d',
    //         strtotime($tgl_awal . ' -1 day')
    //     );

    //     if ($request->ajax()) {

    //         // $data_mut = DB::select("
    //         //     WITH pos_periode_agg AS (
    //         //         SELECT
    //         //             p.id_so_det,
    //         //             SUM( a.total_scan ) AS total_keluar
    //         //         FROM
    //         //             ( SELECT id_ppic, COUNT(*) AS total_scan FROM laravel_nds.packing_packing_out_scan WHERE tgl_trans BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59' GROUP BY id_ppic ) a
    //         //             JOIN ppic_master_so p ON p.id = a.id_ppic
    //         //         WHERE
    //         //             p.id_so_det IS NOT NULL
    //         //         GROUP BY
    //         //             p.id_so_det
    //         //     ),
    //         //     trx_union ( so_det_id, pl_saldo_awal_masuk, pl_saldo_awal_keluar, pl_rft, pl_reject, pc_saldo_awal, pc_terima, pc_keluar ) AS (

    //         //     /* ================= SALDO AWAL ================= */
    //         //         SELECT
    //         //             id_so_det AS so_det_id,
    //         //             CASE WHEN type = 'packing_line' THEN saldo ELSE 0 END AS pl_saldo_awal_masuk,
    //         //             0 AS pl_saldo_awal_keluar,
    //         //             0 AS pl_rft,
    //         //             0 AS pl_reject,
    //         //             CASE WHEN type = 'packing_center' THEN saldo ELSE 0 END AS pc_saldo_awal,
    //         //             0 AS pc_terima,
    //         //             0 AS pc_keluar
    //         //         FROM
    //         //             sa_report_pck
    //         //         WHERE
    //         //             tgl_saldo = '{$tanggal_saldo_awal}'

    //         //         UNION ALL

    //         //     /* ================= PACKING LINE SALDO AWAL ================= */
    //         //         SELECT
    //         //             so_det_id,
    //         //             COUNT(*) AS pl_saldo_awal_masuk,
    //         //             0 AS pl_saldo_awal_keluar,
    //         //             0 AS pl_rft,
    //         //             0 AS pl_reject,
    //         //             0 AS pc_saldo_awal,
    //         //             0 AS pc_terima,
    //         //             0 AS pc_keluar
    //         //         FROM
    //         //             signalbit_erp.output_rfts_packing_po
    //         //         WHERE
    //         //             so_det_id IS NOT NULL
    //         //             AND updated_at >= '{$tanggal_saldo_awal} 00:00:00'
    //         //             AND updated_at < '{$tgl_awal} 00:00:00'
    //         //         GROUP BY
    //         //             so_det_id

    //         //         UNION ALL

    //         //     /* ================= PACKING LINE PERIODE ================= */
    //         //         SELECT
    //         //             so_det_id,
    //         //             0 AS pl_saldo_awal_masuk,
    //         //             0 AS pl_saldo_awal_keluar,
    //         //             SUM( type = 'RFT' ) AS pl_rft,
    //         //             SUM( type = 'REJECT' ) AS pl_reject,
    //         //             0 AS pc_saldo_awal,
    //         //             0 AS pc_terima,
    //         //             0 AS pc_keluar
    //         //         FROM
    //         //             signalbit_erp.output_rfts_packing_po
    //         //         WHERE
    //         //             so_det_id IS NOT NULL
    //         //             AND updated_at BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //         //         GROUP BY
    //         //             so_det_id

    //         //         UNION ALL

    //         //     /* ================= PACKING CENTRAL SALDO AWAL ================= */
    //         //         SELECT
    //         //             pms.id_so_det AS so_det_id,
    //         //             0 AS pl_saldo_awal_masuk,
    //         //             SUM( pi.qty ) AS pl_saldo_awal_keluar,
    //         //             0 AS pl_rft,
    //         //             0 AS pl_reject,
    //         //             SUM( pi.qty ) AS pc_saldo_awal,
    //         //             0 AS pc_terima,
    //         //             0 AS pc_keluar
    //         //         FROM
    //         //             laravel_nds.packing_packing_in pi
    //         //             JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
    //         //         WHERE
    //         //             pms.id_so_det IS NOT NULL
    //         //             AND pi.tgl_penerimaan >= '{$tanggal_saldo_awal} 00:00:00'
    //         //             AND pi.tgl_penerimaan < '{$tgl_awal} 00:00:00'
    //         //         GROUP BY
    //         //             pms.id_so_det

    //         //         UNION ALL

    //         //     /* ================= PACKING CENTRAL PERIODE ================= */
    //         //         SELECT
    //         //             x.id_so_det AS so_det_id,
    //         //             0 AS pl_saldo_awal_masuk,
    //         //             0 AS pl_saldo_awal_keluar,
    //         //             0 AS pl_rft,
    //         //             0 AS pl_reject,
    //         //             0 AS pc_saldo_awal,
    //         //             SUM( x.masuk ) AS pc_terima,
    //         //             SUM( x.keluar ) AS pc_keluar
    //         //         FROM
    //         //             (
    //         //             SELECT
    //         //                 pms.id_so_det,
    //         //                 SUM( qty ) AS masuk,
    //         //                 0 AS keluar
    //         //             FROM
    //         //                 laravel_nds.packing_packing_in pi
    //         //                 JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
    //         //             WHERE
    //         //                 pms.id_so_det IS NOT NULL
    //         //                 AND pi.tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //         //             GROUP BY
    //         //                 pms.id_so_det

    //         //             UNION ALL

    //         //             SELECT
    //         //                 id_so_det,
    //         //                 0 AS masuk,
    //         //                 total_keluar AS keluar
    //         //             FROM
    //         //                 pos_periode_agg
    //         //             ) x
    //         //         GROUP BY
    //         //             x.id_so_det
    //         //     )

    //         //     /* ================= FINAL RESULT ================= */
    //         //     SELECT
    //         //         msn.urutan,
    //         //         msw.ws,
    //         //         msw.color,
    //         //         msw.styleno AS style,
    //         //         msw.size,
    //         //         msw.buyer,
    //         //         (SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) AS pl_saldo_awal,
    //         //         SUM( pl_rft ) AS pl_rft,
    //         //         SUM( pl_reject ) AS pl_reject,
    //         //         SUM( pc_terima ) AS pl_keluar,
    //         //         ((SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) + SUM( pl_rft ) + SUM( pl_reject ) - SUM( pc_terima )) AS pl_saldo_akhir,
    //         //         SUM( pc_saldo_awal ) AS pc_saldo_awal,
    //         //         SUM( pc_terima ) AS pc_terima,
    //         //         SUM( pc_keluar ) AS pc_packing_scan,
    //         //         SUM( pc_saldo_awal ) + SUM( pc_terima ) - SUM( pc_keluar ) AS pc_saldo_akhir
    //         //     FROM
    //         //         trx_union t
    //         //         LEFT JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
    //         //         LEFT JOIN master_size_new msn ON msn.size = msw.size
    //         //     GROUP BY
    //         //         msn.urutan,
    //         //         msw.ws,
    //         //         msw.color,
    //         //         msw.styleno,
    //         //         msw.size,
    //         //         msw.buyer
    //         //     ORDER BY
    //         //         msw.ws,
    //         //         msw.color,
    //         //         msw.buyer,
    //         //         msn.urutan
    //         // ");

    //         $data_mut = DB::select("
    //             WITH pos_saldo_agg AS (
    //                 SELECT
    //                     p.id_so_det,
    //                     SUM( a.total_scan ) AS total_keluar
    //                 FROM
    //                     ( SELECT id_ppic, COUNT(*) AS total_scan FROM laravel_nds.packing_packing_out_scan WHERE tgl_trans >= '{$tanggal_saldo_awal} 00:00:00' AND tgl_trans < '{$tgl_awal} 00:00:00' GROUP BY id_ppic ) a
    //                     JOIN ppic_master_so p ON p.id = a.id_ppic
    //                 WHERE
    //                     p.id_so_det IS NOT NULL
    //                 GROUP BY
    //                     p.id_so_det
    //             ),
    //             pos_periode_agg AS (
    //                 SELECT
    //                     p.id_so_det,
    //                     SUM( a.total_scan ) AS total_keluar
    //                 FROM
    //                     ( SELECT id_ppic, COUNT(*) AS total_scan FROM laravel_nds.packing_packing_out_scan WHERE tgl_trans BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59' GROUP BY id_ppic ) a
    //                     JOIN ppic_master_so p ON p.id = a.id_ppic
    //                 WHERE
    //                     p.id_so_det IS NOT NULL
    //                 GROUP BY
    //                     p.id_so_det
    //             ),
    //             trx_union ( so_det_id, pl_saldo_awal_masuk, pl_saldo_awal_keluar, pl_rft, pl_reject, pl_keluar, pc_saldo_awal_masuk, pc_saldo_awal_keluar, pc_terima, pc_keluar ) AS (

    //             /* ================= SALDO AWAL (INJECT) ================= */
    //                 SELECT
    //                     id_so_det AS so_det_id,
    //                     CASE WHEN type = 'packing_line' THEN saldo ELSE 0 END AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     CASE WHEN type = 'packing_center' THEN saldo ELSE 0 END AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     sa_report_pck
    //                 WHERE
    //                     tgl_saldo = '{$tanggal_saldo_awal}'

    //                 UNION ALL

    //             /* ================= PACKING LINE SALDO AWAL MASUK (HISTORY) ================= */
    //                 SELECT
    //                     so_det_id,
    //                     COUNT(*) AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     signalbit_erp.output_rfts_packing_po
    //                 WHERE
    //                     so_det_id IS NOT NULL
    //                     AND updated_at >= '{$tanggal_saldo_awal} 00:00:00'
    //                     AND updated_at < '{$tgl_awal} 00:00:00'
    //                 GROUP BY
    //                     so_det_id

    //                 UNION ALL

    //             /* ================= PACKING LINE SALDO AWAL KELUAR (HISTORY TRANSFER GARMENT) ================= */
    //                 SELECT
    //                     pms.id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     SUM( tg.qty ) AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     laravel_nds.packing_trf_garment tg
    //                     JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
    //                 WHERE
    //                     pms.id_so_det IS NOT NULL
    //                     AND tg.tgl_trans >= '{$tanggal_saldo_awal} 00:00:00'
    //                     AND tg.tgl_trans < '{$tgl_awal} 00:00:00'
    //                 GROUP BY
    //                     pms.id_so_det

    //                 UNION ALL

    //             /* ================= PACKING LINE PERIODE MASUK ================= */
    //                 SELECT
    //                     so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     SUM( type = 'RFT' ) AS pl_rft,
    //                     SUM( type = 'REJECT' ) AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     signalbit_erp.output_rfts_packing_po
    //                 WHERE
    //                     so_det_id IS NOT NULL
    //                     AND updated_at BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //                 GROUP BY
    //                     so_det_id

    //                 UNION ALL

    //             /* ================= PACKING LINE PERIODE KELUAR (TRANSFER GARMENT) ================= */
    //                 SELECT
    //                     pms.id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     SUM( tg.qty ) AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     laravel_nds.packing_trf_garment tg
    //                     JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
    //                 WHERE
    //                     pms.id_so_det IS NOT NULL
    //                     AND tg.tgl_trans BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //                 GROUP BY
    //                     pms.id_so_det

    //                 UNION ALL

    //             /* ================= PACKING CENTRAL SALDO AWAL MASUK (HISTORY) ================= */
    //                 SELECT
    //                     pms.id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     SUM( pi.qty ) AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     laravel_nds.packing_packing_in pi
    //                     JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
    //                 WHERE
    //                     pms.id_so_det IS NOT NULL
    //                     AND pi.tgl_penerimaan >= '{$tanggal_saldo_awal} 00:00:00'
    //                     AND pi.tgl_penerimaan < '{$tgl_awal} 00:00:00'
    //                 GROUP BY
    //                     pms.id_so_det

    //                 UNION ALL

    //             /* ================= PACKING CENTRAL SALDO AWAL KELUAR (HISTORY POS SCAN) ================= */
    //                 SELECT
    //                     id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     total_keluar AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     pos_saldo_agg

    //                 UNION ALL

    //             /* ================= PACKING CENTRAL PERIODE MASUK ================= */
    //                 SELECT
    //                     pms.id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     SUM( pi.qty ) AS pc_terima,
    //                     0 AS pc_keluar
    //                 FROM
    //                     laravel_nds.packing_packing_in pi
    //                     JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
    //                 WHERE
    //                     pms.id_so_det IS NOT NULL
    //                     AND pi.tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //                 GROUP BY
    //                     pms.id_so_det

    //                 UNION ALL

    //             /* ================= PACKING CENTRAL PERIODE KELUAR (POS SCAN) ================= */
    //                 SELECT
    //                     id_so_det AS so_det_id,
    //                     0 AS pl_saldo_awal_masuk,
    //                     0 AS pl_saldo_awal_keluar,
    //                     0 AS pl_rft,
    //                     0 AS pl_reject,
    //                     0 AS pl_keluar,
    //                     0 AS pc_saldo_awal_masuk,
    //                     0 AS pc_saldo_awal_keluar,
    //                     0 AS pc_terima,
    //                     total_keluar AS pc_keluar
    //                 FROM
    //                     pos_periode_agg
    //             ),

    //             /* ================= FINAL RESULT ================= */
    //             final_query as (SELECT
    //                 msn.urutan,
    //                 msw.ws,
    //                 msw.color,
    //                 msw.styleno AS style,
    //                 msw.size,
    //                 msw.buyer,
    //                 (SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) AS pl_saldo_awal,
    //                 SUM( pl_rft ) AS pl_rft,
    //                 SUM( pl_reject ) AS pl_reject,
    //                 SUM( pl_keluar ) AS pl_keluar,
    //                 ((SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) + SUM( pl_rft ) + SUM( pl_reject ) - SUM( pl_keluar )) AS pl_saldo_akhir,
    //                 (SUM( pc_saldo_awal_masuk ) - SUM( pc_saldo_awal_keluar )) AS pc_saldo_awal,
    //                 SUM( pc_terima ) AS pc_terima,
    //                 SUM( pc_keluar ) AS pc_packing_scan,
    //                 ((SUM( pc_saldo_awal_masuk ) - SUM( pc_saldo_awal_keluar )) + SUM( pc_terima ) - SUM( pc_keluar )) AS pc_saldo_akhir
    //             FROM
    //                 trx_union t
    //                 LEFT JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
    //                 LEFT JOIN master_size_new msn ON msn.size = msw.size
    //             GROUP BY
    //                 msn.urutan,
    //                 msw.ws,
    //                 msw.color,
    //                 msw.styleno,
    //                 msw.size,
    //                 msw.buyer
    //             ORDER BY
    //                 msw.ws,
    //                 msw.color,
    //                 msw.buyer,
    //                 msn.urutan)

    //                 select
    //                 urutan, ws, color, style, a.size, buyer,
    //                 sum(pl_saldo_awal) pl_saldo_awal, sum(pl_rft) pl_rft, sum(pl_reject) pl_reject, sum(pl_keluar) pl_keluar, (SUM(pl_saldo_awal) + SUM(pl_rft) + SUM(pl_reject) - SUM(pl_keluar)) pl_saldo_akhir, sum(pc_saldo_awal) pc_saldo_awal, sum(pc_terima) pc_terima, sum(pc_packing_scan) pc_packing_scan, (sum(pc_saldo_awal) + SUM(pc_terima) - SUM(pc_packing_scan)) pc_saldo_akhir
    //                 from
    //                 (
    //                     select * from final_query
    //                     UNION ALL
    //                     select msn.urutan, ws, color, styleno style, a.size, buyer, COALESCE(packing_saldo_awal, 0) pl_saldo_awal, COALESCE(packing_rft,0) pl_rft, 0 pl_reject, 0 pl_keluar, 0 pl_saldo_akhir, COALESCE( pc_saldo_awal, 0) pc_saldo_awal, 0 pc_terima, COALESCE(pc_packing_scan, 0) pc_packing_scan, 0 pc_saldo_akhir from signalbit_erp.inject_mutasi_sewing a LEFT JOIN master_size_new msn ON msn.size = a.size where type_saldo = 'PACKING' and tgl_saldo BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
    //                     UNION ALL
    //                     select msn.urutan, ws, color, styleno style, a.size, buyer, (COALESCE(packing_saldo_awal, 0)+COALESCE(packing_rft, 0)+COALESCE(packing_reject, 0)-COALESCE(packing_keluar, 0)) pl_saldo_awal, 0 pl_reject, 0 pl_keluar, 0 pl_saldo_akhir, (COALESCE(pc_saldo_awal, 0)+COALESCE(pc_terima, 0)-COALESCE(pc_packing_scan, 0)) pc_saldo_awal, 0 pc_terima, 0 pc_packing_scan, 0 pc_saldo_akhir from signalbit_erp.inject_mutasi_sewing a LEFT JOIN master_size_new msn ON msn.size = a.size where type_saldo = 'PACKING' and tgl_saldo < '{$tgl_awal}'
    //                 ) a
    //                 GROUP BY urutan, ws, color, style, size, buyer ORDER BY ws, color, buyer, urutan
    //         ");

    //         return DataTables::of($data_mut)->toJson();
    //     }

    //     return view('packing.packing_rep_packing_mutasi_wip', [
    //         'page' => 'dashboard-packing',
    //         'subPageGroup' => 'packing-report',
    //         'subPage' => 'packing_rep_packing_mutasi_wip',
    //         'containerFluid' => true,
    //     ]);
    // }

    public function packing_rep_packing_mutasi_wip(Request $request)
    {
        ini_set('memory_limit', '2048M');

        $tgl_awal  = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        $tanggal_saldo_awal = '2026-03-01';

        $tgl_saldo_akhir = date(
            'Y-m-d',
            strtotime($tgl_awal . ' -1 day')
        );

        if ($request->ajax()) {

            $data_mut = DB::select("

                WITH trx_union ( so_det_id, pl_saldo_awal_masuk, pl_saldo_awal_keluar, pl_rft, pl_reject, pl_keluar, pc_saldo_awal_masuk, pc_saldo_awal_keluar, pc_terima, pc_terima_return, pc_fg_in ) AS (

                /* ================= SALDO AWAL (INJECT) ================= */
                    SELECT
                        id_so_det AS so_det_id,
                        CASE WHEN type = 'packing_line' THEN saldo ELSE 0 END AS pl_saldo_awal_masuk,
                        0 AS pl_saldo_awal_keluar,
                        0 AS pl_rft,
                        0 AS pl_reject,
                        0 AS pl_keluar,
                        CASE WHEN type = 'packing_center' THEN saldo ELSE 0 END AS pc_saldo_awal_masuk,
                        0 AS pc_saldo_awal_keluar,
                        0 AS pc_terima,
                        0 AS pc_terima_return,
                        0 AS pc_fg_in
                    FROM
                        sa_report_pck
                    WHERE
                        tgl_saldo = '{$tanggal_saldo_awal}'

                    UNION ALL

                /* ================= PACKING LINE SALDO AWAL MASUK (HISTORY) ================= */
                    SELECT
                        so_det_id,
                        COUNT(*) AS pl_saldo_awal_masuk,
                        0, 0, 0, 0, 0, 0, 0, 0, 0
                    FROM
                        signalbit_erp.output_rfts_packing_po
                    WHERE
                        so_det_id IS NOT NULL
                        AND updated_at >= '{$tanggal_saldo_awal} 00:00:00'
                        AND updated_at < '{$tgl_awal} 00:00:00'
                    GROUP BY
                        so_det_id

                    UNION ALL

                /* ================= PACKING LINE SALDO AWAL KELUAR (HISTORY TRF GARMENT) ================= */
                    SELECT
                        pms.id_so_det AS so_det_id,
                        0 AS pl_saldo_awal_masuk,
                        SUM( tg.qty ) AS pl_saldo_awal_keluar,
                        0, 0, 0, 0, 0, 0, 0, 0
                    FROM
                        laravel_nds.packing_trf_garment tg
                        JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
                    WHERE
                        pms.id_so_det IS NOT NULL
                        AND tg.tgl_trans >= '{$tanggal_saldo_awal} 00:00:00'
                        AND tg.tgl_trans < '{$tgl_awal} 00:00:00'
                    GROUP BY
                        pms.id_so_det

                    UNION ALL

                /* ================= PACKING LINE PERIODE MASUK ================= */
                    SELECT
                        so_det_id,
                        0, 0,
                        SUM( type = 'RFT' ) AS pl_rft,
                        SUM( type = 'REJECT' ) AS pl_reject,
                        0, 0, 0, 0, 0, 0
                    FROM
                        signalbit_erp.output_rfts_packing_po
                    WHERE
                        so_det_id IS NOT NULL
                        AND updated_at BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    GROUP BY
                        so_det_id

                    UNION ALL

                /* ================= PACKING LINE PERIODE KELUAR (TRF GARMENT) ================= */
                    SELECT
                        pms.id_so_det AS so_det_id,
                        0, 0, 0, 0,
                        SUM( tg.qty ) AS pl_keluar,
                        0, 0, 0, 0, 0
                    FROM
                        laravel_nds.packing_trf_garment tg
                        JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
                    WHERE
                        pms.id_so_det IS NOT NULL
                        AND tg.tgl_trans BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    GROUP BY
                        pms.id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL SALDO AWAL MASUK (HISTORY TERIMA) ================= */
                    SELECT
                        pms.id_so_det AS so_det_id,
                        0, 0, 0, 0, 0,
                        SUM( pi.qty ) AS pc_saldo_awal_masuk,
                        0, 0, 0, 0
                    FROM
                        laravel_nds.packing_packing_in pi
                        JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                    WHERE
                        pms.id_so_det IS NOT NULL
                        AND pi.tgl_penerimaan >= '{$tanggal_saldo_awal} 00:00:00'
                        AND pi.tgl_penerimaan < '{$tgl_awal} 00:00:00'
                    GROUP BY
                        pms.id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL SALDO AWAL MASUK (HISTORY TERIMA RETURN - BPPB) ================= */
                    SELECT
                        id_so_det AS so_det_id,
                        0, 0, 0, 0, 0,
                        SUM( qty ) AS pc_saldo_awal_masuk,
                        0, 0, 0, 0
                    FROM
                        signalbit_erp.bppb
                    WHERE
                        id_so_det IS NOT NULL
                        AND bppbno_int LIKE '%FG/RO%'
                        AND bppbdate >= '{$tanggal_saldo_awal} 00:00:00'
                        AND bppbdate < '{$tgl_awal} 00:00:00'
                    GROUP BY
                        id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL SALDO AWAL KELUAR (HISTORY FG IN - BPB) ================= */
                    SELECT
                        id_so_det AS so_det_id,
                        0, 0, 0, 0, 0, 0,
                        SUM( qty ) AS pc_saldo_awal_keluar,
                        0, 0, 0
                    FROM
                        signalbit_erp.bpb
                    WHERE
                        id_so_det IS NOT NULL
                        AND bpbno_int LIKE '%FG%'
                        AND bpbdate >= '{$tanggal_saldo_awal} 00:00:00'
                        AND bpbdate < '{$tgl_awal} 00:00:00'
                    GROUP BY
                        id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL PERIODE MASUK ================= */
                    SELECT
                        pms.id_so_det AS so_det_id,
                        0, 0, 0, 0, 0, 0, 0,
                        SUM( pi.qty ) AS pc_terima,
                        0, 0
                    FROM
                        laravel_nds.packing_packing_in pi
                        JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                    WHERE
                        pms.id_so_det IS NOT NULL
                        AND pi.tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    GROUP BY
                        pms.id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL PERIODE TERIMA RETURN (BPPB) ================= */
                    SELECT
                        id_so_det AS so_det_id,
                        0, 0, 0, 0, 0, 0, 0, 0,
                        SUM( qty ) AS pc_terima_return,
                        0
                    FROM
                        signalbit_erp.bppb
                    WHERE
                        id_so_det IS NOT NULL
                        AND bppbno_int LIKE '%FG/RO%'
                        AND bppbdate BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    GROUP BY
                        id_so_det

                    UNION ALL

                /* ================= PACKING CENTRAL PERIODE FG IN KELUAR (BPB) ================= */
                    SELECT
                        id_so_det AS so_det_id,
                        0, 0, 0, 0, 0, 0, 0, 0, 0,
                        SUM( qty ) AS pc_fg_in
                    FROM
                        signalbit_erp.bpb
                    WHERE
                        id_so_det IS NOT NULL
                        AND bpbno_int LIKE '%FG%'
                        AND bpbdate BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    GROUP BY
                        id_so_det
                ),

                /* ================= FINAL RESULT CTE ================= */
                final_query as (SELECT
                    msn.urutan,
                    msw.ws,
                    msw.color,
                    msw.styleno AS style,
                    msw.size,
                    msw.buyer,
                    (SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) AS pl_saldo_awal,
                    SUM( pl_rft ) AS pl_rft,
                    SUM( pl_reject ) AS pl_reject,
                    SUM( pl_keluar ) AS pl_keluar,
                    ((SUM( pl_saldo_awal_masuk ) - SUM( pl_saldo_awal_keluar )) + SUM( pl_rft ) + SUM( pl_reject ) - SUM( pl_keluar )) AS pl_saldo_akhir,

                    (SUM( pc_saldo_awal_masuk ) - SUM( pc_saldo_awal_keluar )) AS pc_saldo_awal,
                    SUM( pc_terima ) AS pc_terima,
                    SUM( pc_terima_return ) AS pc_terima_return,
                    SUM( pc_fg_in ) AS pc_fg_in,

                    ((SUM( pc_saldo_awal_masuk ) - SUM( pc_saldo_awal_keluar )) + SUM( pc_terima ) + SUM( pc_terima_return ) - SUM( pc_fg_in )) AS pc_saldo_akhir
                FROM
                    trx_union t
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
                    LEFT JOIN master_size_new msn ON msn.size = msw.size
                GROUP BY
                    msn.urutan,
                    msw.ws,
                    msw.color,
                    msw.styleno,
                    msw.size,
                    msw.buyer
                )

                /* ================= MAIN SELECT ================= */
                    select
                    urutan, ws, color, style, a.size, buyer,
                    sum(pl_saldo_awal) pl_saldo_awal, sum(pl_rft) pl_rft, sum(pl_reject) pl_reject, sum(pl_keluar) pl_keluar,
                    (SUM(pl_saldo_awal) + SUM(pl_rft) + SUM(pl_reject) - SUM(pl_keluar)) pl_saldo_akhir,
                    sum(pc_saldo_awal) pc_saldo_awal, sum(pc_terima) pc_terima,
                    sum(pc_terima_return) pc_terima_return,
                    sum(pc_fg_in) pc_fg_in,
                    (sum(pc_saldo_awal) + SUM(pc_terima) + SUM(pc_terima_return) - SUM(pc_fg_in)) pc_saldo_akhir
                    from
                    (
                        select * from final_query

                        UNION ALL

                        select msn.urutan, ws, color, styleno style, a.size, buyer,
                        COALESCE(packing_saldo_awal, 0) pl_saldo_awal, COALESCE(packing_rft,0) pl_rft, 0 pl_reject, 0 pl_keluar, 0 pl_saldo_akhir,
                        COALESCE( pc_saldo_awal, 0) pc_saldo_awal, 0 pc_terima, 0 pc_terima_return, 0 pc_fg_in, 0 pc_saldo_akhir
                        from signalbit_erp.inject_mutasi_sewing a LEFT JOIN master_size_new msn ON msn.size = a.size where type_saldo = 'PACKING' and tgl_saldo BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'

                        UNION ALL

                        select msn.urutan, ws, color, styleno style, a.size, buyer,
                        (COALESCE(packing_saldo_awal, 0)+COALESCE(packing_rft, 0)+COALESCE(packing_reject, 0)-COALESCE(packing_keluar, 0)) pl_saldo_awal,
                        0 pl_rft,
                        0 pl_reject, 0 pl_keluar, 0 pl_saldo_akhir,
                        (COALESCE(pc_saldo_awal, 0)+COALESCE(pc_terima, 0)) pc_saldo_awal, 0 pc_terima, 0 pc_terima_return, 0 pc_fg_in, 0 pc_saldo_akhir
                        from signalbit_erp.inject_mutasi_sewing a LEFT JOIN master_size_new msn ON msn.size = a.size where type_saldo = 'PACKING' and tgl_saldo < '{$tgl_awal}'
                    ) a
                    GROUP BY urutan, ws, color, style, size, buyer ORDER BY ws, color, buyer, urutan
            ");

            return DataTables::of($data_mut)->toJson();
        }

        return view('packing.packing_rep_packing_mutasi_wip', [
            'page' => 'dashboard-packing',
            'subPageGroup' => 'packing-report',
            'subPage' => 'packing_rep_packing_mutasi_wip',
            'containerFluid' => true,
        ]);
    }


    public function export_excel_rep_packing_mutasi_wip(Request $request)
    {
        // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;

        return Excel::download(
            new Export_excel_rep_packing_mutasi(
                $tgl_awal,
                $tgl_akhir,
            ),
            'Laporan Mutasi Packing (WIP) ' . $tgl_awal . ' - ' . $tgl_akhir . '.xlsx'
        );
    }
}
