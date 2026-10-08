<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Stocker\Stocker;
use App\Models\Dc\SecondaryInhouse;
use App\Exports\DC\ExportSecondaryInHouse;
use App\Exports\DC\ExportSecondaryInHouseDetail;
use App\Services\SecondaryInhouseService;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Carbon\Carbon;
use DB;

class SecondaryInhouseOutController extends Controller
{
    /**
     * Satu-satunya sumber query list Secondary Inhouse Out (tabel, filter dropdown, total, export list & export detail).
     * Mengembalikan query builder di atas subquery `sec_out` sehingga paging/sort/sum/distinct
     * dikerjakan database, bukan PHP.
     *
     * Catatan kolom: `panel` & `nama_part` adalah versi tampil di tabel (dengan status),
     * sedangkan `panel_only` & `nama_part_only` adalah nilai polos (dipakai export).
     */
    private function secondaryInhouseOutQuery(Request $request, $from = null, $to = null)
    {
        $where = [];
        $bindings = [];

        // kondisi "ekspresi IN (?, ?, ...)"
        $addIn = function ($expr, $values) use (&$where, &$bindings) {
            $values = array_values(array_filter((array) $values, fn ($v) => $v !== null && $v !== ''));
            if (count($values) < 1) {
                return;
            }
            $where[] = "$expr in (" . implode(',', array_fill(0, count($values), '?')) . ")";
            foreach ($values as $v) {
                $bindings[] = trim($v);
            }
        };

        $tipeExpr = "(CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END)";
        $panelRaw = "(CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END)";
        $panelStatusRaw = "(CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)";
        $panelExpr = "CONCAT($panelRaw, (CASE WHEN $panelStatusRaw IS NOT NULL THEN CONCAT(' - ', $panelStatusRaw) ELSE '' END))";
        $partStatusExpr = "UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))";
        $partExpr = "CONCAT(mp.nama_part, (CASE WHEN $partStatusExpr != '-' THEN CONCAT(' - ', $partStatusExpr) ELSE '' END))";
        $tujuanExpr = "COALESCE(mms.tujuan, ms.tujuan, mx.tujuan, dc.tujuan)";
        $lokasiExpr = "COALESCE(mms.proses, ms.proses, mx.proses, dc.lokasi)";

        if ($from) {
            $where[] = "a.tgl_trans >= ?";
            $bindings[] = $from;
        }
        if ($to) {
            $where[] = "a.tgl_trans <= ?";
            $bindings[] = $to;
        }

        // filter dropdown (multi select), memakai ekspresi yang sama dengan kolom yang ditampilkan
        $addIn($tipeExpr, $request->sec_filter_tipe);
        $addIn("p.buyer", $request->sec_filter_buyer);
        $addIn("s.act_costing_ws", $request->sec_filter_ws);
        $addIn("p.style", $request->sec_filter_style);
        $addIn("s.color", $request->sec_filter_color);
        $addIn($panelExpr, $request->sec_filter_panel);
        $addIn($partExpr, $request->sec_filter_part);
        $addIn("COALESCE(msb.size, s.size)", $request->sec_filter_size);
        $addIn("COALESCE(msb.size, s.size)", $request->size_filter);
        $addIn("COALESCE(f.no_cut, fp.no_cut, '-')", $request->sec_filter_no_cut);
        $addIn($tujuanExpr, $request->sec_filter_tujuan);
        $addIn("dc.tempat", $request->sec_filter_tempat);
        $addIn($lokasiExpr, $request->sec_filter_lokasi);

        $additionalQuery = count($where) > 0 ? " and " . implode(" and ", $where) : "";

        $sql = "
            SELECT
                a.id,
                a.id_qr_stocker,
                $tipeExpr AS tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                $panelExpr panel,
                $panelRaw panel_only,
                $panelStatusRaw panel_status,
                COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                COALESCE(a.qty_in) qty_in,
                a.created_at,
                $tujuanExpr as tujuan,
                $lokasiExpr lokasi,
                dc.tempat,
                COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                COALESCE(msb.size, s.size) AS size,
                a.user,
                (CASE WHEN a.urutan > 0 THEN a.urutan ELSE '-' END) urutan,
                $partExpr nama_part,
                mp.nama_part nama_part_only,
                $partStatusExpr part_status,
                CONCAT(
                    s.range_awal, ' - ', s.range_akhir,
                    CASE
                    WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                        THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                    ELSE ' (0)'
                    END
                ) AS stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
                s.notes
            FROM secondary_inhouse_input a
            LEFT JOIN (
                SELECT
                    secondary_inhouse_input.id_qr_stocker,
                    MAX(qty_awal) as qty_awal,
                    SUM(qty_reject) qty_reject,
                    SUM(qty_replace) qty_replace,
                    (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                    MAX(secondary_inhouse_input.urutan) AS max_urutan,
                    GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                    GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                FROM secondary_inhouse_input
                LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                GROUP BY id_qr_stocker
                having MAX(secondary_inhouse_input.urutan) is not null
            ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
            LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
            LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
            LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
            LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
            LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            LEFT JOIN master_part mp ON mp.id = pd.master_part_id
            left join part_detail_secondary pds on pds.part_detail_id = pd.id and pds.urutan = a.urutan
            left join master_secondary mms on mms.id = pds.master_secondary_id
            left join master_secondary ms on ms.id = pd.master_secondary_id
            LEFT JOIN (
                SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                FROM dc_in_input
            ) dc ON a.id_qr_stocker = dc.id_qr_stocker
            WHERE
                a.tgl_trans IS NOT NULL
                and (s.cancel IS NULL OR s.cancel != 'y')
                $additionalQuery
        ";

        return DB::query()->fromRaw("($sql) as sec_out", $bindings);
    }

    /**
     * Satu-satunya sumber query detail (rekap per WS/buyer/style/color/lokasi) Secondary Inhouse Out.
     */
    private function secondaryInhouseOutDetailQuery(Request $request, $from = null, $to = null)
    {
        $where = [];
        $bindings = [];

        $addIn = function ($expr, $values) use (&$where, &$bindings) {
            $values = array_values(array_filter((array) $values, fn ($v) => $v !== null && $v !== ''));
            if (count($values) < 1) {
                return;
            }
            $where[] = "$expr in (" . implode(',', array_fill(0, count($values), '?')) . ")";
            foreach ($values as $v) {
                $bindings[] = trim($v);
            }
        };

        if ($from) {
            $where[] = "(a.tgl_trans >= ?)";
            $bindings[] = $from;
        }
        if ($to) {
            $where[] = "(a.tgl_trans <= ?)";
            $bindings[] = $to;
        }

        $addIn("p.buyer", $request->detail_sec_filter_buyer);
        $addIn("s.act_costing_ws", $request->detail_sec_filter_ws);
        $addIn("p.style", $request->detail_sec_filter_style);
        $addIn("s.color", $request->detail_sec_filter_color);
        $addIn("COALESCE(mx.proses, dc.lokasi)", $request->detail_sec_filter_lokasi);

        $additionalQuery = count($where) > 0 ? " and " . implode(" and ", $where) : "";

        $sql = "
            select
                act_costing_ws, buyer, color, style as styleno, COALESCE(SUM(qty_awal), 0) qty_in, COALESCE(sum(qty_reject), 0) qty_reject, COALESCE(sum(qty_replace), 0) qty_replace, COALESCE(sum(qty_in), 0) qty_out, COALESCE(sum(qty_awal) - sum(qty_in), 0) balance, lokasi
            from
                (
                    SELECT
                        (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                        DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                        a.tgl_trans,
                        s.act_costing_ws,
                        s.color,
                        p.buyer,
                        p.style,
                        COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                        COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                        COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                        COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                        a.created_at,
                        COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                        COALESCE(mx.proses, dc.lokasi) lokasi,
                        dc.tempat,
                        COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                        COALESCE(msb.size, s.size) AS size,
                        a.user,
                        mp.nama_part,
                        CONCAT(
                            s.range_awal, ' - ', s.range_akhir,
                            CASE
                            WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                                THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                            ELSE ' (0)'
                            END
                        ) AS stocker_range_old,
                        CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
                    FROM secondary_inhouse_input a
                    LEFT JOIN (
                        SELECT
                            secondary_inhouse_input.id_qr_stocker,
                            MAX(qty_awal) as qty_awal,
                            SUM(qty_reject) qty_reject,
                            SUM(qty_replace) qty_replace,
                            (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                            MAX(secondary_inhouse_input.urutan) AS max_urutan,
                            GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                            GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                        FROM secondary_inhouse_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                        GROUP BY id_qr_stocker
                        having MAX(secondary_inhouse_input.urutan) is not null
                    ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                    LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
                    LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
                    LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
                    LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
                    LEFT JOIN part p ON pd.part_id = p.id
                    LEFT JOIN master_part mp ON mp.id = pd.master_part_id
                    LEFT JOIN (
                        SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                        FROM dc_in_input
                    ) dc ON a.id_qr_stocker = dc.id_qr_stocker
                    WHERE
                        a.tgl_trans IS NOT NULL
                        AND (
                            a.urutan IS NULL
                            OR a.urutan = mx.max_urutan
                        )
                        $additionalQuery
                    GROUP BY
                        a.id_qr_stocker
                ) a
            GROUP BY
                act_costing_ws,buyer,style,color,lokasi
        ";

        return DB::query()->fromRaw("($sql) as sec_out_detail", $bindings);
    }

    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        // dd($data_rak);
        if ($request->ajax()) {
            // paging, sorting & searching dilakukan di database (client memakai ordering: false, jadi urutan default di sini)
            $query = $this->secondaryInhouseOutQuery($request, $request->dateFrom, $request->dateTo)->orderByDesc('tgl_trans');

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryInhouse(Request $request)
    {
        // hanya batasi tanggal, filter dropdown tidak ikut dipakai di sini
        $base = $this->secondaryInhouseOutQuery(new Request(), $request->dateFrom, $request->dateTo);

        $distinct = fn ($column) => (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column)->values();

        return array(
            "tipe" => $distinct("tipe"),
            "ws" => $distinct("act_costing_ws"),
            "color" => $distinct("color"),
            "buyer" => $distinct("buyer"),
            "style" => $distinct("style"),
            "tujuan" => $distinct("tujuan"),
            "tempat" => $distinct("tempat"),
            "lokasi" => $distinct("lokasi"),
            "panel" => $distinct("panel"),
            "part" => $distinct("nama_part"),
            "no_cut" => $distinct("no_cut"),
            "size" => $distinct("size")
        );
    }

    public function total_secondary_inhouse_out(Request $request)
    {
        $query = $this->secondaryInhouseOutQuery($request, $request->dateFrom, $request->dateTo);

        // filter header per kolom & pencarian global (mengikuti kolom yang tampil di tabel)
        $columns = [
            'tgl_trans_fix', 'id_qr_stocker', 'tipe', 'act_costing_ws', 'style', 'color', 'panel', 'nama_part',
            'size', 'no_cut', 'tujuan', 'lokasi', 'urutan', 'stocker_range', 'qty_awal', 'qty_reject', 'qty_replace',
            'qty_in', 'buyer', 'user', 'created_at',
        ];

        foreach ($columns as $column) {
            if ($request->filled($column)) {
                $query->where($column, 'like', '%' . $request->input($column) . '%');
            }
        }

        if ($request->filled('filter')) {
            $keyword = '%' . $request->input('filter') . '%';

            $query->where(function ($q) use ($columns, $keyword) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', $keyword);
                }
            });
        }

        return $query->selectRaw("
            SUM(qty_awal) as total_qty_awal,
            SUM(qty_reject) as total_qty_reject,
            SUM(qty_replace) as total_qty_replace,
            SUM(qty_in) as total_qty_in
        ")->first();
    }

    public function detail_stocker_inhouse(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $query = $this->secondaryInhouseOutDetailQuery($request, $request->dateFrom, $request->dateTo);

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryInhouse(Request $request)
    {
        $base = $this->secondaryInhouseOutDetailQuery($request, $request->dateFrom, $request->dateTo);

        $distinct = fn ($column) => (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column)->values();

        return array(
            "ws" => $distinct("act_costing_ws"),
            "color" => $distinct("color"),
            "buyer" => $distinct("buyer"),
            "style" => $distinct("styleno"),
            "lokasi" => $distinct("lokasi")
        );
    }

    public function cek_data_stocker_inhouse_old(Request $request)
    {
        $cekdata =  DB::select("
            SELECT
                dc.id_qr_stocker,
                s.act_costing_ws,
                msb.buyer,
                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                msb.styleno as style,
                s.color,
                COALESCE(msb.size, s.size) size,
                mp.nama_part,
                dc.tujuan,
                dc.lokasi,
                COALESCE(sii.id, '-') as in_id,
                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                COALESCE(sii.user, '-') as author_in,
                COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                ifnull(si.id_qr_stocker,'x')
            from dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input a on s.form_cut_id = a.id
                left join form_cut_reject b on s.form_reject_id = b.id
                left join form_cut_piece c on s.form_piece_id = c.id
                left join part_detail p on s.part_detail_id = p.id
                left join master_part mp on p.master_part_id = mp.id
                left join marker_input mi on a.id_marker = mi.kode
                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
            where
                dc.id_qr_stocker =  '" . $request->txtqrstocker . "'
                and dc.tujuan = 'SECONDARY DALAM'
                and ifnull(si.id_qr_stocker,'x') = 'x'
        ");

        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
    }

    public function cek_data_stocker_inhouse(Request $request, SecondaryInhouseService $secondaryInhouseService)
    {
        // When i wrote this code only god and i knew how it worked, now only god knows it
        // Therefore if you trying to optimize this and fail please increase this counter as a warning for the next person
        // total_wasted_hours = 696

        $stocker = Stocker::where('id_qr_stocker', $request->txtqrstocker)->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
            first();

        if ($stocker) {
            // Check Close Order
            if (checkCloseOrder($stocker->id_act_cost)) {
                return "WS '".$stocker->ws."' sudah close order.";
            }

            // Check Part Detail
            $partDetail = $stocker->partDetail;
            if ($partDetail) {

                // Check Part Detail Secondary
                $partDetailSecondary = $partDetail->secondaries;

                if ($partDetailSecondary && $partDetailSecondary->count() > 0) {
                    // If there ain't no urutan
                    if ($stocker->urutan == null) {
                        // Check Secondary Inhouse OUT
                        $secondaryInhouseOut = $secondaryInhouseService->checkSecondaryInhouseOut($request->txtqrstocker);
                        if ($secondaryInhouseOut) {
                            return "Stocker ".$secondaryInhouseOut->id_qr_stocker." sudah discan di Secondary Inhouse OUT pada tanggal ".$secondaryInhouseOut->tgl_trans."";
                        }

                        // Check Secondary Inhouse IN
                        $secondaryInhouseIn = $secondaryInhouseService->checkSecondaryInhouseIn($request->txtqrstocker);
                        if (!$secondaryInhouseIn) {
                            return "Belum di-scan <a href='".route('secondary-inhouse-in')."' target='_blank'>Secondary Inhouse IN</a>";
                        }

                        $cekdata = DB::select("
                            SELECT
                                dc.id_qr_stocker,
                                s.act_costing_ws,
                                msb.buyer,
                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                msb.styleno as style,
                                s.color,
                                COALESCE(msb.size, s.size) size,
                                CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                dc.tujuan,
                                dc.lokasi,
                                COALESCE(s.qty_ply, s.qty_ply_mod) qty_stocker,
                                COALESCE(sii.id, '-') as in_id,
                                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                COALESCE(sii.user, '-') as author_in,
                                COALESCE(sii.qty_in) qty_awal,
                                ifnull(si.id_qr_stocker,'x'),
                                1 as urutan
                            from dc_in_input dc
                                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                left join form_cut_input a on s.form_cut_id = a.id
                                left join form_cut_reject b on s.form_reject_id = b.id
                                left join form_cut_piece c on s.form_piece_id = c.id
                                left join part_detail pd on s.part_detail_id = pd.id
                                left join part p on p.id = pd.part_id
                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                left join part p_com on p_com.id = pd_com.part_id
                                left join master_part mp on pd.master_part_id = mp.id
                                left join marker_input mi on a.id_marker = mi.kode
                                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                            where
                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                and ifnull(si.id_qr_stocker,'x') = 'x'
                        ");

                        return $cekdata && $cekdata[0] ? json_encode($cekdata[0]) : null;
                    }
                    // If there is urutan
                    else {
                        // Current Secondary
                        $currentPartDetailSecondary = $partDetailSecondary->where('urutan', $stocker->urutan)->first();

                        if ($currentPartDetailSecondary && ($currentPartDetailSecondary->secondary && $currentPartDetailSecondary->secondary->tujuan == 'SECONDARY DALAM')) {
                            // Check Secondary Inhouse OUT
                            $secondaryInhouseOut = $secondaryInhouseService->checkSecondaryInhouseOut($request->txtqrstocker, $currentPartDetailSecondary->urutan);
                            if ($secondaryInhouseOut) {
                                return "Stocker ".$secondaryInhouseOut->id_qr_stocker." sudah discan di Secondary Inhouse OUT pada tanggal ".$secondaryInhouseOut->tgl_trans."";
                            }

                            // Check Secondary Inhouse IN
                            $secondaryInhouseIn = $secondaryInhouseService->checkSecondaryInhouseIn($request->txtqrstocker, $currentPartDetailSecondary->urutan);
                            if (!$secondaryInhouseIn) {
                                return "Belum discan <a href='".route('secondary-inhouse-in')."' target='_blank'>Secondary Inhouse IN</a>";
                            }

                            // Check the Secondary Inhouse IN first
                            $cekdata =  DB::select("
                                SELECT
                                    dc.id_qr_stocker,
                                    s.act_costing_ws,
                                    msb.buyer,
                                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                    msb.styleno as style,
                                    s.color,
                                    COALESCE(msb.size, s.size) size,
                                    CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                    CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                    ms.tujuan,
                                    ms.proses lokasi,
                                    COALESCE(s.qty_ply, s.qty_ply_mod) qty_stocker,
                                    COALESCE(sii.id, '-') as in_id,
                                    COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                    COALESCE(sii.user, '-') as author_in,
                                    sii.qty_in qty_awal,
                                    ifnull(si.id_qr_stocker,'x'),
                                    (pds.urutan) as urutan
                                from
                                    dc_in_input dc
                                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                    left join form_cut_input a on s.form_cut_id = a.id
                                    left join form_cut_reject b on s.form_reject_id = b.id
                                    left join form_cut_piece c on s.form_piece_id = c.id
                                    left join part_detail pd on s.part_detail_id = pd.id
                                    left join part p on p.id = pd.part_id
                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                    left join part p_com on p_com.id = pd_com.part_id
                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                    left join master_part mp on pd.master_part_id = mp.id
                                    left join master_secondary ms on pds.master_secondary_id = ms.id
                                    left join marker_input mi on a.id_marker = mi.kode
                                    left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                    left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                where
                                    ms.tujuan = 'SECONDARY DALAM' and
                                    dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                    pds.urutan = '".$currentPartDetailSecondary->urutan."' and
                                    sii.urutan = '".$currentPartDetailSecondary->urutan."' and
                                    sii.id is not null
                            ");

                            if ($cekdata && $cekdata[0]) {
                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }

                            // Check the one step before
                            $multiSecondaryBefore = DB::table("stocker_input")->selectRaw("
                                    stocker_input.id,
                                    stocker_input.id_qr_stocker,
                                    part_detail_secondary.urutan,
                                    master_secondary.tujuan
                                ")->
                                where('id_qr_stocker', $request->txtqrstocker)->
                                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                                leftJoin("part_detail_secondary", "part_detail_secondary.part_detail_id", "=", "part_detail.id")->
                                leftJoin("master_secondary", "master_secondary.id", "=",  "part_detail_secondary.master_secondary_id")->
                                where("part_detail_secondary.urutan", "<", $currentPartDetailSecondary->urutan)->
                                orderBy("part_detail_secondary.urutan", "desc")->
                                first();

                            // When there is a step before
                            if ($multiSecondaryBefore) {

                                // When the tujuan is different
                                if ($multiSecondaryBefore->tujuan != $currentPartDetailSecondary->secondary->tujuan) {

                                    // Check Secondary Before on Secondary In (where the secondary should've finished)
                                    $multiSecondaryBeforeSecondaryIn = DB::table("secondary_in_input")->
                                        where("id_qr_stocker", $request->txtqrstocker)->
                                        where("urutan", $multiSecondaryBefore->urutan)->
                                        first();

                                    // When there is secondary in on the step before then it should pass
                                    if ($multiSecondaryBeforeSecondaryIn) {

                                        // Return the data
                                        $cekdata =  DB::select("
                                            SELECT
                                                dc.id_qr_stocker,
                                                s.act_costing_ws,
                                                msb.buyer,
                                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                msb.styleno as style,
                                                s.color,
                                                COALESCE(msb.size, s.size) size,
                                                CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                ms.tujuan,
                                                ms.proses lokasi,
                                                COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                COALESCE(sii.id, '-') as in_id,
                                                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                                COALESCE(sii.user, '-') as author_in,
                                                ".($multiSecondaryBeforeSecondaryIn->qty_in)." qty_awal,
                                                ifnull(si.id_qr_stocker,'x'),
                                                (pds.urutan) as urutan
                                            from
                                                dc_in_input dc
                                                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join form_cut_input a on s.form_cut_id = a.id
                                                left join form_cut_reject b on s.form_reject_id = b.id
                                                left join form_cut_piece c on s.form_piece_id = c.id
                                                left join part_detail pd on s.part_detail_id = pd.id
                                                left join part p on p.id = pd.part_id
                                                left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                left join part p_com on p_com.id = pd_com.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                                left join master_part mp on pd.master_part_id = mp.id
                                                left join master_secondary ms on pds.master_secondary_id = ms.id
                                                left join marker_input mi on a.id_marker = mi.kode
                                                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                            where
                                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                                ms.tujuan = 'SECONDARY DALAM' and
                                                pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                        ");

                                        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                    }
                                }
                                // When the tujuan is the same
                                else {

                                    // Check the before tujuan
                                    $multiSecondaryBeforeSecondary = null;
                                    if ($multiSecondaryBefore->tujuan == 'SECONDARY DALAM') {
                                        $multiSecondaryBeforeSecondary = DB::table("secondary_inhouse_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();
                                    } else {
                                        $multiSecondaryBeforeSecondary = DB::table("secondary_in_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();
                                    }

                                    // Return the data
                                    $cekdata =  DB::select("
                                        SELECT
                                            dc.id_qr_stocker,
                                            s.act_costing_ws,
                                            msb.buyer,
                                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                            msb.styleno as style,
                                            s.color,
                                            COALESCE(msb.size, s.size) size,
                                            CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                            CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                            ms.tujuan,
                                            ms.proses lokasi,
                                            COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                            COALESCE(sii.id, '-') as in_id,
                                            COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                            COALESCE(sii.user, '-') as author_in,
                                            '".($multiSecondaryBeforeSecondary->qty_in)."' qty_awal,
                                            ifnull(si.id_qr_stocker,'x'),
                                            (pds.urutan) as urutan
                                        from
                                            dc_in_input dc
                                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join form_cut_input a on s.form_cut_id = a.id
                                            left join form_cut_reject b on s.form_reject_id = b.id
                                            left join form_cut_piece c on s.form_piece_id = c.id
                                            left join part_detail pd on s.part_detail_id = pd.id
                                            left join part p on p.id = pd.part_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            left join part p_com on p_com.id = pd_com.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                            left join master_part mp on pd.master_part_id = mp.id
                                            left join master_secondary ms on pds.master_secondary_id = ms.id
                                            left join marker_input mi on a.id_marker = mi.kode
                                            left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                        where
                                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                            ms.tujuan = 'SECONDARY DALAM' and
                                            pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                    ");

                                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                }
                            } else {
                                $cekdata =  DB::select("
                                    SELECT
                                        dc.id_qr_stocker,
                                        s.act_costing_ws,
                                        msb.buyer,
                                        COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                        msb.styleno as style,
                                        s.color,
                                        COALESCE(msb.size, s.size) size,
                                        CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                        CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                        dc.tujuan,
                                        dc.lokasi,
                                        COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                        COALESCE(sii.id, '-') as in_id,
                                        COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                        COALESCE(sii.user, '-') as author_in,
                                        COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                                        ifnull(si.id_qr_stocker,'x'),
                                        1 as urutan
                                    from dc_in_input dc
                                        left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input a on s.form_cut_id = a.id
                                        left join form_cut_reject b on s.form_reject_id = b.id
                                        left join form_cut_piece c on s.form_piece_id = c.id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on p.id = pd.part_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                        left join part p_com on p_com.id = pd_com.part_id
                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                        left join master_part mp on pd.master_part_id = mp.id
                                        left join marker_input mi on a.id_marker = mi.kode
                                        left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = 1
                                        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                    where
                                        dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                        and ifnull(si.id_qr_stocker,'x') = 'x'
                                ");

                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }
                        } else {
                            $message = "";
                            if ($currentPartDetailSecondary && $currentPartDetailSecondary->secondary) {
                                $message = "Proses saat ini : ".$currentPartDetailSecondary->secondary->proses." - ".$currentPartDetailSecondary->secondary->tujuan;
                            } else {
                                $message = "Tidak ditemukan proses Secondary Inhouse OUT";
                            }

                            // Check Secondary Inhouse OUT
                            $secondaryInhouseOut = $secondaryInhouseService->checkSecondaryInhouseOut($request->txtqrstocker);
                            if ($secondaryInhouseOut) {
                                $message .= "<br><br> Stocker ".$secondaryInhouseOut->id_qr_stocker." sudah discan di Secondary Inhouse OUT pada tanggal ".$secondaryInhouseOut->tgl_trans."";
                            }

                            return "Part Detail Secondary tidak sesuai. <br>".$message;
                        }
                    }
                }
                // Default
                else {
                    // Check Secondary Inhouse OUT
                    $secondaryInhouseOut = $secondaryInhouseService->checkSecondaryInhouseOut($request->txtqrstocker);
                    if ($secondaryInhouseOut) {
                        return "Stocker ".$secondaryInhouseOut->id_qr_stocker." sudah discan di Secondary Inhouse OUT pada tanggal ".$secondaryInhouseOut->tgl_trans."";
                    }

                    // Check Secondary Inhouse IN
                    $secondaryInhouseIn = $secondaryInhouseService->checkSecondaryInhouseIn($request->txtqrstocker);
                    if (!$secondaryInhouseIn) {
                        return "Belum discan <a href='".route('secondary-inhouse-in')."' target='_blank'>Secondary Inhouse IN</a>";
                    }


                    $cekdata =  DB::select("
                        SELECT
                            dc.id_qr_stocker,
                            s.act_costing_ws,
                            msb.buyer,
                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                            msb.styleno as style,
                            s.color,
                            COALESCE(msb.size, s.size) size,
                            CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                            CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                            dc.tujuan,
                            dc.lokasi,
                            COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                            COALESCE(sii.id, '-') as in_id,
                            COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                            COALESCE(sii.user, '-') as author_in,
                            COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                            ifnull(si.id_qr_stocker,'x'),
                            1 as urutan
                        from dc_in_input dc
                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            left join form_cut_input a on s.form_cut_id = a.id
                            left join form_cut_reject b on s.form_reject_id = b.id
                            left join form_cut_piece c on s.form_piece_id = c.id
                            left join part_detail pd on s.part_detail_id = pd.id
                            left join part p on p.id = pd.part_id
                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                            left join part p_com on p_com.id = pd_com.part_id
                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                            left join master_part mp on pd.master_part_id = mp.id
                            left join marker_input mi on a.id_marker = mi.kode
                            left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = 1
                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                        where
                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                            and ifnull(si.id_qr_stocker,'x') = 'x'
                    ");

                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                }
            } else {
                return "No Part Detail Found.";
            }
        }

        return "No Stocker Data Found.";
    }

    // public function get_rak(Request $request)
    // {
    //     $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
    //     $html = "<option value=''>Pilih Rak</option>";

    //     foreach ($data_rak as $datarak) {
    //         $html .= " <option value='" . $datarak->isi . "'>" . $datarak->tampil . "</option> ";
    //     }

    //     return $html;
    // }

    public function create()
    {
        return view('dc.secondary-in.create-secondary-in', ['page' => 'dashboard-dc']);
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $validatedRequest = $request->validate([
            "txtqtyreject" => "required",
            "txtqtyreplace" => "required"
        ]);

        $qtyIn = $request['txtqtyawal'] - $request['txtqtyreject'] + $request['txtqtyreplace'];
        if ($qtyIn < 1) {
            return array(
                'status' => 400,
                'message' => 'Qty In tidak bisa kurang dari 1',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        $saveinhouse = SecondaryInhouse::create([
            'tgl_trans' => $tgltrans,
            'id_qr_stocker' => $request['txtno_stocker'],
            'qty_awal' => $request['txtqtyawal'],
            'qty_reject' => $request['txtqtyreject'],
            'qty_replace' => $request['txtqtyreplace'],
            'qty_in' => $qtyIn,
            'user' => Auth::user()->name,
            'urutan' => $request['txturutan'],
            'ket' => $request['txtket'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::update(
            "update stocker_input set status = 'secondary' ".($request->txturutan ? ", urutan = '" . ($request->txturutan + 1) . "'" : "")." where id_qr_stocker = '" . $request->txtno_stocker . "'"
        );

        // dd($savemutasi);
        // $message .= "$tglpindah <br>";

        return array(
            'status' => 300,
            'message' => 'Data Sudah Disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function massStore(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $thisStocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.act_costing_ws, stocker_input.color, COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') as no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
            leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
            where("id_qr_stocker", $request['txtno_stocker'])->
            first();

        if ($thisStocker) {
            $cekdata = DB::select("
                SELECT
                    dc.id_qr_stocker,
                    s.act_costing_ws,
                    msb.buyer,
                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                    p.style,
                    COALESCE(msb.size, s.size) AS size,
                    a.user,
                    mp.nama_part,
                    UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) part_status,
                    CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
                    COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                    (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                    ifnull( si.id_qr_stocker, 'x' )
                FROM
                    dc_in_input dc
                    LEFT JOIN stocker_input s ON dc.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    LEFT JOIN form_cut_input a ON s.form_cut_id = a.id
                    LEFT JOIN form_cut_reject b ON s.form_reject_id = b.id
                    LEFT JOIN form_cut_piece c ON s.form_piece_id = c.id
                    LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
                    LEFT JOIN part p ON p.id = pd.part_id
                    LEFT JOIN master_part mp ON pd.master_part_id = mp.id
                    LEFT JOIN marker_input mi ON a.id_marker = mi.kode
                    LEFT JOIN secondary_inhouse_input si ON dc.id_qr_stocker = si.id_qr_stocker
                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                WHERE
                    s.act_costing_ws = '".$thisStocker->act_costing_ws."' AND
                    s.color = '".$thisStocker->color."' AND
                    COALESCE(a.no_cut, c.no_cut, '-') = '".$thisStocker->no_cut."'
                    AND dc.tujuan = 'SECONDARY DALAM'
                    AND ifnull( si.id_qr_stocker, 'x' ) = 'x'
            ");

            foreach ($cekdata as $d) {
                $saveinhouse = SecondaryInhouse::create([
                    'tgl_trans' => $tgltrans,
                    'id_qr_stocker' => $d->id_qr_stocker,
                    'qty_awal' => $d->qty_awal,
                    'qty_reject' => 0,
                    'qty_replace' => 0,
                    'qty_in' => $d->qty_awal,
                    'user' => Auth::user()->name,
                    'ket' => '',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);


                DB::update(
                    "update stocker_input set status = 'secondary' where id_qr_stocker = '" . $d->id_qr_stocker . "'"
                );
            }

            // dd($savemutasi);
            // $message .= "$tglpindah <br>";

            return array(
                'status' => 300,
                'message' => 'Data Sudah Disimpan',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data gagal Disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $data = $this->secondaryInhouseOutQuery($request, $request->from, $request->to)->orderByDesc('tgl_trans')->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary InHouse Out Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary InHouse Out Report', ['font-size' => 16]);
        $sheet->mergeCells('A1:V1');

        // Headers
        $sheet->writeTo('A2', 'Tgl Transaksi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'ID QR')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Part Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Panel Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', 'No. Cut')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', 'Tujuan Asal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', 'Lokasi Asal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', 'Range')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', 'Qty Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', 'Qty Reject')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', 'Qty Replace')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', 'Urutan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('T2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('U2', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('V2', 'Created At')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('W2', 'Notes')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->tgl_trans_fix ?? "-",
                    $row->id_qr_stocker ?? "-",
                    $row->act_costing_ws ?? "-",
                    $row->style ?? "-",
                    $row->color ?? "-",
                    $row->nama_part_only ? preg_replace('/\s+/', ' ', $row->nama_part_only) : "-",
                    $row->part_status ?? "-",
                    $row->panel_only ? preg_replace('/\s+/', ' ', $row->panel_only) : "-",
                    $row->panel_status ?? "-",
                    $row->size ?? "-",
                    $row->no_cut ?? "-",
                    $row->tujuan ?? "-",
                    $row->lokasi ?? "-",
                    $row->stocker_range ?? "-",
                    intval($row->qty_awal) ?? 0,
                    intval($row->qty_reject) ?? 0,
                    intval($row->qty_replace) ?? 0,
                    intval($row->qty_in) ?? 0,
                    $row->urutan ?? "-",
                    $row->buyer ?? "-",
                    $row->user ?? "-",
                    $row->created_at ?? "-",
                    $row->notes ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan Secondary Inhouse OUT '.$from.' - '.$to.' ('.Carbon::now()->format('Y-m-d H:i:s').').xlsx';

        return $excel->download($filename);
    }

    // public function exportExcel1(Request $request)
    // {
    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $from = $request->from ? $request->from : date('Y-m-d');
    //     $to = $request->to ? $request->to : date('Y-m-d');

    //     $additionalQuery = "";

    //     if ($request->from) {
    //         $additionalQuery .= " and a.tgl_trans >= '" . $request->from . "' ";
    //     }

    //     if ($request->to) {
    //         $additionalQuery .= " and a.tgl_trans <= '" . $request->to . "' ";
    //     }

    //     $data = DB::select("
    //         SELECT
    //             a.*,
    //             (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
    //             DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
    //             a.tgl_trans,
    //             s.act_costing_ws,
    //             s.color,
    //             p.buyer,
    //             p.style,
    //             CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
    //             COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
    //             COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
    //             COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
    //             COALESCE(a.qty_in) qty_in,
    //             a.created_at,
    //             COALESCE(mx.tujuan, dc.tujuan) as tujuan,
    //             COALESCE(mx.proses, dc.lokasi) lokasi,
    //             dc.tempat,
    //             COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
    //             COALESCE(msb.size, s.size) AS size,
    //             a.user,
    //             CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
    //             CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
    //             s.notes
    //         FROM secondary_inhouse_input a
    //         LEFT JOIN (
    //             SELECT
    //                 secondary_inhouse_input.id_qr_stocker,
    //                 MAX(qty_awal) as qty_awal,
    //                 SUM(qty_reject) qty_reject,
    //                 SUM(qty_replace) qty_replace,
    //                 (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
    //                 MAX(secondary_inhouse_input.urutan) AS max_urutan,
    //                 GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
    //                 GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
    //             FROM secondary_inhouse_input
    //             LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
    //             LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
    //             LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
    //             GROUP BY id_qr_stocker
    //             having MAX(secondary_inhouse_input.urutan) is not null
    //         ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
    //         LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
    //         LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
    //         LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
    //         LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
    //         LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
    //         LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
    //         LEFT JOIN part p ON pd.part_id = p.id
    //         LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail
    //         LEFT JOIN part p_com ON p_com.id = pd_com.part_id
    //         LEFT JOIN master_part mp ON mp.id = pd.master_part_id
    //         LEFT JOIN (select id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat from dc_in_input) dc ON a.id_qr_stocker = dc.id_qr_stocker
    //         WHERE a.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
    //         ".$additionalQuery."
    //         ORDER BY a.tgl_trans DESC
    //     ");

    //     // Create Excel file using FastExcel
    //     $excel = FastExcel::create('Secondary InHouse Out Report');
    //     $sheet = $excel->getSheet();

    //     // Title
    //     $sheet->writeTo('A1', 'Secondary InHouse Out Report', ['font-size' => 16]);
    //     $sheet->mergeCells('A1:V1');

    //     // Headers
    //     $sheet->writeTo('A2', 'Tgl Transaksi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('B2', 'ID QR')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('C2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('E2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('F2', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('G2', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('H2', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('I2', 'No. Cut')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('J2', 'Qty Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('K2', 'Qty Reject')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('L2', 'Qty Replace')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('M2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('N2', 'Tujuan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('O2', 'Tempat')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('P2', 'Lokasi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('Q2', 'Range')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('R2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('S2', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('T2', 'Created At')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('U2', 'Tipe')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $sheet->writeTo('V2', 'Notes')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
    //         $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $row->tgl_trans_fix ?? "-",
    //                 $row->id_qr_stocker ?? "-",
    //                 $row->act_costing_ws ?? "-",
    //                 $row->style ?? "-",
    //                 $row->color ?? "-",
    //                 $row->nama_part ?? "-",
    //                 $row->panel ?? "-",
    //                 $row->size ?? "-",
    //                 $row->no_cut ?? "-",
    //                 $row->qty_awal ?? "-",
    //                 $row->qty_reject ?? "-",
    //                 $row->qty_replace ?? "-",
    //                 $row->qty_in ?? "-",
    //                 $row->tujuan ?? "-",
    //                 $row->tempat ?? "-",
    //                 $row->lokasi ?? "-",
    //                 $row->stocker_range ?? "-",
    //                 $row->buyer ?? "-",
    //                 $row->user ?? "-",
    //                 $row->created_at ?? "-",
    //                 $row->tipe ?? "-",
    //                 $row->notes ?? "-",
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = 'Laporan sec inhouse ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

    //     return $excel->download($filename);
    // }

    public function exportExcelDetail(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $data = $this->secondaryInhouseOutQuery($request, $request->from, $request->to)->orderByDesc('tgl_trans')->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary InHouse Out Detail Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary InHouse Out Detail Report', ['font-size' => 16]);
        $sheet->mergeCells('A1:O1');

        // Headers
        $sheet->writeTo('A2', 'Tgl Transaksi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'ID QR')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Part Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Panel Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', 'No. Cut')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', 'Qty')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', 'Range')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', 'Tipe')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->tgl_trans_fix ?? "-",
                    $row->id_qr_stocker ?? "-",
                    $row->act_costing_ws ?? "-",
                    $row->style ?? "-",
                    $row->color ?? "-",
                    $row->nama_part ?? "-",
                    $row->part_status ?? "-",
                    $row->panel ?? "-",
                    $row->panel_status ?? "-",
                    $row->size ?? "-",
                    $row->no_cut ?? "-",
                    $row->qty_in ?? "-",
                    $row->stocker_range ?? "-",
                    $row->user ?? "-",
                    $row->tipe ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan sec inhouse detail ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }
}
