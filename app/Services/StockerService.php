<?php

namespace App\Services;

use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\LoadingLine;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutPieceDetailSize;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartForm;
use App\Models\Stocker\ModifySizeQty;
use App\Models\Stocker\MonthCount;
use App\Models\Stocker\YearSequence;
use App\Models\Stocker\StockerAdditional;
use App\Models\Stocker\StockerAdditionalDetail;
use App\Models\Stocker\StockerSeparate;
use App\Models\Stocker\StockerSeparateDetail;
use Illuminate\Http\Request;
use DB;
use PDF;

class StockerService
{
    public function getStockerForPrint(array $filters = [])
    {
        $formCutId = $filters['formCutId'] ?? null;

        if ($formCutId > 0) {

            $partDetailId     = $filters['partDetailId']    ?? null;
            $soDetId          = $filters['soDetId']         ?? null;
            $group            = $filters['group']           ?? null;
            $groupStocker     = $filters['groupStocker']    ?? null;
            $multiPartDetail  = $filters['multiPartDetail'] ?? null;

            $stockerSql = Stocker::selectRaw("
                    (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    stocker_input.range_awal,
                    stocker_input.range_akhir,
                    MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', UPPER(part_com.panel_status)) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', UPPER(part.panel_status)) ELSE '' END))) panel,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.style,
                    UPPER(TRIM(marker_input.color)) as color,
                    stocker_input.shade,
                    stocker_input.group_stocker,
                    stocker_input.notes,
                    form_cut_input.no_cut,
                    CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL AND part_detail.part_status != 'regular' THEN CONCAT(' - ', UPPER(part_detail.part_status)) ELSE '' END)) part,
                    master_sb_ws.dest
                ")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_form", "part_form.part_id", "=", "part.id")->
                leftJoin(DB::raw("part_detail as part_detail_com"), function ($join) {
                    $join->on("part_detail_com.id", "=", "part_detail.from_part_detail");
                    $join->on("part_detail.part_status", "=", DB::raw("'complement'"));
                })->
                leftJoin(DB::raw("part as part_com"), "part_com.id", "=", "part_detail_com.part_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("master_size_new", "master_size_new.size", "=", "stocker_input.size")->
                leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                where("stocker_input.form_cut_id", $formCutId);
                if ($multiPartDetail) {
                    $stockerSql->whereIn("part_detail.id", $multiPartDetail);
                }
                if ($partDetailId) {
                    $stockerSql->where("part_detail.id", $partDetailId);
                }
                if ($soDetId) {
                    $stockerSql->where("stocker_input.so_det_id", $soDetId);
                }
                if ($group) {
                    $stockerSql->where("stocker_input.shade", $group);
                }
                if ($groupStocker) {
                    $stockerSql->where("stocker_input.group_stocker", $groupStocker);
                }
                $stockerData = $stockerSql->groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
                    orderBy("stocker_input.group_stocker", "desc")->
                    orderBy("stocker_input.so_det_id", "asc")->
                    orderByRaw("CAST(stocker_input.ratio AS UNSIGNED) ASC")->
                    get();

            return $stockerData;
        }

        return ["notes" => "Form tidak ditemukan"];
    }

    public function getStockerAdditionalForPrint(array $filters = [])
    {
        $formCutId = $filters['formCutId'] ?? null;

        if ($formCutId > 0) {
            $noWs = $filters['noWs'] ?? null;
            $style = $filters['style'] ?? null;
            $color = $filters['color'] ?? null;
            $multiPartDetail = $filters['multiPartDetail'] ?? null;

            $stockerAdditionalSql = Stocker::selectRaw("
                    (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    stocker_input.range_awal,
                    stocker_input.range_akhir,
                    stocker_input.id_qr_stocker,
                    stocker_ws_additional.act_costing_ws,
                    stocker_ws_additional.buyer,
                    stocker_ws_additional.style,
                    UPPER(TRIM(stocker_ws_additional.color)) color,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', UPPER(part_com.panel_status)) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', UPPER(part.panel_status)) ELSE '' END))) panel,
                    stocker_input.shade,
                    stocker_input.group_stocker,
                    stocker_input.notes,
                    form_cut_input.no_cut,
                    CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL AND part_detail.part_status != 'regular' THEN CONCAT(' - ', UPPER(part_detail.part_status)) ELSE '' END)) part,
                    master_sb_ws.dest
                ")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_form", "part_form.part_id", "=", "part.id")->
                leftJoin(DB::raw("part_detail as part_detail_com"), function ($join) {
                    $join->on("part_detail_com.id", "=", "part_detail.from_part_detail");
                    $join->on("part_detail.part_status", "=", DB::raw("'complement'"));
                })->
                leftJoin(DB::raw("part as part_com"), "part_com.id", "=", "part_detail_com.part_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("stocker_ws_additional", "stocker_ws_additional.form_cut_id", "=", "form_cut_input.id")->
                leftJoin("stocker_ws_additional_detail", "stocker_ws_additional_detail.stocker_additional_id", "=", "stocker_ws_additional.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "stocker_ws_additional_detail.size")->
                leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                where("form_cut_input.id", $formCutId);
                if ($noWs) {
                    $stockerAdditionalSql->where("stocker_ws_additional.act_costing_ws", $noWs);
                }
                if ($style) {
                    $stockerAdditionalSql->where("stocker_ws_additional.style", $style);
                }
                if ($color) {
                    $stockerAdditionalSql->whereRaw("UPPER(TRIM(stocker_ws_additional.color)) = '".strtoupper(trim($color))."'");
                }
                if ($multiPartDetail) {
                    $stockerAdditionalSql->whereIn("part_detail.id", $multiPartDetail);
                }
                $stockerData = $stockerAdditionalSql->groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
                orderBy("stocker_input.group_stocker", "desc")->
                orderBy("stocker_input.shade", "desc")->
                orderBy("stocker_input.so_det_id", "asc")->
                orderByRaw("CAST(stocker_input.ratio AS UNSIGNED) asc")->
                get();

            return $stockerData;
        }

        return ["notes" => "Form tidak ditemukan"];
    }

    public function getStockerGenerate($formCutId)
    {
        $dataSpreading = FormCutInput::selectRaw("
                part.id part_id,
                part_detail.id part_detail_id,
                form_cut_input.id as form_cut_id,
                form_cut_input.id,
                form_cut_input.no_meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                DATE(form_cut_input.waktu_selesai) tgl_form_cut,
                marker_input.id marker_id,
                marker_input.act_costing_ws ws,
                marker_input.buyer,
                marker_input.panel,
                marker_input.color,
                marker_input.style,
                form_cut_input.status,
                users.name nama_meja,
                marker_input.panjang_marker,
                UPPER(marker_input.unit_panjang_marker) unit_panjang_marker,
                marker_input.comma_marker,
                UPPER(marker_input.unit_comma_marker) unit_comma_marker,
                marker_input.lebar_marker,
                UPPER(marker_input.unit_lebar_marker) unit_lebar_marker,
                form_cut_input.qty_ply,
                marker_input.gelar_qty,
                marker_input.po_marker,
                marker_input.urutan_marker,
                marker_input.cons_marker,
                form_cut_input.total_lembar,
                form_cut_input.no_cut,
                UPPER(form_cut_input.shell) shell,
                GROUP_CONCAT(DISTINCT COALESCE(master_size_new.size, marker_input_detail.size) ORDER BY master_size_new.urutan ASC SEPARATOR ', ') sizes,
                GROUP_CONCAT(DISTINCT CONCAT(' ', COALESCE(master_size_new.size, marker_input_detail.size), '(', marker_input_detail.ratio * form_cut_input.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) SEPARATOR ', ') part,
                part.panel_status
            ")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.id", $formCutId)->
            groupBy("form_cut_input.id")->
            first();

        $dataPartForm = PartForm::selectRaw("part_form.form_id, form_cut_input.no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "part_form.form_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            where("marker_input.color", $dataSpreading->color)->
            where("part_form.part_id", $dataSpreading->part_id)->
            whereRaw("(form_cut_input.no_cut <= ".$dataSpreading->no_cut." or form_cut_input.no_cut > ".$dataSpreading->no_cut.")")->
            get();

        $dataPartDetail = PartDetail::selectRaw("
                part_detail.id,
                master_part.nama_part,
                master_part.bag,
                GROUP_CONCAT(DISTINCT COALESCE(master_secondary.tujuan, '-') SEPARATOR ' | ') tujuan,
                GROUP_CONCAT(COALESCE(master_secondary.proses, '-') SEPARATOR ' | ') proses,
                GROUP_CONCAT(DISTINCT COALESCE(master_secondary.proses, '-') ORDER BY part_detail_secondary.urutan SEPARATOR ' , ') proses_tujuan
            ")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("part", "part.id", "part_detail.part_id")->
            leftJoin("part_form", "part_form.part_id", "part.id")->
            leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")->
            leftJoin("part_detail_secondary", "part_detail_secondary.part_detail_id", "=", "part_detail.id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail_secondary.master_secondary_id")->
            where("form_cut_input.id", $formCutId)->
            groupBy("master_part.id")->
            get();

        $dataRatio = MarkerDetail::selectRaw("
                marker_input_detail.id marker_detail_id,
                marker_input_detail.so_det_id,
                COALESCE(master_sb_ws.size, marker_input_detail.size) size,
                COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), marker_input_detail.size) size_dest,
                marker_input_detail.ratio
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            where("marker_input.id", $dataSpreading->marker_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            orderBy("marker_input_detail.id", "asc")->
            groupBy("marker_input_detail.id")->
            get();

        $dataStocker = MarkerDetail::selectRaw("
                MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                marker_input.color,
                marker_input_detail.so_det_id,
                COALESCE(stocker_input.ratio, marker_input_detail.ratio) ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(stocker_input.id) stocker_id,
                MAX(stocker_input.shade) shade,
                MAX(stocker_input.group_stocker) group_stocker,
                MAX(stocker_input.qty_ply) qty_ply,
                MIN(CAST(stocker_input.range_awal as UNSIGNED)) range_awal,
                MAX(CAST(stocker_input.range_akhir as UNSIGNED)) range_akhir,
                modify_size_qty.modified_qty,
                modify_size_qty.difference_qty
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("stocker_input", function ($join) {
                $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            leftJoin("modify_size_qty", function ($join) {
                $join->on("modify_size_qty.form_cut_id", "=", "form_cut_input.id");
                $join->on("modify_size_qty.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("part_form.part_id", $dataSpreading->part_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

        $dataNumbering = MarkerDetail::selectRaw("
                marker_input.color,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(number.numbering_id) numbering_id,
                MAX(number.no_cut_size) no_cut_size,
                MAX(number.range_akhir) range_akhir,
                modify_size_qty.difference_qty
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin(
                DB::raw(
                    "(
                        SELECT
                            stocker_numbering.form_cut_id,
                            stocker_numbering.so_det_id,
                            MAX( stocker_numbering.no_cut_size ) no_cut_size,
                            MAX( stocker_numbering.id ) numbering_id,
                            MAX( stocker_numbering.number ) range_akhir
                        FROM
                            form_cut_input
                            INNER JOIN marker_input ON form_cut_input.id_marker = marker_input.kode
                            INNER JOIN `stocker_numbering` ON form_cut_input.id = stocker_numbering.form_cut_id
                        WHERE
                            `marker_input`.`act_costing_ws` = '".$dataSpreading->ws."'
                            AND `marker_input`.`color` = '".$dataSpreading->color."'
                            AND `marker_input`.`panel` = '".$dataSpreading->panel."'
                            AND ( stocker_numbering.cancel IS NULL OR stocker_numbering.cancel != 'Y' )
                            AND `form_cut_input`.`no_cut` <= ".$dataSpreading->no_cut."
                        GROUP BY
                            stocker_numbering.form_cut_id,
                            stocker_numbering.so_det_id
                    ) number"
                ), function ($join) {
                    $join->on("number.form_cut_id", "=", "form_cut_input.id");
                    $join->on("number.so_det_id", "=", "marker_input_detail.so_det_id");
                }
            )->
            leftJoin("modify_size_qty", function ($join) {
                $join->on("modify_size_qty.form_cut_id", "=", "form_cut_input.id");
                $join->on("modify_size_qty.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("part_form.part_id", $dataSpreading->part_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

        $modifySizeQty = ModifySizeQty::selectRaw("modify_size_qty.*, master_sb_ws.size, master_sb_ws.dest ")->leftJoin("master_sb_ws","master_sb_ws.id_so_det", "=", "modify_size_qty.so_det_id")->where("form_cut_id", $dataSpreading->form_cut_id)->where("difference_qty", "!=", 0)->get();

        $dataAdditional = DB::table("stocker_ws_additional")->where("form_cut_id", $dataSpreading->form_cut_id)->first();

        if ($dataAdditional) {
            $dataPartDetailAdditional = StockerAdditional::selectRaw("
                    part_detail.id,
                    master_part.nama_part,
                    master_part.bag,
                    COALESCE(master_secondary.tujuan, '-') tujuan,
                    COALESCE(master_secondary.proses, '-') proses
                ")->
                leftJoin("part", function($join) {
                    $join->on("stocker_ws_additional.act_costing_id", "=", "part.act_costing_id");
                    $join->on("stocker_ws_additional.panel", "=", "part.panel");
                })->
                leftJoin("part_detail", "part_detail.part_id", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                where("stocker_ws_additional.id", $dataAdditional->id)->
                groupBy("master_part.id")->
                get();

            $dataRatioAdditional = DB::table("stocker_ws_additional_detail")->selectRaw("
                    stocker_ws_additional_detail.id additional_detail_id,
                    stocker_ws_additional_detail.so_det_id,
                    master_sb_ws.color,
                    COALESCE(master_sb_ws.size, stocker_ws_additional_detail.size) size,
                    master_sb_ws.dest dest,
                    COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), stocker_ws_additional_detail.size) size_dest,
                    stocker_ws_additional_detail.ratio
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_ws_additional_detail.so_det_id")->
                leftJoin("stocker_ws_additional", "stocker_ws_additional.id", "=", "stocker_ws_additional_detail.stocker_additional_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_ws_additional.form_cut_id")->
                where("stocker_ws_additional.id", $dataAdditional->id)->
                // where("marker_input_detail.ratio", ">", "0")->
                orderBy("stocker_ws_additional_detail.id", "asc")->
                groupBy("stocker_ws_additional_detail.id")->
                get();

            $dataStockerAdditional = DB::table("stocker_ws_additional_detail")->selectRaw("
                    MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                    stocker_ws_additional.color,
                    stocker_ws_additional_detail.so_det_id,
                    CONCAT(master_sb_ws.color, master_sb_ws.size, master_sb_ws.dest) info,
                    COALESCE(stocker_input.ratio, stocker_ws_additional_detail.ratio) ratio,
                    MAX(form_cut_input.no_form) no_form,
                    form_cut_input.no_cut,
                    MAX(stocker_input.id) stocker_id,
                    MAX(stocker_input.shade) shade,
                    MAX(stocker_input.group_stocker) group_stocker,
                    MAX(stocker_input.qty_ply) qty_ply,
                    MIN(CAST(stocker_input.range_awal as UNSIGNED)) range_awal,
                    MAX(CAST(stocker_input.range_akhir as UNSIGNED)) range_akhir,
                    modify_size_qty.modified_qty,
                    modify_size_qty.difference_qty
                ")->
                leftJoin("stocker_ws_additional", "stocker_ws_additional.id", "=", "stocker_ws_additional_detail.stocker_additional_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_ws_additional.form_cut_id")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_ws_additional_detail.so_det_id")->
                leftJoin("stocker_input", function ($join) {
                    $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                    $join->on("stocker_input.so_det_id", "=", "stocker_ws_additional_detail.so_det_id");
                })->
                leftJoin("modify_size_qty", function ($join) {
                    $join->on("modify_size_qty.form_cut_id", "=", "form_cut_input.id");
                    $join->on("modify_size_qty.so_det_id", "=", "stocker_ws_additional_detail.so_det_id");
                })->
                where("stocker_ws_additional.act_costing_ws", $dataAdditional->act_costing_ws)->
                where("stocker_ws_additional.color", $dataAdditional->color)->
                where("stocker_ws_additional.panel", $dataAdditional->panel)->
                where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
                where("part_form.part_id", $dataSpreading->part_id)->
                // where("marker_input_detail.ratio", ">", "0")->
                groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "stocker_ws_additional_detail.so_det_id")->
                orderBy("form_cut_input.no_cut", "desc")->
                orderBy("form_cut_input.no_form", "desc")->
                get();
        } else {
            $dataPartDetailAdditional = null;
            $dataRatioAdditional = null;
            $dataStockerAdditional = null;
        }

        return ["dataSpreading" => $dataSpreading, "dataPartForm" => $dataPartForm, "dataPartDetail" => $dataPartDetail, "dataRatio" => $dataRatio, "dataStocker" => $dataStocker, "dataNumbering" => $dataNumbering, "modifySizeQty" => $modifySizeQty, "dataAdditional" => $dataAdditional, "dataPartDetailAdditional" => $dataPartDetailAdditional, "dataRatioAdditional" => $dataRatioAdditional, "dataStockerAdditional" => $dataStockerAdditional];
    }

    public function reorderStockerNumbering($partId, $color = null)
    {
        ini_set('max_execution_time', 360000);

        $colorFormCutFilter = $color ? " and UPPER(TRIM(marker_input.color)) = '".strtoupper(trim($color))."'" : null;
        $colorFormPieceFilter = $color ? " and UPPER(TRIM(form_cut_piece.color)) = '".strtoupper(trim($color))."'" : null;

        $formCutInputs = collect(DB::select("
            SELECT
                UPPER(TRIM(marker_input.color)) color,
                form_cut_input.id AS id_form,
                form_cut_input.no_cut,
                form_cut_input.no_form AS no_form,
                form_cut_input.waktu_selesai,
                'GENERAL' AS type
            FROM
                `form_cut_input`
                LEFT JOIN `part_form` ON `part_form`.`form_id` = `form_cut_input`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input_detail`.`marker_id` = `marker_input`.`id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `marker_input_detail`.`size`
                LEFT JOIN `users` ON `users`.`id` = `form_cut_input`.`no_meja`
            WHERE
                part_form.id IS NOT NULL
                AND `part`.`id` = ".$partId."
                ".$colorFormCutFilter."
                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_input`.`id`

            UNION

            SELECT
                UPPER(TRIM(form_cut_piece.color)) color,
                form_cut_piece.id AS id_form,
                form_cut_piece.no_cut,
                form_cut_piece.no_form AS no_form,
                form_cut_piece.updated_at as waktu_selesai,
                'PIECE' AS type
            FROM
                `form_cut_piece`
                LEFT JOIN `part_form` ON `part_form`.`form_pcs_id` = `form_cut_piece`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `form_cut_piece_detail_size`.`size`
            WHERE
                part_form.id IS NOT NULL
                AND `part`.`id` = ".$partId."
                ".$colorFormPieceFilter."
                AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piece`.`id`
            ORDER BY
                `color` ASC,
                `waktu_selesai` ASC,
                `no_cut` ASC
        "));

        $rangeAwal = 0;
        $sizeRangeAkhir = collect();

        $rangeAwalAdd = 0;
        $sizeRangeAkhirAdd = collect();

        $currentColor = "";
        $currentNumber = 0;

        // Loop over all forms
        foreach ($formCutInputs as $formCut) {
            // Reset cumulative data on color switch
            if ($formCut->color != $currentColor) {
                $rangeAwal = 0;
                $sizeRangeAkhir = collect();

                $rangeAwalAdd = 0;
                $sizeRangeAkhirAdd = collect();

                $currentColor = $formCut->color;
                $currentNumber = 0;
            }

            // Type Checking
            if ($formCut->type == "PIECE") {
                // Piece Form
                $currentNumber++;
                FormCutPiece::where("id", $formCut->id_form)->update([
                    "no_cut" => $currentNumber
                ]);

                $stockerForm = Stocker::where("form_piece_id", $formCut->id_form)->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
                $currentStockerSize = "";

                foreach ($stockerForm as $key => $stocker) {
                    $lembarGelaran = FormCutPieceDetailSize::selectRaw("form_cut_piece_detail_size.*")->leftJoin("form_cut_piece_detail", "form_cut_piece_detail.id", "=", "form_cut_piece_detail_size.form_detail_id")->where("form_id", $formCut->id_form)->where("so_det_id", $stocker->so_det_id)->sum("form_cut_piece_detail_size.qty");

                    $separate = StockerSeparateDetail::selectRaw("stocker_separate_detail.*")->leftJoin("stocker_separate", "stocker_separate.id", "=", "stocker_separate_detail.separate_id")->where("form_piece_id", $formCut->id_form)->where("so_det_id", $stocker->so_det_id)->where("group_stocker", $stocker->group_stocker)->where("group_roll", $stocker->shade)->where("urutan", $stocker->ratio)->first();

                    if ($separate) {
                        $lembarGelaran = $separate->qty;
                    }

                    if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id)) {
                        $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                        $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                        $currentStockerSize = $stocker->so_det_id;
                    } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                        $rangeAwal =  1;
                        $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                    }

                    $stocker->so_det_id && (($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty_ply || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = null;
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = isset($sizeRangeAkhir[$stocker->so_det_id]) ? $sizeRangeAkhir[$stocker->so_det_id] : $rangeAwal + $lembarGelaran;
                    $stocker->save();

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    }
                }
            } else {
                // Regular Form
                $modifySizeQty = ModifySizeQty::selectRaw("modify_size_qty.*, master_sb_ws.size, master_sb_ws.dest ")->leftJoin("master_sb_ws","master_sb_ws.id_so_det", "=", "modify_size_qty.so_det_id")->where("form_cut_id", $formCut->id_form)->get();

                // Adjust form data
                $currentNumber++;
                FormCutInput::where("id", $formCut->id_form)->update([
                    "no_cut" => $currentNumber
                ]);

                // Adjust form cut detail data
                $formCutInputDetails = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->orderBy("created_at", "asc")->orderBy("updated_at", "asc")->get();

                $currentGroup = "";
                $currentGroupNumber = 0;
                foreach ($formCutInputDetails as $formCutInputDetail) {
                    if ($currentGroup != $formCutInputDetail->group_roll) {
                        $currentGroup = $formCutInputDetail->group_roll;
                        $currentGroupNumber += 1;
                    }

                    $formCutInputDetail->group_stocker = $currentGroupNumber;
                    $formCutInputDetail->save();
                }

                // Adjust stocker data
                $stockerForm = Stocker::withoutGlobalScopes()->where("form_cut_id", $formCut->id_form)->whereRaw("(`notes` IS NULL OR `notes` NOT LIKE '%ADDITIONAL%')")->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
                $currentStockerSize = "";
                $currentStockerGroup = "initial";
                $currentStockerRatio = 0;

                $currentModifySizeQty = $modifySizeQty->filter(function ($item) {
                    return !is_null($item->group_stocker);
                })->count();

                foreach ($stockerForm as $key => $stocker) {
                    $lembarGelaran = 1;
                    if ($stocker->group_stocker) {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                    } else {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                    }

                    if ($currentStockerPart == $stocker->part_detail_id) {
                        if ($stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {

                            $modifyThis = null;
                            if ($currentModifySizeQty > 0) {
                                $modifyThis = $modifySizeQty->where("group_stocker", $stocker->group_stocker)->where("so_det_id", $stocker->so_det_id)->first();
                            } else {
                                if ($stockerForm->min("group_stocker") == $stocker->group) {
                                    $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();
                                }
                            }

                            if ($modifyThis) {
                                $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                            }
                        }

                        $separate = StockerSeparateDetail::selectRaw("stocker_separate_detail.*")->leftJoin("stocker_separate", "stocker_separate.id", "=", "stocker_separate_detail.separate_id")->where("form_cut_id", $formCut->id_form)->where("so_det_id", $stocker->so_det_id)->where("group_stocker", $stocker->group_stocker)->where("group_roll", $stocker->shade)->where("urutan", $stocker->ratio)->first();

                        if ($separate) {
                            $lembarGelaran = $separate->qty;
                        }

                        if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                            $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                            $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                            $currentStockerSize = $stocker->so_det_id;
                            $currentStockerGroup = $stocker->group_stocker;
                            $currentStockerRatio = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                            $rangeAwal =  1;
                            $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                        }
                    }

                    $stocker->so_det_id && (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty_ply || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = null;
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = $stocker->range_akhir = isset($sizeRangeAkhir[$stocker->so_det_id]) ? $sizeRangeAkhir[$stocker->so_det_id] : $rangeAwal + $lembarGelaran;
                    $stocker->save();

                    $checkFormRatio = $stocker->formCut->marker->markerDetails()->where("so_det_id", $stocker->so_det_id)->orderBy("ratio", "desc")->first();
                    $checkFormPart = $stocker->partDetail->part;
                    $formPartWs = $checkFormPart ? ($checkFormPart->act_costing_ws ?? null) : null;
                    $maxFormRatio = $checkFormRatio ? ($checkFormRatio->ratio ?? null) : null;

                    if (($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) || ($maxFormRatio && $stocker->ratio > $maxFormRatio)) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    } else {
                        $stocker->cancel = "n";
                        $stocker->save();
                    }
                }

                // Stocker Additional
                $stockerFormAdd = Stocker::selectRaw("stocker_input.*, master_sb_ws.dest")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->where("form_cut_id", $formCut->id_form)->where("notes", "ADDITIONAL")->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPartAdd = $stockerFormAdd->first() ? $stockerFormAdd->first()->part_detail_id : "";
                $currentStockerSizeAdd = "";
                $currentStockerGroupAdd = "initial";
                $currentStockerRatioAdd = 0;

                $currentModifySizeQty = $modifySizeQty->filter(function ($item) {
                    return !is_null($item->group_stocker);
                })->count();

                foreach ($stockerFormAdd as $key => $stocker) {
                    $lembarGelaran = 1;
                    if ($stocker->group_stocker) {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                    } else {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                    }

                    if ($currentStockerPartAdd == $stocker->part_detail_id) {
                        if ($stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {

                            $modifyThis = null;
                            if ($currentModifySizeQty > 0) {
                                $modifyThis = $modifySizeQty->where("group_stocker", $stocker->group_stocker)->where("size", $stocker->size)->where("dest", $stocker->dest)->first();
                            } else {
                                if ($stockerForm->min("group_stocker") == $stocker->group) {
                                    $modifyThis = $modifySizeQty->where("size", $stocker->size)->where("dest", $stocker->dest)->first();
                                }
                            }

                            if ($modifyThis) {
                                $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                            }
                        }

                        $separate = StockerSeparateDetail::selectRaw("stocker_separate_detail.*")->leftJoin("stocker_separate", "stocker_separate.id", "=", "stocker_separate_detail.separate_id")->where("form_cut_id", $formCut->id_form)->where("so_det_id", $stocker->so_det_id)->where("group_stocker", $stocker->group_stocker)->where("group_roll", $stocker->shade)->where("urutan", $stocker->ratio)->first();

                        if ($separate) {
                            $lembarGelaran = $separate->qty;
                        }

                        if (isset($sizeRangeAkhirAdd[$stocker->so_det_id]) && ($currentStockerSizeAdd != $stocker->so_det_id || $currentStockerGroupAdd != $stocker->group_stocker || $currentStockerRatioAdd != $stocker->ratio)) {
                            $rangeAwalAdd = $sizeRangeAkhirAdd[$stocker->so_det_id] + 1;
                            $sizeRangeAkhirAdd[$stocker->so_det_id] = ($sizeRangeAkhirAdd[$stocker->so_det_id] + $lembarGelaran);

                            $currentStockerSizeAdd = $stocker->so_det_id;
                            $currentStockerGroupAdd = $stocker->group_stocker;
                            $currentStockerRatioAdd = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhirAdd[$stocker->so_det_id])) {
                            $rangeAwalAdd =  1;
                            $sizeRangeAkhirAdd->put($stocker->so_det_id, $lembarGelaran);
                        }
                    }

                    $stocker->so_det_id && (($sizeRangeAkhirAdd[$stocker->so_det_id] - ($rangeAwalAdd-1)) != $stocker->qty_ply || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhirAdd[$stocker->so_det_id] - ($rangeAwalAdd-1))) : $stocker->qty_ply_mod = null;
                    $stocker->range_awal = $rangeAwalAdd;
                    $stocker->range_akhir = isset($sizeRangeAkhirAdd[$stocker->so_det_id]) ? $sizeRangeAkhirAdd[$stocker->so_det_id] : $rangeAwalAdd + $lembarGelaran;
                    $stocker->save();

                    $checkFormRatio = $stocker->formCut->marker->markerDetails()->where("so_det_id", $stocker->so_det_id)->orderBy("ratio", "desc")->first();
                    $checkFormPart = $stocker->partDetail->part;
                    $formPartWs = $checkFormPart ? ($checkFormPart->act_costing_ws ?? null) : null;
                    $maxFormRatio = $checkFormRatio ? ($checkFormRatio->ratio ?? null) : null;

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1 || ($formPartWs && $formPartWs != $stocker->act_costing_ws) || ($maxFormRatio && $stocker->ratio > $maxFormRatio)) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    } else {
                        $stocker->cancel = "n";
                        $stocker->save();
                    }
                }

                // Adjust numbering data
                $numbers = StockerDetail::selectRaw("
                        form_cut_id,
                        act_costing_ws,
                        color,
                        panel,
                        so_det_id,
                        size,
                        no_cut_size,
                        MAX(number) number
                    ")->
                    where("form_cut_id", $formCut->id_form)->
                    whereRaw("(cancel is null OR cancel = 'N')")->
                    groupBy("form_cut_id", "size")->
                    get();

                // Numbering Data
                foreach ($numbers as $number) {
                    if (isset($sizeRangeAkhir[$number->so_det_id])) {
                        if ($number->number > $sizeRangeAkhir[$number->so_det_id]) {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", ">", $sizeRangeAkhir[$number->so_det_id])->
                                update([
                                    "cancel" => "Y"
                                ]);
                        } else {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", "<=", $sizeRangeAkhir[$number->so_det_id])->
                                where("cancel", "Y")->
                                update([
                                    "cancel" => "N"
                                ]);
                        }

                        if ($number->number < $sizeRangeAkhir[$number->so_det_id]) {
                            $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                            $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                            $no = 0;
                            for ($i = $number->number; $i < $sizeRangeAkhir[$number->so_det_id]; $i++) {
                                StockerDetail::create([
                                    "kode" => "WIP-".($stockerDetailCount+$no),
                                    "form_cut_id" => $number->form_cut_id,
                                    "act_costing_ws" => $number->act_costing_ws,
                                    "color" => $number->color,
                                    "panel" => $number->panel,
                                    "so_det_id" => $number->so_det_id,
                                    "size" => $number->size,
                                    "no_cut_size" => $noCutSize. sprintf('%04s', ($i+1)),
                                    "number" => $i+1
                                ]);

                                $no++;
                            }
                        }
                    }
                }
            }

            \Log::channel("reorderStockerNumbering")->info("row ".$formCut->no_form." & ".$formCut->no_cut);
        }

        return $sizeRangeAkhir;
    }

    public function printYearSequence($year, $yearSequence, $rangeAwal, $rangeAkhir) {
        $yearSequence = YearSequence::selectRaw("(CASE WHEN COALESCE(master_sb_ws.reff_no, '-') != '-' THEN master_sb_ws.reff_no ELSE master_sb_ws.styleno END) style, UPPER(TRIM(master_sb_ws.color)) color, master_sb_ws.size, id_year_sequence, year, year_sequence, year_sequence_number")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            where("year", $year)->
            where("year_sequence", $yearSequence)->
            where("year_sequence_number", ">=", $rangeAwal)->
            where("year_sequence_number", "<=", $rangeAkhir)->
            orderBy("year_sequence", "asc")->
            orderBy("year_sequence_number", "asc")->
            get()->
            toArray();

        $customPaper = array(0, 0, 35.35, 110.90);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence-1-new', ["data" => $yearSequence])->setPaper($customPaper);

        $fileName = str_replace("/", "-", ('Year Sequence.pdf'));
    }

    function recalculateStockerTransaction($formCutId = null){
        $stockers = Stocker::selectRaw("stocker_input.*")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input as secondary_inhouse", "secondary_inhouse.id_qr_stocker", "=", "dc_in_input.id_qr_stocker")->
            leftJoin("secondary_in_input as secondary_in", "secondary_in.id_qr_stocker", "=", "secondary_inhouse.id_qr_stocker")->
            whereRaw("
                (
                    dc_in_input.qty_awal != COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) OR
                    (COALESCE(dc_in_input.qty_awal, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0)) != COALESCE(secondary_inhouse.qty_awal, 0) OR
                    (COALESCE(secondary_inhouse.qty_in, 0)) != COALESCE(secondary_in.qty_awal, 0)
                )
            ".($formCutId ? " and stocker_input.form_cut_id = '".$formCutId."' " : " AND DATE(stocker_input.updated_at) > '".(date('Y-m-d', strtotime('-7 days')))."'")."")->
            get();

        // dd($stockers);

        $log = [];
        foreach ($stockers as $s) {
            $s->qty_ply_mod = null;
            $s->save();

            $dc = DcIn::where("id_qr_stocker", $s->id_qr_stocker)->first();
            if ($dc) {
                $dc->qty_awal = $s->qty_ply_mod != null ? $s->qty_ply_mod : $s->qty_ply;
                $dc->save();

                // Sec inhouse
                $secondaryInhouse = SecondaryInhouse::where("id_qr_stocker", $s->id_qr_stocker)->first();
                if ($secondaryInhouse) {
                    $secondaryInhouse->qty_awal = $dc->qty_awal - $dc->qty_reject + $dc->qty_replace;
                    $secondaryInhouse->qty_in = $secondaryInhouse->qty_awal - $secondaryInhouse->qty_reject + $secondaryInhouse->qty_replace;
                    $secondaryInhouse->save();

                    // Sec in
                    $secondaryIn = SecondaryIn::where("id_qr_stocker", $s->id_qr_stocker)->first();
                    if ($secondaryIn) {
                        $secondaryIn->qty_awal = $secondaryInhouse->qty_in;
                        $secondaryIn->qty_in = $secondaryIn->qty_awal - $secondaryIn->qty_reject + $secondaryIn->qty_replace;
                        $secondaryIn->save();
                    }
                }

                // Loading Line
                $loadingLine = LoadingLine::where("stocker_id", $s->id)->first();
                if ($loadingLine) {
                    $loadingLine->qty = (isset($secondaryIn) ? $secondaryIn->qty_in : ($dc->qty_awal - $dc->qty_reject + $dc->qty_replace));
                    $loadingLine->save();
                }

                array_push($log, $s->id_qr_stocker." Qty Updated.");
            }
        }

        return $log;
    }
}
