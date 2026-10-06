<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dc\RackDetailStocker;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\Trolley;
use App\Models\Dc\TrolleyStocker;
use App\Models\Stocker\Stocker;
use App\Models\Dc\LoadingLine;
use App\Exports\DC\ExportSecondaryIn;
use App\Exports\DC\ExportSecondaryInDetail;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Carbon\Carbon;
use DB;

class SecondaryInController extends Controller
{
    /**
     * Satu-satunya sumber query list Secondary In (tabel, filter dropdown, total, export).
     * Query = secondary_in_input UNION ALL secondary_in_update, dikembalikan sebagai query builder
     * di atas subquery `sec_in` sehingga paging/sort/sum/distinct dikerjakan database, bukan PHP.
     *
     * Catatan kolom:
     * - `panel` & `nama_part` adalah versi tampil di tabel (dengan status), `panel_only` & `nama_part_only` versi polos.
     * - `qty_*`, `tujuan`, `lokasi` adalah nilai tampil di tabel (agregat `mx`), sedangkan `exp_*` adalah nilai per baris
     *   yang dipakai export Excel.
     */
    private function secondaryInQuery(Request $request, $from = null, $to = null)
    {
        $tipeExpr = "(CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END)";
        $panelRaw = "(CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END)";
        $panelStatusRaw = "(CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)";
        $panelExpr = "CONCAT($panelRaw, (CASE WHEN $panelStatusRaw IS NOT NULL THEN CONCAT(' - ', $panelStatusRaw) ELSE '' END))";
        $partStatusExpr = "UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))";
        $partExpr = "CONCAT(mp.nama_part, (CASE WHEN $partStatusExpr != '-' THEN CONCAT(' - ', $partStatusExpr) ELSE '' END))";

        $tujuanInput = "COALESCE(mx.tujuan, ms.tujuan, dc.tujuan)";
        $lokasiInput = "COALESCE(mx.proses, ms.proses, dc.lokasi)";
        $tujuanUpdate = "COALESCE(mms.tujuan, ms.tujuan, dc.tujuan)";
        $lokasiUpdate = "COALESCE(mms.proses, ms.proses, dc.lokasi)";

        // kondisi WHERE per cabang (tujuan & lokasi berbeda ekspresi antara input dan update)
        $buildWhere = function ($tujuanExpr, $lokasiExpr) use ($request, $from, $to, $tipeExpr, $panelExpr, $partExpr) {
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
            $addIn("COALESCE(s.lokasi, '-')", $request->sec_filter_tempat);
            $addIn($lokasiExpr, $request->sec_filter_lokasi);
            $addIn("COALESCE(s.lokasi, '-')", $request->sec_filter_lokasi_rak);

            return [count($where) > 0 ? " and " . implode(" and ", $where) : "", $bindings];
        };

        [$whereInput, $bindingsInput] = $buildWhere($tujuanInput, $lokasiInput);
        [$whereUpdate, $bindingsUpdate] = $buildWhere($tujuanUpdate, $lokasiUpdate);

        $mxJoin = "
            SELECT
                secondary_in_input.id_qr_stocker,
                MAX(qty_awal) as qty_awal,
                SUM(qty_reject) qty_reject,
                SUM(qty_replace) qty_replace,
                (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                MAX(secondary_in_input.urutan) AS max_urutan,
                GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
            FROM secondary_in_input
            LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
            LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_in_input.urutan
            LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
            GROUP BY id_qr_stocker
            having MAX(secondary_in_input.urutan) is not null
        ";

        $stockerRangeOld = "CONCAT(s.range_awal, ' - ', s.range_akhir,
            (
                CASE WHEN (mx.qty_reject IS NOT NULL AND mx.qty_replace IS NOT NULL) THEN
                    (CONCAT(' (', (COALESCE(mx.qty_replace, 0) - COALESCE(mx.qty_reject, 0)), ') ')) ELSE
                    (
                        CASE WHEN ((dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL) OR (sii.qty_reject IS NOT NULL AND sii.qty_replace IS NOT NULL)) THEN
                            CONCAT(' (', ((COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)) + (COALESCE(sii.qty_replace, 0) - COALESCE(sii.qty_reject, 0))), ') ') ELSE
                            ' (0)'
                        END
                    )
                END
            )
        )";

        $sql = "
            SELECT
                a.id_qr_stocker,
                $tipeExpr tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                $panelExpr panel,
                $panelRaw panel_only,
                $panelStatusRaw panel_status,
                $tujuanInput tujuan,
                $lokasiInput lokasi,
                COALESCE(s.lokasi, '-') lokasi_rak,
                COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                COALESCE(a.qty_in) qty_in,
                $partExpr nama_part,
                mp.nama_part nama_part_only,
                $partStatusExpr part_status,
                a.created_at,
                $stockerRangeOld stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
                COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                COALESCE(msb.size, s.size) size,
                a.user,
                (CASE WHEN a.urutan > 0 THEN a.urutan ELSE '-' END) urutan,
                s.notes,
                a.qty_awal exp_qty_awal,
                a.qty_reject exp_qty_reject,
                a.qty_replace exp_qty_replace,
                COALESCE(a.qty_in) exp_qty_in,
                $tujuanUpdate exp_tujuan,
                $lokasiUpdate exp_lokasi
            from secondary_in_input a
            LEFT JOIN ($mxJoin) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on fr.id = s.form_reject_id
            left join form_cut_piece fp on fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            left join master_secondary ms on ms.id = pd.master_secondary_id
            left join part_detail_secondary pds on pds.part_detail_id = pd.id and pds.urutan = a.urutan
            left join master_secondary mms on mms.id = pds.master_secondary_id
            left join master_part mp on mp.id = pd.master_part_id
            left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
            where
                a.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
                $whereInput
            group by a.id
            UNION ALL
            SELECT
                b.id_qr_stocker,
                $tipeExpr tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                $panelExpr panel,
                $panelRaw panel_only,
                $panelStatusRaw panel_status,
                $tujuanUpdate tujuan,
                $lokasiUpdate lokasi,
                COALESCE(s.lokasi, '-') lokasi_rak,
                0 qty_awal,
                COALESCE(a.reject, 0) qty_reject,
                COALESCE(a.replace, 0) qty_replace,
                (0 - COALESCE(a.reject, 0) + COALESCE(a.replace, 0)) qty_in,
                $partExpr nama_part,
                mp.nama_part nama_part_only,
                $partStatusExpr part_status,
                a.created_at,
                CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
                COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                COALESCE(msb.size, s.size) size,
                COALESCE(a.created_by_username, b.user) user,
                (CASE WHEN b.urutan > 0 THEN b.urutan ELSE '-' END) urutan,
                s.notes,
                0 exp_qty_awal,
                a.reject exp_qty_reject,
                a.replace exp_qty_replace,
                (0 - COALESCE(a.reject, 0)) exp_qty_in,
                $tujuanUpdate exp_tujuan,
                $lokasiUpdate exp_lokasi
            from secondary_in_update a
            left join secondary_in_input b on b.id = a.secondary_in_id
            LEFT JOIN ($mxJoin) mx ON b.id_qr_stocker = mx.id_qr_stocker AND b.urutan = mx.max_urutan
            left join stocker_input s on b.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on fr.id = s.form_reject_id
            left join form_cut_piece fp on fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            left join master_secondary ms on ms.id = pd.master_secondary_id
            left join part_detail_secondary pds on pds.part_detail_id = pd.id and pds.urutan = b.urutan
            left join master_secondary mms on mms.id = pds.master_secondary_id
            left join master_part mp on mp.id = pd.master_part_id
            left join dc_in_input dc on b.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input sii on b.id_qr_stocker = sii.id_qr_stocker
            where
                b.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
                $whereUpdate
            group by a.id
        ";

        return DB::query()->fromRaw("($sql) as sec_in", array_merge($bindingsInput, $bindingsUpdate));
    }

    /**
     * Satu-satunya sumber query detail (rekap per WS/buyer/style/color/lokasi) Secondary In.
     */
    private function secondaryInDetailQuery(Request $request, $from = null, $to = null)
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
        $addIn("COALESCE(mx.proses, ms.proses, dc.lokasi)", $request->detail_sec_filter_lokasi);

        $additionalQuery = count($where) > 0 ? " and " . implode(" and ", $where) : "";

        $sql = "
            select
                act_costing_ws, buyer, color, style as styleno, COALESCE(sum(qty_awal), 0) qty_in, COALESCE(sum(qty_reject), 0) qty_reject, COALESCE(sum(qty_replace), 0) qty_replace, COALESCE(sum(qty_in), 0) qty_out, COALESCE(sum(qty_awal - qty_in), 0) balance, tujuan, lokasi
            from
                (
                    SELECT
                        a.id_qr_stocker,
                        (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                        DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                        a.tgl_trans,
                        s.act_costing_ws,
                        s.color,
                        p.buyer,
                        p.style,
                        COALESCE(mx.tujuan, ms.tujuan, dc.tujuan) tujuan,
                        COALESCE(mx.proses, ms.proses, dc.lokasi) lokasi,
                        COALESCE(s.lokasi, '-') lokasi_rak,
                        COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                        COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                        COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                        COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                        a.created_at,
                        COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                        COALESCE(msb.size, s.size) size,
                        a.user,
                        mp.nama_part,
                        a.urutan
                    from secondary_in_input a
                    LEFT JOIN (
                        SELECT
                            secondary_in_input.id_qr_stocker,
                            MAX(qty_awal) as qty_awal,
                            SUM(qty_reject) qty_reject,
                            SUM(qty_replace) qty_replace,
                            (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                            MAX(secondary_in_input.urutan) AS max_urutan,
                            GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                            GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                        FROM secondary_in_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_in_input.urutan
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                        GROUP BY id_qr_stocker
                        having MAX(secondary_in_input.urutan) is not null
                    ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                    left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                    left join form_cut_input f on f.id = s.form_cut_id
                    left join form_cut_reject fr on fr.id = s.form_reject_id
                    left join form_cut_piece fp on fp.id = s.form_piece_id
                    left join part_detail pd on s.part_detail_id = pd.id
                    left join part p on pd.part_id = p.id
                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                    left join master_secondary ms on ms.id = pd.master_secondary_id
                    left join master_part mp on mp.id = pd.master_part_id
                    left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
                    where
                        a.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
                        AND (
                            a.urutan IS NULL
                            OR a.urutan = mx.max_urutan
                        )
                        $additionalQuery
                    group by a.id
                ) a
            group by
                act_costing_ws,buyer,style,color,lokasi
        ";

        return DB::query()->fromRaw("($sql) as sec_in_detail", $bindings);
    }

    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        $data_trolley = DB::select("select nama_trolley isi, nama_trolley tampil from trolley");
        // dd($data_rak);
        if ($request->ajax()) {
            // paging, sorting & searching dilakukan di database (client memakai ordering: false, jadi urutan default di sini)
            $query = $this->secondaryInQuery($request, $request->dateFrom, $request->dateTo)->orderByDesc('tgl_trans');

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-in.secondary-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-in", "data_rak" => $data_rak, "data_trolley" => $data_trolley], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryIn(Request $request)
    {
        // hanya batasi tanggal, filter dropdown tidak ikut dipakai di sini
        $base = $this->secondaryInQuery(new Request(), $request->dateFrom, $request->dateTo);

        $distinct = fn ($column) => (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column)->values();

        return array(
            "tipe" => $distinct("tipe"),
            "ws" => $distinct("act_costing_ws"),
            "color" => $distinct("color"),
            "buyer" => $distinct("buyer"),
            "style" => $distinct("style"),
            "tujuan" => $distinct("tujuan"),
            "lokasi" => $distinct("lokasi"),
            "lokasi_rak" => $distinct("lokasi_rak"),
            "panel" => $distinct("panel"),
            "part" => $distinct("nama_part"),
            "no_cut" => $distinct("no_cut"),
            "size" => $distinct("size")
        );
    }

    public function total_secondary_in(Request $request)
    {
        $query = $this->secondaryInQuery($request, $request->dateFrom, $request->dateTo);

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
            SUM(qty_awal) total_qty_awal,
            SUM(qty_reject) total_qty_reject,
            SUM(qty_replace) total_qty_replace,
            SUM(qty_in) total_qty_in
        ")->first();
    }

    public function detail_stocker_in(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $query = $this->secondaryInDetailQuery($request, $request->dateFrom, $request->dateTo);

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-in.secondary-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-in"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryIn(Request $request)
    {
        $base = $this->secondaryInDetailQuery($request, $request->dateFrom, $request->dateTo);

        $distinct = fn ($column) => (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column)->values();

        return array(
            "ws" => $distinct("act_costing_ws"),
            "color" => $distinct("color"),
            "buyer" => $distinct("buyer"),
            "style" => $distinct("styleno"),
            "lokasi" => $distinct("lokasi")
        );
    }

    public function cek_data_stocker_in_old(Request $request)
    {
        $cekdata =  DB::select("
            select
            s.id_qr_stocker,
            s.act_costing_ws,
            msb.buyer,
            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
            msb.styleno as style,
            s.color,
            COALESCE(msb.size, s.size) size,
            dc.tujuan,
            dc.lokasi,
            mp.nama_part,
            if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
            s.lokasi lokasi_tujuan,
            s.tempat tempat_tujuan,
            md.sec_in_stocker,
            md.sec_in_created_at
            from
            (
                select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                where dc.tujuan = 'SECONDARY DALAM' and
                ifnull(si.id_qr_stocker,'x') != 'x'
                union
                select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                where dc.tujuan = 'SECONDARY LUAR'
            ) md
            left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input a on s.form_cut_id = a.id
            left join form_cut_reject b on s.form_reject_id = b.id
            left join form_cut_piece c on s.form_piece_id = c.id
            left join part_detail p on s.part_detail_id = p.id
            left join master_part mp on p.master_part_id = mp.id
            left join marker_input mi on a.id_marker = mi.kode
            left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
            where s.id_qr_stocker = '" . $request->txtqrstocker . "'
        ");

        if ($cekdata && $cekdata[0] && $cekdata[0]->sec_in_stocker) {
            return array([
                "status" => 400,
                "message" => "Stocker <b>'".$cekdata[0]->sec_in_stocker."'</b> sudah masuk Secondary IN pada <b>'".$cekdata[0]->sec_in_created_at."'</b> <br>",
            ]);
        }

        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
    }

    public function cek_data_stocker_in(Request $request)
    {
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
                    if (!$stocker->urutan) {
                        $cekdata = DB::select("
                            select
                                s.id_qr_stocker,
                                s.act_costing_ws,
                                msb.buyer,
                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                msb.styleno as style,
                                s.color,
                                COALESCE(msb.size, s.size) size,
                                dc.tujuan,
                                dc.lokasi,
                                CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
                                s.lokasi lokasi_tujuan,
                                s.tempat tempat_tujuan,
                                1 urutan,
                                (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND 1 >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status,
                                max_urutan.max_urutan,
                                md.sec_in_stocker,
                                md.sec_in_created_at
                            from
                                (
                                    select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                    left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                    left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                    where dc.tujuan = 'SECONDARY DALAM' and
                                    ifnull(si.id_qr_stocker,'x') != 'x'
                                    union
                                    select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                    left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                    where dc.tujuan = 'SECONDARY LUAR'
                                ) md
                                left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                left join form_cut_input a on s.form_cut_id = a.id
                                left join form_cut_reject b on s.form_reject_id = b.id
                                left join form_cut_piece c on s.form_piece_id = c.id
                                left join part_detail pd on s.part_detail_id = pd.id
                                left join part p on p.id = pd.part_id
                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                left join part p_com on p_com.id = pd_com.part_id
                                left join (
                                    select
                                        part_detail_id,
                                        MAX(part_detail_secondary.urutan) max_urutan
                                    from
                                        part_detail_secondary
                                    WHERE
                                        part_detail_secondary.urutan IS NOT NULL
                                    group by
                                        part_detail_id
                                ) max_urutan on max_urutan.part_detail_id = pd.id
                                left join master_part mp on pd.master_part_id = mp.id
                                left join marker_input mi on a.id_marker = mi.kode
                                left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                            where s.id_qr_stocker = '" . $request->txtqrstocker . "' and (act.close_order is null or act.close_order != 'Y')
                        ");

                        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                    }
                    // If there is urutan
                    else {

                        // Current Secondary
                        $currentPartDetailSecondary = $partDetailSecondary->where('urutan', $stocker->urutan)->first();

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
                            where("part_detail_secondary.urutan", "<", $stocker->urutan)->
                            orderBy("part_detail_secondary.urutan", "desc")->
                            first();

                        // Check the step after
                        $multiSecondaryAfter = DB::table("stocker_input")->selectRaw("
                                stocker_input.id,
                                stocker_input.id_qr_stocker,
                                part_detail_secondary.urutan,
                                master_secondary.tujuan
                            ")->
                            where('id_qr_stocker', $request->txtqrstocker)->
                            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                            leftJoin("part_detail_secondary", "part_detail_secondary.part_detail_id", "=", "part_detail.id")->
                            leftJoin("master_secondary", "master_secondary.id", "=",  "part_detail_secondary.master_secondary_id")->
                            where("part_detail_secondary.urutan", ">", $stocker->urutan)->
                            orderBy("part_detail_secondary.urutan", "desc")->
                            first();

                        // If there is another step
                        if ($currentPartDetailSecondary && $currentPartDetailSecondary->secondary) {

                            // If Secondary Dalam
                            if ($currentPartDetailSecondary->secondary->tujuan == "SECONDARY DALAM") {

                                // Check current secondary inhouse
                                $multiSecondaryCurrentSecondary = DB::table("secondary_inhouse_input")->
                                    where("id_qr_stocker", $request->txtqrstocker)->
                                    where("urutan", $currentPartDetailSecondary->urutan)->
                                    first();

                                // If there is current secondary
                                if ($multiSecondaryCurrentSecondary) {

                                    // If one step after
                                    if ($multiSecondaryAfter) {

                                        // If it wasn't secondary dalam then
                                        if ($multiSecondaryAfter->tujuan != "SECONDARY DALAM") {

                                            // Return the data for Secondary Dalam
                                            $cekdata = DB::select("
                                                select
                                                    s.id_qr_stocker,
                                                    s.act_costing_ws,
                                                    msb.buyer,
                                                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                    msb.styleno as style,
                                                    s.color,
                                                    COALESCE(msb.size, s.size) size,
                                                    ms.tujuan,
                                                    ms.proses lokasi,
                                                    CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                    CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                    COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                    ".$multiSecondaryCurrentSecondary->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$multiSecondaryCurrentSecondary->urutan." as urutan,
                                                    (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$multiSecondaryCurrentSecondary->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status
                                                from
                                                    stocker_input
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join form_cut_input a on s.form_cut_id = a.id
                                                    left join form_cut_reject b on s.form_reject_id = b.id
                                                    left join form_cut_piece c on s.form_piece_id = c.id
                                                    left join part_detail pd on s.part_detail_id = pd.id
                                                    left join part p on p.id = pd.part_id
                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                    left join part p_com on p_com.id = pd_com.part_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_part mp on pd.master_part_id = mp.id
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                    left join (
                                                        select
                                                            part_detail_id,
                                                            MAX(part_detail_secondary.urutan) max_urutan
                                                        from
                                                            part_detail_secondary
                                                        WHERE
                                                            part_detail_secondary.urutan IS NOT NULL
                                                        group by
                                                            part_detail_id
                                                    ) max_urutan on max_urutan.part_detail_id = pd.id
                                                    left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY DALAM' and
                                                    pds.urutan = '".$multiSecondaryCurrentSecondary->urutan."'
                                                    and (act.close_order is null or act.close_order != 'Y')
                                            ");

                                            return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                        }
                                        // If it was secondary dalam
                                        else {
                                            return "Harap langsung scan di secondary dalam untuk proses selanjutnya.";
                                        }
                                    } else {
                                        // Return the data for Secondary Dalam
                                        $cekdata = DB::select("
                                            select
                                                s.id_qr_stocker,
                                                s.act_costing_ws,
                                                msb.buyer,
                                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                msb.styleno as style,
                                                s.color,
                                                COALESCE(msb.size, s.size) size,
                                                ms.tujuan,
                                                ms.proses lokasi,
                                                CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                ".$multiSecondaryCurrentSecondary->qty_in." qty_awal,
                                                s.lokasi lokasi_tujuan,
                                                s.tempat tempat_tujuan,
                                                ".$multiSecondaryCurrentSecondary->urutan." as urutan,
                                                (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$multiSecondaryCurrentSecondary->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status
                                            from
                                                stocker_input s
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
                                                left join master_secondary ms on ms.id = pds.master_secondary_id
                                                left join marker_input mi on a.id_marker = mi.kode
                                                left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                left join (
                                                    select
                                                        part_detail_id,
                                                        MAX(part_detail_secondary.urutan) max_urutan
                                                    from
                                                        part_detail_secondary
                                                    WHERE
                                                        part_detail_secondary.urutan IS NOT NULL
                                                    group by
                                                        part_detail_id
                                                ) max_urutan on max_urutan.part_detail_id = pd.id
                                                 left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                            where
                                                s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                ms.tujuan = 'SECONDARY DALAM' and
                                                pds.urutan = '".$multiSecondaryCurrentSecondary->urutan."'
                                                and (act.close_order is null or act.close_order != 'Y')
                                        ");

                                        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                    }
                                } else {

                                    $additional = "";
                                    if ($currentPartDetailSecondary->secondary) {
                                        // Secondary Inhouse
                                        $route = "#";
                                        if ($currentPartDetailSecondary->secondary->tujuan == "SECONDARY DALAM") {
                                            $route = route('secondary-inhouse');
                                        }
                                        $additional .= "<br> Proses saat ini : <a href='".$route."' target='_blank'><b>".$currentPartDetailSecondary->secondary->tujuan." / ".$currentPartDetailSecondary->secondary->proses."</b></a>";
                                    }

                                    return "Secondary Inhouse belum ada".$additional;
                                }
                            }
                            // If Secondary Luar
                            else if ($currentPartDetailSecondary->secondary->tujuan == "SECONDARY LUAR") {
                                // When there is a step before
                                if ($multiSecondaryBefore) {
                                    // If Secondary Dalam
                                    if ($multiSecondaryBefore->tujuan == "SECONDARY DALAM") {

                                        // Check current secondary inhouse
                                        $multiSecondaryBeforeSecondary = DB::table("secondary_inhouse_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();

                                        // If there is secondary inhouse (it should always be there)
                                        if ($multiSecondaryBeforeSecondary) {

                                            // Check the secondary in data
                                            $multiSecondaryBeforeSecondaryIn = DB::table("secondary_in_input")->
                                                where("id_qr_stocker", $request->txtqrstocker)->
                                                where("urutan", $multiSecondaryBefore->urutan)->
                                                first();

                                            // If there is secondary in
                                            if ($multiSecondaryBeforeSecondaryIn) {

                                                // Return the data for Secondary Luar
                                                $cekdata = DB::select("
                                                    select
                                                        s.id_qr_stocker,
                                                        s.act_costing_ws,
                                                        msb.buyer,
                                                        COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                        msb.styleno as style,
                                                        s.color,
                                                        COALESCE(msb.size, s.size) size,
                                                        ms.tujuan,
                                                        ms.proses lokasi,
                                                        CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                        CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                        COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                        ".$multiSecondaryBeforeSecondaryIn->qty_in." qty_awal,
                                                        s.lokasi lokasi_tujuan,
                                                        s.tempat tempat_tujuan,
                                                        ".$currentPartDetailSecondary->urutan." as urutan,
                                                        (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$currentPartDetailSecondary->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status
                                                    from
                                                        stocker_input s
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
                                                        left join master_secondary ms on ms.id = pds.master_secondary_id
                                                        left join marker_input mi on a.id_marker = mi.kode
                                                        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                        left join (
                                                            select
                                                                part_detail_id,
                                                                MAX(part_detail_secondary.urutan) max_urutan
                                                            from
                                                                part_detail_secondary
                                                            WHERE
                                                                part_detail_secondary.urutan IS NOT NULL
                                                            group by
                                                                part_detail_id
                                                        ) max_urutan on max_urutan.part_detail_id = pd.id
                                                         left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                                    where
                                                        s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                        ms.tujuan = 'SECONDARY LUAR' and
                                                        pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                                        and (act.close_order is null or act.close_order != 'Y')
                                                ");

                                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                            }
                                            // If there is no secondary in
                                            else {
                                                // Return the data for Secondary Dalam
                                                $cekdata = DB::select("
                                                    select
                                                        s.id_qr_stocker,
                                                        s.act_costing_ws,
                                                        msb.buyer,
                                                        COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                        msb.styleno as style,
                                                        s.color,
                                                        COALESCE(msb.size, s.size) size,
                                                        ms.tujuan,
                                                        ms.proses lokasi,
                                                        CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                        CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                        COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                        ".$multiSecondaryBeforeSecondary->qty_in." qty_awal,
                                                        s.lokasi lokasi_tujuan,
                                                        s.tempat tempat_tujuan,
                                                        ".$multiSecondaryBefore->urutan." as urutan,
                                                        (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$multiSecondaryBefore->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status
                                                    from
                                                        stocker_input s
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
                                                        left join master_secondary ms on ms.id = pds.master_secondary_id
                                                        left join marker_input mi on a.id_marker = mi.kode
                                                        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                        left join (
                                                            select
                                                                part_detail_id,
                                                                MAX(part_detail_secondary.urutan) max_urutan
                                                            from
                                                                part_detail_secondary
                                                            WHERE
                                                                part_detail_secondary.urutan IS NOT NULL
                                                            group by
                                                                part_detail_id
                                                        ) max_urutan on max_urutan.part_detail_id = pd.id
                                                         left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                                    where
                                                        s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                        ms.tujuan = 'SECONDARY DALAM' and
                                                        pds.urutan = '".$multiSecondaryBefore->urutan."'
                                                        and (act.close_order is null or act.close_order != 'Y')
                                                ");

                                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                            }
                                        } else {
                                            return "Data belum di scan secondary dalam";
                                        }
                                    } else {
                                        // Check the secondary in data
                                        $multiSecondaryBeforeSecondaryIn = DB::table("secondary_in_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();

                                        // If there is secondary in
                                        if ($multiSecondaryBeforeSecondaryIn) {

                                            // Return the data for Secondary Luar
                                            $cekdata = DB::select("
                                                select
                                                    s.id_qr_stocker,
                                                    s.act_costing_ws,
                                                    msb.buyer,
                                                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                    msb.styleno as style,
                                                    s.color,
                                                    COALESCE(msb.size, s.size) size,
                                                    ms.tujuan,
                                                    ms.proses lokasi,
                                                    CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                    CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                    COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                    ".$multiSecondaryBeforeSecondaryIn->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$currentPartDetailSecondary->urutan." as urutan
                                                    (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$currentPartDetailSecondary->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status
                                                from
                                                    stocker_input s
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
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                    left join (
                                                        select
                                                            part_detail_id,
                                                            MAX(part_detail_secondary.urutan) max_urutan
                                                        from
                                                            part_detail_secondary
                                                        WHERE
                                                            part_detail_secondary.urutan IS NOT NULL
                                                        group by
                                                            part_detail_id
                                                    ) max_urutan on max_urutan.part_detail_id = pd.id
                                                     left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY LUAR' and
                                                    pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                                    and (act.close_order is null or act.close_order != 'Y')
                                            ");

                                            return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                        } else {
                                            return "You should never got here, how could you.";
                                        }
                                    }
                                } else {
                                    $cekdata =  DB::select("
                                        select
                                            s.id_qr_stocker,
                                            s.act_costing_ws,
                                            msb.buyer,
                                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                            msb.styleno as style,
                                            s.color,
                                            COALESCE(msb.size, s.size) size,
                                            dc.tujuan,
                                            dc.lokasi,
                                            CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                            CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                            COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                            if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
                                            s.lokasi lokasi_tujuan,
                                            s.tempat tempat_tujuan,
                                            1 urutan,
                                            (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND 1 >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status,
                                            max_urutan.max_urutan,
                                            md.sec_in_stocker,
                                            md.sec_in_created_at
                                        from
                                            (
                                                select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                                where dc.tujuan = 'SECONDARY DALAM' and
                                                ifnull(si.id_qr_stocker,'x') != 'x'
                                                union
                                                select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                                where dc.tujuan = 'SECONDARY LUAR'
                                            ) md
                                            left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join form_cut_input a on s.form_cut_id = a.id
                                            left join form_cut_reject b on s.form_reject_id = b.id
                                            left join form_cut_piece c on s.form_piece_id = c.id
                                            left join part_detail pd on s.part_detail_id = pd.id
                                            left join part p on p.id = pd.part_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            left join part p_com on p_com.id = pd_com.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join (
                                                select
                                                    part_detail_id,
                                                    MAX(part_detail_secondary.urutan) max_urutan
                                                from
                                                    part_detail_secondary
                                                WHERE
                                                    part_detail_secondary.urutan IS NOT NULL
                                                group by
                                                    part_detail_id
                                            ) max_urutan on max_urutan.part_detail_id = pd.id
                                            left join master_part mp on pd.master_part_id = mp.id
                                            left join marker_input mi on a.id_marker = mi.kode
                                            left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                            left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                            left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                        where s.id_qr_stocker = '" . $request->txtqrstocker . "' and (act.close_order is null or act.close_order != 'Y')
                                    ");

                                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                }
                            }
                        }
                        // If there is no current secondary (last step)
                        else {

                            // When there is a step before
                            if ($multiSecondaryBefore) {

                                // If Secondary Dalam
                                if ($multiSecondaryBefore->tujuan == "SECONDARY DALAM") {

                                    // Check the secondary dalam data
                                    $multiSecondaryBeforeSecondary = DB::table("secondary_inhouse_input")->
                                        where("id_qr_stocker", $request->txtqrstocker)->
                                        where("urutan", $multiSecondaryBefore->urutan)->
                                        first();

                                    // If there is secondary dalam
                                    if ($multiSecondaryBeforeSecondary) {

                                        // Check the secondary in data
                                        $multiSecondaryBeforeSecondaryIn = DB::table("secondary_in_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();

                                        // When there is no secondary in then
                                        if (!$multiSecondaryBeforeSecondaryIn) {

                                            // Return the data for Secondary Dalam
                                            $cekdata = DB::select("
                                                select
                                                    s.id_qr_stocker,
                                                    s.act_costing_ws,
                                                    msb.buyer,
                                                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                    msb.styleno as style,
                                                    s.color,
                                                    COALESCE(msb.size, s.size) size,
                                                    ms.tujuan,
                                                    ms.proses lokasi,
                                                    CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                                    CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                                    COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                                    ".$multiSecondaryBeforeSecondary->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$multiSecondaryBefore->urutan." as urutan,
                                                    (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND ".$multiSecondaryBefore->urutan." >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status,
                                                    max_urutan.max_urutan
                                                from
                                                    stocker_input s
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
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                    left join (
                                                        select
                                                            part_detail_id,
                                                            MAX(part_detail_secondary.urutan) max_urutan
                                                        from
                                                            part_detail_secondary
                                                        WHERE
                                                            part_detail_secondary.urutan IS NOT NULL
                                                        group by
                                                            part_detail_id
                                                    ) max_urutan on max_urutan.part_detail_id = pd.id
                                                     left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY DALAM' and
                                                    pds.urutan = '".$multiSecondaryBefore->urutan."' and
                                                    (act.close_order is null or act.close_order != 'Y')
                                            ");

                                            return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                        } else {
                                            $additional = "";
                                            if ($multiSecondaryBeforeSecondaryIn) {
                                                $additional .= "<br> Secondary IN tanggal ".$multiSecondaryBeforeSecondaryIn->tgl_trans;
                                            }

                                            return "Data Secondary In sudah ada".$additional;
                                        }
                                    } else {
                                        return "Data Secondary Dalam belum ada";
                                    }
                                } else {
                                    // Check the secondary in data
                                    return "when there is no step after (last step) and the step before was secondary in then you could not be able to scan the secondary in again, I mean you got yourself here from secondary in already.";
                                }
                            } else {
                                $cekdata =  DB::select("
                                    select
                                        s.id_qr_stocker,
                                        s.act_costing_ws,
                                        msb.buyer,
                                        COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                        msb.styleno as style,
                                        s.color,
                                        COALESCE(msb.size, s.size) size,
                                        dc.tujuan,
                                        dc.lokasi,
                                        CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                                        CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                                        COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                                        if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
                                        s.lokasi lokasi_tujuan,
                                        s.tempat tempat_tujuan,
                                        ".($multiSecondaryBefore->urutan + 1)." urutan,
                                        (CASE WHEN max_urutan.max_urutan IS NULL OR (max_urutan.max_urutan IS NOT NULL AND 1 >= max_urutan.max_urutan) THEN 'finish' ELSE 'process' END) status,
                                        max_urutan.max_urutan,
                                        md.sec_in_stocker,
                                        md.sec_in_created_at
                                    from
                                        (
                                            select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                            left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                            where dc.tujuan = 'SECONDARY DALAM' and
                                            ifnull(si.id_qr_stocker,'x') != 'x'
                                            union
                                            select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                            left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                            where dc.tujuan = 'SECONDARY LUAR'
                                        ) md
                                        left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input a on s.form_cut_id = a.id
                                        left join form_cut_reject b on s.form_reject_id = b.id
                                        left join form_cut_piece c on s.form_piece_id = c.id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on p.id = pd.part_id
                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                        left join part p_com on p_com.id = pd_com.part_id
                                        left join (
                                            select
                                                part_detail_id,
                                                MAX(part_detail_secondary.urutan) max_urutan
                                            from
                                                part_detail_secondary
                                            WHERE
                                                part_detail_secondary.urutan IS NOT NULL
                                            group by
                                                part_detail_id
                                        ) max_urutan on max_urutan.part_detail_id = pd.id
                                        left join master_part mp on pd.master_part_id = mp.id
                                        left join marker_input mi on a.id_marker = mi.kode
                                        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                        left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                    where s.id_qr_stocker = '" . $request->txtqrstocker . "' and (act.close_order is null or act.close_order != 'Y')
                                ");

                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }
                        }
                    }
                }
                // Default
                else {
                    $cekdata =  DB::select("
                        select
                            s.id_qr_stocker,
                            s.act_costing_ws,
                            msb.buyer,
                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                            msb.styleno as style,
                            s.color,
                            COALESCE(msb.size, s.size) size,
                            dc.tujuan,
                            dc.lokasi,
                            CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                            CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                            COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                            if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
                            s.lokasi lokasi_tujuan,
                            s.tempat tempat_tujuan,
                            md.sec_in_stocker,
                            md.sec_in_created_at
                        from
                            (
                                select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                where dc.tujuan = 'SECONDARY DALAM' and
                                ifnull(si.id_qr_stocker,'x') != 'x'
                                union
                                select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2, sii.id_qr_stocker sec_in_stocker, sii.created_at sec_in_created_at from dc_in_input dc
                                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                where dc.tujuan = 'SECONDARY LUAR'
                            ) md
                            left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
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
                            left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                            left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                            left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                        where s.id_qr_stocker = '" . $request->txtqrstocker . "' and (act.close_order is null or act.close_order != 'Y')
                    ");

                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                }
            } else {
                return "Part Detail tidak ditemukan.";
            }
        }

        return "Stocker tidak ditemukan.";
    }

    public function cek_data_stocker_in_edit(Request $request)
    {
        $cekdata =  DB::select("
            select
                s.id_qr_stocker,
                s.act_costing_ws,
                msb.buyer,
                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                msb.styleno as style,
                s.color,
                COALESCE(msb.size, s.size) size,
                dc.tujuan,
                dc.lokasi,
                CONCAT((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), (CASE WHEN (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) IS NOT NULL THEN CONCAT(' - ', (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END)) ELSE '' END)) panel,
                CONCAT(mp.nama_part, (CASE WHEN UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) != '-' THEN CONCAT(' - ', UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-'))) ELSE '' END)) nama_part,
                COALESCE(s.qty_ply_mod, s.qty_ply) qty_stocker,
                COALESCE(sii.qty_awal, si.qty_in, (dc.qty_awal - dc.qty_reject - dc.qty_replace), 0) as qty_awal,
                sii.qty_reject,
                sii.qty_replace,
                sii.qty_in qty_in,
                COALESCE(siu.qty_reject, 0) total_reject,
                COALESCE(siu.qty_replace, 0) total_replace,
                (sii.qty_in - COALESCE(siu.qty_reject, 0) + COALESCE(siu.qty_replace, 0)) qty_in_akhir,
                s.lokasi lokasi_tujuan,
                s.tempat tempat_tujuan
            from
            (
                select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2  from dc_in_input dc
                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                where
                    (
                        dc.tujuan = 'SECONDARY DALAM'
                        or
                        dc.tujuan = 'SECONDARY LUAR'
                    )
                    and
                    (
                        ifnull(si.id_qr_stocker,'x') != 'x'
                        or
                        ifnull(sii.id_qr_stocker,'x') != 'x'
                    )
            ) md
            left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
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
            left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
            left join secondary_in_input sii on s.id_qr_stocker = sii.id_qr_stocker
            left join (
                select
                    siu.secondary_in_id,
                    SUM(siu.reject) qty_reject,
                    SUM(siu.replace) qty_replace
                from
                    secondary_in_update siu
                group by
                    siu.secondary_in_id
            ) siu on siu.secondary_in_id = sii.id
            where s.id_qr_stocker = '" . $request->txtqrstocker . "'
        ");

        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
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
            "txtqtyreject" => "required"
        ]);

        $qtyIn = $request['txtqtyawal'] - $request['txtqtyreject'] + $request['txtqtyreplace'];
        if ($qtyIn < 1) {
            return array(
                'status' => 400,
                'message' => 'Qty tidak bisa kurang dari 1',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        // Check stocker's availability on secondary in
        $checkSecondaryIn = SecondaryIn::where("id_qr_stocker", $request->txtno_stocker)->where('urutan', $request->txturutan)->first();
        if ($checkSecondaryIn) {
            return array(
                'status' => 400,
                'message' => 'Stocker <b>'.$request->txtno_stocker.'</b> '.($request->txturutan ? 'urutan '.$request->txturutan : '').' sudah di scan di secondary in pada tanggal <b>'.$checkSecondaryIn->tgl_trans.'</b>',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        // Check if last step of process
        $lastStep = Stocker::selectRaw("MAX(part_detail_secondary.urutan) as urutan")->
            leftJoin("part_detail_secondary", "part_detail_secondary.part_detail_id", "=", "stocker_input.part_detail_id")->
            where("stocker_input.id_qr_stocker", $request['txtno_stocker'])->
            groupBy("stocker_input.id")->
            value("urutan");

        $additionalMessage = "";
        // Update Rak/Trolley (One Step Before Loading) On Last Step/No Step at all
        if (!$lastStep || $lastStep <= $request->txturutan) {

            // Update Rak
            if ($request['cborak']) {
                $rak = DB::table('rack_detail')
                ->select('id', 'nama_detail_rak')
                ->where('nama_detail_rak', '=', $request['cborak'])
                ->first();
                $rak_data = $rak ? $rak->id : null;

                $insert_rak = RackDetailStocker::create([
                    'nm_rak' => $request['cborak'],
                    'detail_rack_id' => $rak_data,
                    'stocker_id' => $request['txtno_stocker'],
                    'qty_in' => $qtyIn,
                    'status' => 'active',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $additionalMessage .= "Stocker dialokasikan ke Rak " . ($rak ? $rak->nama_detail_rak : $request['cborak']);
            }

            // Update Trolley
            if ($request['cbotrolley']) {

                $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                $trolleyStockArr = [];

                $thisStocker = Stocker::whereRaw("id_qr_stocker = '" . $request['txtno_stocker'] . "'")->first();
                $thisTrolley = Trolley::where("nama_trolley", $request['cbotrolley'])->first();
                if ($thisTrolley && $thisStocker) {

                    // Get trolley_id from the earliest existing TrolleyStocker of similar stockers
                    $similarStockerData = Stocker::where(
                            ($thisStocker->form_piece_id > 0 ? "form_piece_id" : ($thisStocker->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")),
                            ($thisStocker->form_piece_id > 0 ? $thisStocker->form_piece_id : ($thisStocker->form_reject_id > 0 ? $thisStocker->form_reject_id : $thisStocker->form_cut_id))
                        )->where("so_det_id", $thisStocker->so_det_id)
                        ->where("group_stocker", $thisStocker->group_stocker)
                        ->where("ratio", $thisStocker->ratio)
                        ->where("stocker_reject", $thisStocker->stocker_reject)
                        ->get();

                    $existingTrolleyStockers = TrolleyStocker::whereIn('stocker_id', $similarStockerData->pluck('id'))->get();
                    $earliestTrolleyStocker = $existingTrolleyStockers->sortBy('created_at')->first();
                    $trolleyId = $earliestTrolleyStocker ? $earliestTrolleyStocker->trolley_id : $thisTrolley->id;

                    // Create Trolley Stock
                    $trolleyCheck = TrolleyStocker::where('stocker_id', $thisStocker->id)->first();
                    if (!$trolleyCheck) {
                        TrolleyStocker::create([
                            "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber)),
                            "trolley_id" => $trolleyId,
                            "stocker_id" => $thisStocker->id,
                            "status" => "active",
                            "tanggal_alokasi" => date('Y-m-d'),
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]);
                    }

                    // Update Stocker Status
                    $thisStocker->status = "trolley";
                    $thisStocker->latest_alokasi = Carbon::now();
                    $thisStocker->save();

                    // Update Status Rak
                    RackDetailStocker::where("stocker_id", $thisStocker->id_qr_stocker)->update([
                        "status" => "not active"
                    ]);

                    $trolley = Trolley::where("id", $trolleyId)->first();

                    $additionalMessage .= "Stocker dialokasikan ke trolley ." . $trolley->nama_trolley;
                }
            }
        }

        // Save Secondary IN
        $savein = SecondaryIn::updateOrCreate(
            ['id_qr_stocker' => $request['txtno_stocker'], 'urutan' => $request->txturutan],
            [
                'tgl_trans' => $tgltrans,
                'qty_awal' => $request['txtqtyawal'],
                'qty_reject' => $request['txtqtyreject'],
                'qty_replace' => $request['txtqtyreplace'],
                'qty_in' => $qtyIn,
                'user' => Auth::user()->name,
                'ket' => $request['txtket'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        // Update Stocker status
        DB::update(
            "update stocker_input set status = '".($request['cbotrolley'] ? "trolley" : "non secondary")."' ".($request->txturutan ? ", urutan = '".(intval($request->txturutan) + 1)."' " : "")." where id_qr_stocker = '" . $request->txtno_stocker . "'"
        );
        // dd($savemutasi);
        // $message .= "$tglpindah <br>";


        return array(
            'status' => 300,
            'message' => 'Data Berhasil Disimpan' . ($additionalMessage ? "<br>" . $additionalMessage : ""),
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function massStore(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        // Get Stocker
        $thisStocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.act_costing_ws, stocker_input.color, COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') as no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
            leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
            where("id_qr_stocker", $request['txtno_stocker'])->
            first();

        if ($thisStocker) {
            // Check Stocker Family Specification
            $cekdata = DB::select("
                SELECT
                    s.id_qr_stocker,
                    s.act_costing_ws,
                    msb.buyer,
                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                    style,
                    s.color,
                    COALESCE ( msb.size, s.size ) size,
                    dc.tujuan,
                    dc.lokasi,
                    mp.nama_part,
                    IF
                    ( dc.tujuan = 'SECONDARY LUAR', dc.qty_awal, si.qty_awal ) qty_awal,
                    s.lokasi lokasi_tujuan,
                    s.tempat tempat_tujuan
                FROM
                    (
                    SELECT
                        dc.id_qr_stocker,
                        ifnull( si.id_qr_stocker, 'x' ) cek_1,
                        ifnull( sii.id_qr_stocker, 'x' ) cek_2
                    FROM
                        dc_in_input dc
                        LEFT JOIN secondary_inhouse_input si ON dc.id_qr_stocker = si.id_qr_stocker
                        LEFT JOIN secondary_in_input sii ON dc.id_qr_stocker = sii.id_qr_stocker
                    WHERE
                        dc.tujuan = 'SECONDARY DALAM'
                        AND ifnull( si.id_qr_stocker, 'x' ) != 'x'
                        AND ifnull( sii.id_qr_stocker, 'x' ) = 'x' UNION
                    SELECT
                        dc.id_qr_stocker,
                        'x' cek_1,
                    IF
                        ( sii.id_qr_stocker IS NULL, dc.id_qr_stocker, 'x' ) cek_2
                    FROM
                        dc_in_input dc
                        LEFT JOIN secondary_in_input sii ON dc.id_qr_stocker = sii.id_qr_stocker
                    WHERE
                        dc.tujuan = 'SECONDARY LUAR'
                    AND
                    IF
                        ( sii.id_qr_stocker IS NULL, dc.id_qr_stocker, 'x' ) != 'x'
                    ) md
                    INNER JOIN stocker_input s ON md.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    INNER JOIN form_cut_input a ON s.form_cut_id = a.id
                    LEFT JOIN form_cut_reject b ON s.form_reject_id = b.id
                    LEFT JOIN form_cut_piece c ON s.form_piece_id = c.id
                    INNER JOIN part_detail p ON s.part_detail_id = p.id
                    INNER JOIN master_part mp ON p.master_part_id = mp.id
                    INNER JOIN marker_input mi ON a.id_marker = mi.kode
                    LEFT JOIN dc_in_input dc ON s.id_qr_stocker = dc.id_qr_stocker
                    LEFT JOIN secondary_inhouse_input si ON s.id_qr_stocker = si.id_qr_stocker
                WHERE
                    s.act_costing_ws = '".$thisStocker->act_costing_ws."' AND
                    s.color = '".$thisStocker->color."' AND
                    a.no_cut = '".$thisStocker->no_cut."'
            ");

            foreach ($cekdata as $d) {
                // When stocker's destination is rack
                if ($d->tempat_tujuan == 'RAK') {
                    $rak = DB::table('rack_detail')
                    ->select('id')
                    ->where('nama_detail_rak', '=', $d->lokasi_tujuan)
                    ->get();
                    $rak_data = $rak ? $rak[0]->id : null;

                    $insert_rak = RackDetailStocker::create([
                        'nm_rak' => $d->lokasi_tujuan,
                        'detail_rack_id' => $rak_data,
                        'stocker_id' => $d->id_qr_stocker,
                        'qty_in' => $d->qty_awal,
                        'status' => 'active',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                // When stocker's destination is trolley
                if ($d->tempat_tujuan == 'TROLLEY') {
                    $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                    $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                    $trolleyStockArr = [];

                    $thisStocker = Stocker::whereRaw("id_qr_stocker = '" . $d->id_qr_stocker . "'")->first();
                    $thisTrolley = Trolley::where("nama_trolley", $d->lokasi_tujuan)->first();
                    if ($thisTrolley && $thisStocker) {

                        // Update Trolley Stocker
                        $trolleyCheck = TrolleyStocker::where('stocker_id', $thisStocker->id)->first();
                        if (!$trolleyCheck) {
                            TrolleyStocker::create([
                                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber)),
                                "trolley_id" => $thisTrolley->id,
                                "stocker_id" => $thisStocker->id,
                                "status" => "active",
                                "tanggal_alokasi" => date('Y-m-d'),
                            ]);
                        }

                        // Update Status Stocker
                        $thisStocker->status = "trolley";
                        $thisStocker->latest_alokasi = Carbon::now();
                        $thisStocker->save();

                        // Update Status Rak
                        RackDetailStocker::where("stocker_id", $thisStocker->id_qr_stocker)->update([
                            "status" => "not active"
                        ]);
                    }
                }

                // Save Secondary Inhouse
                $saveinhouse = SecondaryIn::updateOrCreate(
                    ['id_qr_stocker' => $d->id_qr_stocker],
                    [
                        'tgl_trans' => $tgltrans,
                        'qty_awal' => $d->qty_awal,
                        'qty_reject' => 0,
                        'qty_replace' => 0,
                        'qty_in' => $d->qty_awal,
                        'user' => Auth::user()->name,
                        'ket' => '',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]
                );

                // Update Stocker status
                DB::update(
                    "update stocker_input set status = 'non secondary' where id_qr_stocker = '" . $d->id_qr_stocker . "'"
                );
            }

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
            'message' => 'Data gagal disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function updateOld(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        // Check Closing
        $dataCheckClosing = DB::table("secondary_in_input")->where("id_qr_stocker", $request['edit_no_stocker'])->first();
        if (checkClosingDate($dataCheckClosing->tgl_trans)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing",
                "table" => "datatable-input",
            );
        }

        $validatedRequest = $request->validate([
            "edit_qtyreject" => "required"
        ]);

        $loadingLine = LoadingLine::leftJoin("stocker_input", "stocker_input.id", "=", "loading_line.stocker_id")->where("stocker_input.id_qr_stocker", $request['edit_no_stocker'])->first();

        if ($loadingLine) {
            return array(
                'status' => 400,
                'message' => 'Data Sudah Di Loading Line',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        $saveinhouse = SecondaryIn::updateOrCreate(
            ['id_qr_stocker' => $request['edit_no_stocker']],
            [
                'tgl_trans' => $tgltrans,
                'qty_awal' => $request['edit_qtyawal'],
                'qty_reject' => $request['edit_qtyreject'],
                'qty_replace' => $request['edit_qtyreplace'],
                'qty_in' => $request['edit_qtyawal'] - $request['edit_qtyreject'] + $request['edit_qtyreplace'],
                'user' => Auth::user()->name,
                'ket' => $request['edit_ket'],
            ]
        );

        DB::update(
            "update stocker_input set status = 'non secondary' where id_qr_stocker = '" . $request->edit_no_stocker . "'"
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

    public function update(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $validatedRequest = $request->validate([
            "edit_qtyreject" => "required",
            "edit_qtyrejectnew" => "nullable|numeric",
            "edit_qtyreplacenew" => "nullable|numeric",
        ]);

        $dataStocker = Stocker::where("id_qr_stocker", $request['edit_no_stocker'])->first();
        $dataSecondaryIn = SecondaryIn::where("id_qr_stocker", $request['edit_no_stocker'])->first();
        $dataSecondaryInUpdate = $dataSecondaryIn->secondaryInUpdate;
        $dataCheckLoading = DB::table("loading_line")->where("stocker_id", $dataStocker->id)->first();

        // Check Secondary In record
        if (!$dataSecondaryIn) {
            return array(
                "status" => 400,
                "message" => "Data Secondary In tidak ditemukan.",
                "table" => "datatable-input",
            );
        }

        // Check Loading In record
        if ($dataCheckLoading) {
            return array(
                "status" => 400,
                "message" => "Data sudah masuk loading.",
                "table" => "datatable-input",
            );
        }

        // Check Closing
        // if (checkClosingDate($dataSecondaryIn->tgl_trans)) {
        //     return array(
        //         "status" => 400,
        //         "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
        //         "additional" => "Closing",
        //         "table" => "datatable-input",
        //     );
        // }

        $qtyRejectNew = (float) ($request['edit_qtyrejectnew'] ?? 0);
        $qtyReplaceNew = (float) ($request['edit_qtyreplacenew'] ?? 0);

        if ($qtyRejectNew <= 0 && $qtyReplaceNew <= 0) {
            return array(
                "status" => 400,
                "message" => "Qty Reject atau Qty Replace baru harus diisi minimal 0.",
                "table" => "datatable-input",
            );
        }

        $qtyAwal = $dataStocker->qty_ply_mod ?? $dataStocker->qty_ply;
        $qtySecondaryIn = $dataSecondaryIn->qty_awal;
        $qtyRejectTotal = $dataSecondaryIn->qty_reject + $dataSecondaryInUpdate->sum("reject") + $qtyRejectNew;
        $qtyReplaceTotal = $dataSecondaryIn->qty_replace + $dataSecondaryInUpdate->sum("replace") + $qtyReplaceNew;
        $qtyInTotal = $qtySecondaryIn - $qtyRejectTotal + $qtyReplaceTotal;

        if ($qtyInTotal > $qtyAwal) {
            return array(
                "status" => 400,
                "message" => "Qty akhir tidak dapat melebihi Qty Awal.",
                "table" => "datatable-input",
            );
        }

        if ($qtyInTotal < 1) {
            return array(
                "status" => 400,
                "message" => "Qty In tidak dapat kurang dari 1.",
                "table" => "datatable-input",
            );
        }

        DB::transaction(function () use ($request, $dataSecondaryIn, $tgltrans, $timestamp, $qtyRejectNew, $qtyReplaceNew, $qtyRejectTotal, $qtyReplaceTotal, $qtyInTotal) {

            // Log the reject/replace adjustment
            DB::table('secondary_in_update')->insert([
                'tgl_trans' => $tgltrans,
                'id_qr_stocker' => $request['edit_no_stocker'],
                'secondary_in_id' => $dataSecondaryIn->id,
                'reject' => $qtyRejectNew,
                'replace' => $qtyReplaceNew,
                'created_by' => Auth::user()->id,
                'created_by_username' => Auth::user()->username,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            // Update the cumulative qty on Secondary In
            // DB::table('secondary_in_input')
            //     ->where('id', $dataSecondaryIn->id)
            //     ->update([
            //         'qty_reject' => $qtyRejectTotal,
            //         'qty_replace' => $qtyReplaceTotal,
            //         'qty_in' => $qtyInTotal,
            //         'updated_at' => $timestamp,
            //     ]);
        });

        return array(
            "status" => 300,
            "message" => "Data Berhasil Disimpan",
            "table" => "datatable-input",
            "additional" => [],
        );
    }

    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $data = $this->secondaryInQuery($request, $request->from, $request->to)->orderByDesc('tgl_trans')->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary In Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary In Report', ['font-size' => 16]);
        $sheet->mergeCells('A1:W1');

        // Headers
        $sheet->writeTo('A2', 'Tgl Transaksi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'ID QR')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Panel Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Part Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', 'No. Cut')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', 'Tujuan Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', 'Lokasi Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', 'Lokasi Rak')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', 'Range')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', 'Qty Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', 'Qty Reject')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', 'Qty Replace')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('T2', 'Urutan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('U2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('V2', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('W2', 'Created At')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('X2', 'Notes')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->tgl_trans_fix ?? "-",
                    $row->id_qr_stocker ?? "-",
                    $row->act_costing_ws ?? "-",
                    $row->style ?? "-",
                    $row->color ?? "-",
                    $row->panel_only ? preg_replace('/\s+/', ' ', $row->panel_only) : "-",
                    $row->panel_status ?? "-",
                    $row->nama_part_only ? preg_replace('/\s+/', ' ', $row->nama_part_only) : "-",
                    $row->part_status ?? "-",
                    $row->size ?? "-",
                    $row->no_cut ?? "-",
                    $row->exp_tujuan ?? "-",
                    $row->exp_lokasi ?? "-",
                    $row->lokasi_rak ?? "-",
                    $row->stocker_range ?? "-",
                    intval($row->exp_qty_awal) ?? 0,
                    intval($row->exp_qty_reject) ?? 0,
                    intval($row->exp_qty_replace) ?? 0,
                    intval($row->exp_qty_in) ?? 0,
                    $row->urutan ?? "-",
                    $row->buyer ?? "-",
                    $row->user ?? "-",
                    $row->created_at ?? "-",
                    $row->notes ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan sec in ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }

    public function exportExcelDetail(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $additionalQuery = "";
        $additionalBindings = [];

        if ($request->from) {
            $additionalQuery .= " and (si.tgl_trans >= ?) ";
            $additionalBindings[] = $request->from;
        }

        if ($request->to) {
            $additionalQuery .= " and (si.tgl_trans <= ?) ";
            $additionalBindings[] = $request->to;
        }

        $data = DB::select("
            select
                s.act_costing_ws, m.buyer,s.color,styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(si.qty_reject), 0) qty_reject, COALESCE(sum(si.qty_replace), 0) qty_replace, COALESCE(sum(si.qty_in), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace -  si.qty_in), 0) balance, dc.tujuan,dc.lokasi
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws m on s.so_det_id = m.id_so_det
                left join secondary_in_input si on dc.id_qr_stocker = si.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY LUAR' and (s.cancel IS NULL OR s.cancel != 'y')
                ".$additionalQuery."
            group
                by m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            union
            select
                s.act_costing_ws, buyer,s.color,styleno, COALESCE(sum(sii.qty_in), 0) qty_in, COALESCE(sum(si.qty_reject), 0) qty_reject, COALESCE(sum(si.qty_replace), 0) qty_replace, COALESCE(sum(si.qty_in), 0) qty_out, COALESCE(sum(sii.qty_in - si.qty_in), 0) balance, dc.tujuan, dc.lokasi
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws m on s.so_det_id = m.id_so_det
                left join secondary_inhouse_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                left join secondary_in_input si on dc.id_qr_stocker = si.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY DALAM' and (s.cancel IS NULL OR s.cancel != 'y')
                ".$additionalQuery."
            group by
                m.ws,m.buyer,m.styleno,m.color,dc.lokasi
        ", array_merge($additionalBindings, $additionalBindings));

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary In Detail Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary In Detail Report', ['font-size' => 16]);
        $sheet->mergeCells('A1:K1');

        // Headers
        $sheet->writeTo('A2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Qty Reject')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Qty Replace')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Qty Out')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Balance')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Tujuan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', 'Lokasi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->act_costing_ws ?? "-",
                    $row->buyer ?? "-",
                    $row->color ?? "-",
                    $row->styleno ?? "-",
                    $row->qty_in ?? "-",
                    $row->qty_reject ?? "-",
                    $row->qty_replace ?? "-",
                    $row->qty_out ?? "-",
                    $row->balance ?? "-",
                    $row->tujuan ?? "-",
                    $row->lokasi ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan sec in detail ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
