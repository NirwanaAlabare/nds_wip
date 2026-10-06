<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Stocker\Stocker;
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\SecondaryInhouseInTemp;
use App\Exports\DC\ExportSecondaryInHouseIn;
use App\Exports\DC\ExportSecondaryInHouseInDetail;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class SecondaryInhouseInController extends Controller
{
    /**
     * Satu-satunya sumber query list Secondary Inhouse In (tabel, filter dropdown, total, export).
     * Mengembalikan query builder di atas subquery `sec_in` sehingga paging/sort/sum/distinct
     * dikerjakan database, bukan PHP.
     */
    private function secondaryInhouseInQuery(Request $request, $from = null, $to = null)
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

        if ($from) {
            $where[] = "a.tgl_trans >= ?";
            $bindings[] = $from;
        }
        if ($to) {
            $where[] = "a.tgl_trans <= ?";
            $bindings[] = $to;
        }

        // filter dropdown (multi select), memakai ekspresi yang sama dengan kolom yang ditampilkan
        $addIn("(CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END)", $request->sec_filter_tipe);
        $addIn("COALESCE(msb.buyer, p.buyer)", $request->sec_filter_buyer);
        $addIn("COALESCE(msb.ws, s.act_costing_ws)", $request->sec_filter_ws);
        $addIn("COALESCE(msb.styleno, p.style)", $request->sec_filter_style);
        $addIn("COALESCE(msb.color, s.color)", $request->sec_filter_color);
        $addIn("(CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END)", $request->sec_filter_panel);
        $addIn("mp.nama_part", $request->sec_filter_part);
        $addIn("COALESCE(msb.size, s.size)", $request->sec_filter_size);
        $addIn("COALESCE(msb.size, s.size)", $request->size_filter);
        $addIn("COALESCE(f.no_cut, fp.no_cut, '-')", $request->sec_filter_no_cut);
        $addIn("COALESCE(mms.tujuan, dc.tujuan)", $request->sec_filter_tujuan);
        $addIn("COALESCE(mms.proses, dc.tempat)", $request->sec_filter_tempat);
        $addIn("dc.lokasi", $request->sec_filter_lokasi);

        $additionalQuery = count($where) > 0 ? " and " . implode(" and ", $where) : "";

        $sql = "
            SELECT
                a.id,
                a.id_qr_stocker,
                a.urutan,
                (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                COALESCE(msb.ws, s.act_costing_ws) act_costing_ws,
                COALESCE(msb.color, s.color) color,
                COALESCE(msb.buyer, p.buyer) buyer,
                COALESCE(msb.styleno, p.style) style,
                (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) as panel_status,
                a.qty_in,
                a.created_at,
                COALESCE(mms.tujuan, dc.tujuan) as tujuan,
                dc.lokasi as lokasi,
                COALESCE(mms.proses, dc.tempat) as tempat,
                COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                COALESCE(msb.size, s.size) size,
                a.user,
                mp.nama_part,
                UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')) part_status,
                CONCAT(s.range_awal, ' - ', s.range_akhir, (CASE WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ') ELSE ' (0)' END)) stocker_range,
                s.notes
            from secondary_inhouse_in_input a
            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on fr.id = s.form_reject_id
            left join form_cut_piece fp on fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            left join master_part mp on mp.id = pd.master_part_id
            left join part_detail_secondary pds on pds.part_detail_id = pd.id and IFNULL(pds.urutan, '') = IFNULL(a.urutan, '')
            left join master_secondary mms on mms.id = pds.master_secondary_id
            left join master_secondary ms on ms.id = pd.master_secondary_id
            left join (select id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat from dc_in_input) dc on a.id_qr_stocker = dc.id_qr_stocker
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            where
                a.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
                " . $additionalQuery . "
        ";

        return DB::query()->fromRaw("($sql) as sec_in", $bindings);
    }

    /**
     * Satu-satunya sumber query detail Secondary Inhouse In (tabel, filter dropdown, export).
     */
    private function secondaryInhouseDetailQuery(Request $request, $from = null, $to = null)
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
            $where[] = "sii.tgl_trans >= ?";
            $bindings[] = $from;
        }
        if ($to) {
            $where[] = "sii.tgl_trans <= ?";
            $bindings[] = $to;
        }

        $addIn("msb.buyer", $request->detail_sec_filter_buyer);
        $addIn("s.act_costing_ws", $request->detail_sec_filter_ws);
        $addIn("msb.styleno", $request->detail_sec_filter_style);
        $addIn("s.color", $request->detail_sec_filter_color);
        $addIn("dc.lokasi", $request->detail_sec_filter_lokasi);

        $additionalQuery = count($where) > 0 ? " and " . implode(" and ", $where) : "";

        $sql = "
            select
                sii.tgl_trans, s.act_costing_ws, msb.buyer, msb.styleno,
                COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                mp.nama_part,
                CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) part_label,
                s.color, s.size, dc.tujuan, dc.lokasi as proses, COALESCE(sum(sii.qty_in), 0) qty_in
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on p.id = pd.part_id
                left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                left join part p_com on p_com.id = pd_com.part_id
                left join master_part mp on mp.id = pd.master_part_id
                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY DALAM' and (s.cancel IS NULL OR s.cancel != 'y')
                " . $additionalQuery . "
            group by
                sii.tgl_trans, s.act_costing_ws, msb.buyer, msb.styleno, s.color, s.size, mp.nama_part, dc.tujuan, dc.lokasi
        ";

        return DB::query()->fromRaw("($sql) as sec_detail", $bindings);
    }

    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        if ($request->ajax()) {
            // paging, sorting & searching dilakukan di database (client memakai ordering: false, jadi urutan default di sini)
            $query = $this->secondaryInhouseInQuery($request, $request->dateFrom, $request->dateTo)->orderByDesc('tgl_trans');

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-inhouse-in.secondary-inhouse-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryInhouse(Request $request)
    {
        // hanya batasi tanggal, filter dropdown tidak ikut dipakai di sini
        $base = $this->secondaryInhouseInQuery(new Request(), $request->dateFrom, $request->dateTo);

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
            "part" => $distinct("nama_part"),
            "no_cut" => $distinct("no_cut"),
            "size" => $distinct("size")
        );
    }

    public function total_secondary_inhouse_in(Request $request)
    {
        $query = $this->secondaryInhouseInQuery($request, $request->dateFrom, $request->dateTo);

        // filter header per kolom & pencarian global (mengikuti kolom yang tampil di tabel)
        $columns = [
            'tgl_trans_fix', 'id_qr_stocker', 'tipe', 'act_costing_ws', 'style', 'color', 'panel', 'nama_part',
            'size', 'no_cut', 'tujuan', 'lokasi', 'stocker_range', 'qty_in', 'buyer', 'user', 'created_at',
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

        return $query->selectRaw("SUM(qty_in) qty_in")->get();
    }

    public function detail_stocker_inhouse(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $query = $this->secondaryInhouseDetailQuery($request, $request->dateFrom, $request->dateTo);

            return DataTables::query($query)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryInhouse(Request $request)
    {
        $base = $this->secondaryInhouseDetailQuery(new Request(), $request->dateFrom, $request->dateTo);

        $distinct = fn ($column) => (clone $base)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column)->values();

        return array(
            "ws" => $distinct("act_costing_ws"),
            "color" => $distinct("color"),
            "buyer" => $distinct("buyer"),
            "style" => $distinct("styleno"),
            "lokasi" => $distinct("proses")
        );
    }

    public function cek_data_stocker_inhouse(Request $request)
    {
        $stocker = Stocker::where('id_qr_stocker', $request->txtqrstocker)->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
            first();

        if ($stocker) {
            // Check Close Order
            if (checkCloseOrder($stocker->id_act_cost)) {
                return array(
                    "status" => 400,
                    "message" => "WS '".$stocker->ws."' sudah close order."
                );
            }

            // Check Part Detail
            $partDetail = $stocker->partDetail;
            if ($partDetail) {

                // Check Part Detail Secondary
                $partDetailSecondary = $partDetail->secondaries;

                if ($partDetailSecondary && $partDetailSecondary->count() > 0) {

                    // If there ain't no urutan
                    if ($stocker->urutan == null) {
                        $cekdata = DB::select("
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
                                COALESCE(coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                                ifnull(si.id_qr_stocker,'x'),
                                1 as urutan
                            from dc_in_input dc
                                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                left join form_cut_input a on s.form_cut_id = a.id
                                left join form_cut_reject b on s.form_reject_id = b.id
                                left join form_cut_piece c on s.form_piece_id = c.id
                                left join part_detail p on s.part_detail_id = p.id
                                left join master_part mp on p.master_part_id = mp.id
                                left join marker_input mi on a.id_marker = mi.kode
                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                            where
                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM' and (act.close_order is null or act.close_order != 'Y')
                        ");
                    }
                    // If there is urutan
                    else {
                        // Current Secondary
                        $currentPartDetailSecondary = $partDetailSecondary->where('urutan', $stocker->urutan)->first();

                        if ($currentPartDetailSecondary && ($currentPartDetailSecondary->secondary && $currentPartDetailSecondary->secondary->tujuan == 'SECONDARY DALAM')) {

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

                                    // When there is secondary in on the step before then
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
                                                mp.nama_part,
                                                ms.tujuan,
                                                ms.proses lokasi,
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
                                                left join part_detail p on s.part_detail_id = p.id
                                                left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                left join master_part mp on p.master_part_id = mp.id
                                                left join master_secondary ms on pds.master_secondary_id = ms.id
                                                left join marker_input mi on a.id_marker = mi.kode
                                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                                left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                            where
                                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                                ms.tujuan = 'SECONDARY DALAM' and
                                                pds.urutan = '".$currentPartDetailSecondary->urutan."' and (act.close_order is null or act.close_order != 'Y')
                                        ");
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
                                            mp.nama_part,
                                            ms.tujuan,
                                            ms.proses lokasi,
                                            '".($multiSecondaryBeforeSecondary ? $multiSecondaryBeforeSecondary->qty_in : ($stocker->dcIn ? ($stocker->dcIn->qty_awal-$stocker->dcIn->qty_reject+$stocker->dcIn->qty_replace) : $stocker->qty_ply))."' qty_awal,
                                            ifnull(si.id_qr_stocker,'x'),
                                            (pds.urutan) as urutan
                                        from
                                            dc_in_input dc
                                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join form_cut_input a on s.form_cut_id = a.id
                                            left join form_cut_reject b on s.form_reject_id = b.id
                                            left join form_cut_piece c on s.form_piece_id = c.id
                                            left join part_detail p on s.part_detail_id = p.id
                                            left join part_detail_secondary pds on pds.part_detail_id = p.id
                                            left join master_part mp on p.master_part_id = mp.id
                                            left join master_secondary ms on pds.master_secondary_id = ms.id
                                            left join marker_input mi on a.id_marker = mi.kode
                                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                            left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                        where
                                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                            ms.tujuan = 'SECONDARY DALAM' and
                                            pds.urutan = '".$currentPartDetailSecondary->urutan."' and
                                            (act.close_order is null or act.close_order != 'Y')
                                    ");
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
                                        mp.nama_part,
                                        dc.tujuan,
                                        dc.lokasi,
                                        COALESCE(coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                                        ifnull(si.id_qr_stocker,'x'),
                                        1 as urutan
                                    from dc_in_input dc
                                        left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input a on s.form_cut_id = a.id
                                        left join form_cut_reject b on s.form_reject_id = b.id
                                        left join form_cut_piece c on s.form_piece_id = c.id
                                        left join part_detail p on s.part_detail_id = p.id
                                        left join master_part mp on p.master_part_id = mp.id
                                        left join marker_input mi on a.id_marker = mi.kode
                                        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                        left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                                    where
                                        dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                        dc.tujuan = 'SECONDARY DALAM' and (act.close_order is null or act.close_order != 'Y')
                                ");
                            }
                        } else {
                            $message = "";
                            if ($currentPartDetailSecondary && $currentPartDetailSecondary->secondary) {
                                $message = "Proses saat ini : ".$currentPartDetailSecondary->secondary->proses." - ".$currentPartDetailSecondary->secondary->tujuan;
                            }

                            return array(
                                "message" => 400,
                                "message" => "Part Detail Secondary tidak sesuai.".$message
                            );
                        }
                    }
                }
                // Default
                else {
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
                            COALESCE(coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                            ifnull(si.id_qr_stocker,'x'),
                            1 as urutan
                        from dc_in_input dc
                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            left join form_cut_input a on s.form_cut_id = a.id
                            left join form_cut_reject b on s.form_reject_id = b.id
                            left join form_cut_piece c on s.form_piece_id = c.id
                            left join part_detail p on s.part_detail_id = p.id
                            left join master_part mp on p.master_part_id = mp.id
                            left join marker_input mi on a.id_marker = mi.kode
                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                            left join signalbit_erp.act_costing act on msb.id_act_cost = act.id
                        where
                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "'
                            and dc.tujuan = 'SECONDARY DALAM' and (act.close_order is null or act.close_order != 'Y')
                    ");
                }
            } else {
                return array(
                    "message" => 400,
                    "message" => "No Part Detail Found."
                );
            }
        }

        if ($cekdata && $cekdata[0]) {
            // Check Secondary Inhouse
            $checkSecInhouseIn = SecondaryInhouseIn::where("id_qr_stocker", $request->txtqrstocker)->where("urutan", $cekdata[0]->urutan)->first();
            if ($checkSecInhouseIn) {

                return array(
                    "status" => 400,
                    "message" => "Stocker sudah discan di transaksi IN Secondary Dalam.".($checkSecInhouseIn->tgl_trans ? " Pada tanggal ".$checkSecInhouseIn->tgl_trans : "")
                );
            }

            // Check Secondary Inhouse Personal Temporary
            $checkSecInhouseInTemp = SecondaryInhouseInTemp::where("id_qr_stocker", $request->txtqrstocker)->where("urutan", $cekdata[0]->urutan)->where("created_by", Auth::user()->id)->first();
            if ($checkSecInhouseInTemp) {
                return array(
                    "status" => 400,
                    "message" => "Stocker sudah discan di temporary IN Secondary Dalam."
                );
            }

            // Insert to Secondary Inhouse to Temporary
            $storeSecondaryInhouseInTemp = SecondaryInhouseInTemp::updateOrCreate([
                    "id_qr_stocker" => $request->txtqrstocker,
                    "urutan" => $cekdata[0]->urutan,
                    "created_by" => Auth::user()->id,
                ], [
                    "qty" => $cekdata[0]->qty_awal,
                    "created_by_username" => Auth::user()->username,
                ]);
            if ($storeSecondaryInhouseInTemp) {
                return array(
                    "status" => 200,
                    "message" => "Stocker Berhasil disimpan ke temporary"
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Stocker tidak ditemukan."
        );
    }


    public function cek_data_stocker_inhouse_temp(Request $request)
    {
        $dataStockerInhouseTemp = DB::select("
            SELECT
            si.id,
            dc.id_qr_stocker,
            s.act_costing_ws,
            msb.buyer,
            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
            msb.styleno as style,
            s.color,
            COALESCE(msb.size, s.size) size,
            COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
            CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
            dc.tujuan,
            dc.lokasi,
            CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
            coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
            si.urutan
            from
            secondary_inhouse_in_temp si
            left join dc_in_input dc on dc.id_qr_stocker = si.id_qr_stocker
            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input a on s.form_cut_id = a.id
            left join form_cut_reject b on s.form_reject_id = b.id
            left join form_cut_piece c on s.form_piece_id = c.id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
            left join part p_com on p_com.id = pd_com.part_id
            left join master_part mp on pd.master_part_id = mp.id
            left join marker_input mi on a.id_marker = mi.kode
            where si.created_by = ".Auth::user()->id."
            order by si.updated_at desc
        ");

        return Datatables::of($dataStockerInhouseTemp)->toJson();
    }

    public function destroySecondaryInhouseInTemp(Request $request, $id = 0)
    {
        if ($id) {
            $checkSecondaryInhouseInTemp = SecondaryInhouseInTemp::where("id", $id)->where("created_by", Auth::user()->id)->first();

            if ($checkSecondaryInhouseInTemp) {
                $destroySecondaryInhouseInTemp = SecondaryInhouseInTemp::where("id", $id)->where("created_by", Auth::user()->id)->delete();

                if ($destroySecondaryInhouseInTemp) {
                    return array(
                        "status" => 200,
                        "message" => "Data berhasil dihapus",
                        "table" => "secondary-inhouse-in-temp-table"
                    );
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Data gagal dihapus.",
                    );
                }
            } else {
                return array(
                    "status" => 400,
                    "message" => "Data tidak ditemukan.",
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan.",
        );
    }

    public function storeSecondaryInhouseIn(Request $request)
    {
        // Get user's temporary data
        $batch = Str::uuid();
        $dataStockerInhouseInTemp = SecondaryInhouseInTemp::selectRaw("
                CURRENT_DATE() tgl_trans,
                secondary_inhouse_in_temp.id_qr_stocker,
                form_cut_input.no_form,
                secondary_inhouse_in_temp.qty qty_in,
                secondary_inhouse_in_temp.created_by_username as user,
                secondary_inhouse_in_temp.urutan,
                '".$batch."' as batch
            ")->
            leftJoin("stocker_input", "stocker_input.id_qr_stocker", "=", "secondary_inhouse_in_temp.id_qr_stocker")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            where("secondary_inhouse_in_temp.created_by", Auth::user()->id)->
            get();

        if ($dataStockerInhouseInTemp && $dataStockerInhouseInTemp->count() > 0) {
            // Insert to stocker inhouse in
            $storeStockerInhouseIn = SecondaryInhouseIn::upsert($dataStockerInhouseInTemp->toArray(), ["id_qr_stocker", "no_form", "urutan"]);

            if ($storeStockerInhouseIn) {
                // Delete user's temporary data
                SecondaryInhouseInTemp::where("created_by", Auth::user()->id)->delete();

                // Get stored data
                $storedStocker = SecondaryInhouseIn::select("id_qr_stocker")->where("batch", $batch)->pluck("id_qr_stocker");
                $storedStockerStr = "";
                if ($storedStocker) {
                    foreach ($storedStocker as $stocker) {
                        $storedStockerStr .= $stocker." berhasil disimpan. <br>";
                    }
                }

                return array(
                    "status" => 200,
                    "message" => $storedStockerStr,
                    "callback" => "datatableReload()"
                );
            }
        } else {
            return  array(
                "status" => 400,
                "message" => "Data temporary tidak ditemukan.",
            );
        }

        return  array(
            "status" => 400,
            "message" => "Data tidak berhasil disimpan.",
        );
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
                'message' => 'Qty In tidak bisa kurang dari 1',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        $saveinhouse = SecondaryInhouseIn::updateOrCreate(
            ['id_qr_stocker' => $request['txtno_stocker']],
            [
                'tgl_trans' => $tgltrans,
                'id_qr_stocker' => $request['txtno_stocker'],
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

        DB::update(
            "update stocker_input set status = 'secondary' where id_qr_stocker = '" . $request->txtno_stocker . "'"
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
                    style,
                    s.color,
                    COALESCE ( msb.size, s.size ) size,
                    mp.nama_part,
                    dc.tujuan,
                    dc.lokasi,
                    COALESCE ( s.qty_ply_mod, s.qty_ply ) - dc.qty_reject + dc.qty_replace qty_awal,
                    ifnull( si.id_qr_stocker, 'x' )
                FROM
                    dc_in_input dc
                    LEFT JOIN stocker_input s ON dc.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    LEFT JOIN form_cut_input a ON s.form_cut_id = a.id
                    LEFT JOIN form_cut_reject b ON s.form_reject_id = b.id
                    LEFT JOIN form_cut_piece c ON s.form_piece_id = c.id
                    LEFT JOIN part_detail p ON s.part_detail_id = p.id
                    LEFT JOIN master_part mp ON p.master_part_id = mp.id
                    LEFT JOIN marker_input mi ON a.id_marker = mi.kode
                    LEFT JOIN secondary_inhouse_in_input si ON dc.id_qr_stocker = si.id_qr_stocker
                    left join part_detail_secondary pds on pds.part_detail_id = pd.id
                    left join master_secondary mms on mms.id = pds.master_secondary_id
                    left join master_secondary ms on ms.id = pd.master_secondary_id
                WHERE
                    s.act_costing_ws = '".$thisStocker->act_costing_ws."' AND
                    s.color = '".$thisStocker->color."' AND
                    COALESCE(a.no_cut, c.no_cut, '-') = '".$thisStocker->no_cut."'
                    AND dc.tujuan = 'SECONDARY DALAM'
                    AND ifnull( si.id_qr_stocker, 'x' ) = 'x'
            ");

            foreach ($cekdata as $d) {
                $saveinhouse = SecondaryInhouseIn::create([
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

        $data = $this->secondaryInhouseInQuery($request, $from, $to)->orderByDesc('tgl_trans')->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary InHouse In Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary InHouse In Report', ['font-size' => 16]);
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
        $sheet->writeTo('L2', 'Tujuan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', 'Tempat')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', 'Lokasi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', 'Range')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', 'Created At')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('T2', 'Tujuan DC')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('U2', 'Lokasi DC')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('V2', 'Notes')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->tgl_trans_fix ?? "-",
                    $row->id_qr_stocker ?? "-",
                    $row->act_costing_ws ?? "-",
                    $row->style ?? "-",
                    $row->color ?? "-",
                    $row->nama_part ? preg_replace('/\s+/', ' ', $row->nama_part) : "-",
                    $row->part_status ?? "-",
                    $row->panel ? preg_replace('/\s+/', ' ', $row->panel) : "-",
                    $row->panel_status ?? "-",
                    $row->size ?? "-",
                    $row->no_cut ?? "-",
                    $row->tujuan ?? "-",
                    $row->tempat ?? "-",
                    $row->lokasi ?? "-",
                    $row->stocker_range ?? "-",
                    intval($row->qty_in) ?? 0,
                    $row->buyer ?? "-",
                    $row->user ?? "-",
                    $row->created_at ?? "-",
                    $row->tujuan ?? "-",
                    $row->lokasi ?? "-",
                    $row->notes ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan sec inhouse ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }

    public function exportExcelDetail(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $data = $this->secondaryInhouseDetailQuery($request, $from, $to)->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Secondary InHouse In Detail Report');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Secondary InHouse In Detail Report', ['font-size' => 16]);
        $sheet->mergeCells('A1:J1');

        // Headers
        $sheet->writeTo('A2', 'Tgl Transaksi')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Tujuan')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Qty In')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->tgl_trans ?? "-",
                    $row->act_costing_ws ?? "-",
                    $row->buyer ?? "-",
                    $row->styleno ?? "-",
                    $row->panel ?? "-",
                    $row->part_label ?? "-",
                    $row->color ?? "-",
                    $row->size ?? "-",
                    $row->tujuan ?? "-",
                    $row->qty_in ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan sec inhouse detail ' . $from . ' - ' . $to . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }
}
