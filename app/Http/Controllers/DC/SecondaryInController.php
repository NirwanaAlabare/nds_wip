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
use Carbon\Carbon;
use DB;

class SecondaryInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        $data_trolley = DB::select("select nama_trolley isi, nama_trolley tampil from trolley");
        // dd($data_rak);
        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
            }

            $keywordQuery = '';
            if ($request->search['value']) {
                $keywordQuery =
                    "
                        (
                            line like '%" .
                                $request->search['value']
                            . "%'
                        )
                    ";
            }

            if ($request->sec_filter_tipe && count($request->sec_filter_tipe) > 0) {
                $additionalQuery .= " and (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) in (".addQuotesAround(implode("\n", $request->sec_filter_tipe)).")";
            }
            if ($request->sec_filter_buyer && count($request->sec_filter_buyer) > 0) {
                $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->sec_filter_buyer)).")";
            }
            if ($request->sec_filter_ws && count($request->sec_filter_ws) > 0) {
                $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->sec_filter_ws)).")";
            }
            if ($request->sec_filter_style && count($request->sec_filter_style) > 0) {
                $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->sec_filter_style)).")";
            }
            if ($request->sec_filter_color && count($request->sec_filter_color) > 0) {
                $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->sec_filter_color)).")";
            }
            if ($request->sec_filter_part && count($request->sec_filter_part) > 0) {
                $additionalQuery .= " and mp.nama_part in (".addQuotesAround(implode("\n", $request->sec_filter_part)).")";
            }
            if ($request->sec_filter_size && count($request->sec_filter_size) > 0) {
                $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->sec_filter_size)).")";
            }
            if ($request->size_filter && count($request->size_filter) > 0) {
                $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->size_filter)).")";
            }
            if ($request->sec_filter_no_cut && count($request->sec_filter_no_cut) > 0) {
                $additionalQuery .= " and COALESCE(f.no_cut, fp.no_cut, '-') in (".addQuotesAround(implode("\n", $request->sec_filter_no_cut)).")";
            }
            if ($request->sec_filter_tujuan && count($request->sec_filter_tujuan) > 0) {
                $additionalQuery .= " and a.tujuan in (".addQuotesAround(implode("\n", $request->sec_filter_tujuan)).")";
            }
            if ($request->sec_filter_tempat && count($request->sec_filter_tempat) > 0) {
                $additionalQuery .= " and a.tempat in (".addQuotesAround(implode("\n", $request->sec_filter_tempat)).")";
            }
            if ($request->sec_filter_lokasi && count($request->sec_filter_lokasi) > 0) {
                $additionalQuery .= " and a.lokasi in (".addQuotesAround(implode("\n", $request->sec_filter_lokasi)).")";
            }

            $data_input = DB::select("
                SELECT
                a.id_qr_stocker,
                (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                COALESCE(mx.tujuan, dc.tujuan) tujuan,
                COALESCE(mx.proses, dc.lokasi) lokasi,
                s.lokasi lokasi_rak,
                COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                a.created_at,
                CONCAT(s.range_awal, ' - ', s.range_akhir,
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
                ) stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
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
                left join master_part mp on mp.id = pd.master_part_id
                left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
                left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
                where
                a.tgl_trans is not null
                AND (
                    a.urutan IS NULL
                    OR a.urutan = mx.max_urutan
                )
                ".$additionalQuery."
                group by a.id
                order by a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('dc.secondary-in.secondary-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-in", "data_rak" => $data_rak, "data_trolley" => $data_trolley], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryIn(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
        }

        $data_input = collect(DB::select("
            SELECT
            a.id_qr_stocker,
            (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            a.tgl_trans,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            dc.tujuan,
            dc.lokasi,
            s.lokasi lokasi_rak,
            COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
            COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
            COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
            COALESCE(mx.qty_akhir, a.qty_in) qty_in,
            a.created_at,
            COALESCE(f.no_cut, fp.no_cut, '-'),
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
            left join master_part mp on mp.id = pd.master_part_id
            left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
            where
            a.tgl_trans is not null
            AND (
                a.urutan IS NULL
                OR a.urutan = mx.max_urutan
            )
            ".$additionalQuery."
            group by a.id
            order by a.tgl_trans desc
        "));

        $tipe = $data_input->groupBy("tipe")->keys();
        $act_costing_ws = $data_input->groupBy("act_costing_ws")->keys();
        $color = $data_input->groupBy("color")->keys();
        $buyer = $data_input->groupBy("buyer")->keys();
        $style = $data_input->groupBy("style")->keys();
        $tujuan = $data_input->groupBy("tujuan")->keys();
        $lokasi = $data_input->groupBy("lokasi")->keys();
        $lokasi_rak = $data_input->groupBy("lokasi_rak")->keys();
        $part = $data_input->groupBy("nama_part")->keys();
        $no_cut = $data_input->groupBy("no_cut")->keys();
        $size = $data_input->groupBy("size")->keys();

        return  array(
            "tipe" => $tipe,
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "tujuan" => $tujuan,
            "lokasi" => $lokasi,
            "lokasi_rak" => $lokasi_rak,
            "part" => $part,
            "no_cut" => $no_cut,
            "size" => $size
        );
    }

    public function detail_stocker_in(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and (si.tgl_trans >= '" . $request->dateFrom . "') ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (si.tgl_trans <= '" . $request->dateTo . "') ";
            }

            if ($request->detail_sec_filter_buyer && count($request->detail_sec_filter_buyer) > 0) {
                $additionalQuery .= " and m.buyer in (".addQuotesAround(implode("\n", $request->detail_sec_filter_buyer)).")";
            }
            if ($request->detail_sec_filter_ws && count($request->detail_sec_filter_ws) > 0) {
                $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->detail_sec_filter_ws)).")";
            }
            if ($request->detail_sec_filter_style && count($request->detail_sec_filter_style) > 0) {
                $additionalQuery .= " and styleno in (".addQuotesAround(implode("\n", $request->detail_sec_filter_style)).")";
            }
            if ($request->detail_sec_filter_color && count($request->detail_sec_filter_color) > 0) {
                $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->detail_sec_filter_color)).")";
            }
            if ($request->detail_sec_filter_lokasi && count($request->detail_sec_filter_lokasi) > 0) {
                $additionalQuery .= " and dc.lokasi in (".addQuotesAround(implode("\n", $request->detail_sec_filter_lokasi)).")";
            }

            $data_input = DB::select("
                select
                    s.act_costing_ws, m.buyer,s.color,styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(si.qty_reject), 0) qty_reject, COALESCE(sum(si.qty_replace), 0) qty_replace, COALESCE(sum(si.qty_in), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace -  si.qty_in), 0) balance, dc.tujuan,dc.lokasi
                from
                    dc_in_input dc
                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws m on s.so_det_id = m.id_so_det
                    left join secondary_in_input si on dc.id_qr_stocker = si.id_qr_stocker
                where
                    dc.tujuan = 'SECONDARY LUAR'
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
                    dc.tujuan = 'SECONDARY DALAM'
                    ".$additionalQuery."
                group by
                    m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('dc.secondary-in.secondary-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-in"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryIn(Request $request)
    {
        $additionalQuery = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and (si.tgl_trans >= '" . $request->dateFrom . "') ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and (si.tgl_trans <= '" . $request->dateTo . "') ";
        }

        $data_input = collect(DB::select("
                select
                    s.act_costing_ws, m.buyer,s.color,styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(si.qty_reject), 0) qty_reject, COALESCE(sum(si.qty_replace), 0) qty_replace, COALESCE(sum(si.qty_in), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace -  si.qty_in), 0) balance, dc.tujuan,dc.lokasi
                from
                    dc_in_input dc
                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws m on s.so_det_id = m.id_so_det
                    left join secondary_in_input si on dc.id_qr_stocker = si.id_qr_stocker
                where
                    dc.tujuan = 'SECONDARY LUAR'
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
                    dc.tujuan = 'SECONDARY DALAM'
                    ".$additionalQuery."
                group by
                    m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            ")
        );

        $act_costing_ws = $data_input->groupBy("act_costing_ws")->keys();
        $color = $data_input->groupBy("color")->keys();
        $buyer = $data_input->groupBy("buyer")->keys();
        $style = $data_input->groupBy("styleno")->keys();
        $lokasi = $data_input->groupBy("lokasi")->keys();

        return  array(
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "lokasi" => $lokasi
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
        $stocker = Stocker::where('id_qr_stocker', $request->txtqrstocker)->first();

        if ($stocker) {
            // Check Part Detail
            $partDetail = $stocker->partDetail;
            if ($partDetail) {

                // Check Part Detail Secondary
                $partDetailSecondary = $partDetail->secondaries;
                if ($partDetailSecondary) {
                    // If there ain't no urutan
                    if ($stocker->urutan == null) {
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
                                mp.nama_part,
                                if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
                                s.lokasi lokasi_tujuan,
                                s.tempat tempat_tujuan,
                                1 urutan,
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
                                    where("urutan", $currentPartDetailSecondary->secondary->urutan)->
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
                                                    mp.nama_part,
                                                    ".$multiSecondaryCurrentSecondary->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$multiSecondaryCurrentSecondary->urutan." as urutan
                                                from
                                                    stocker_input
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join form_cut_input a on s.form_cut_id = a.id
                                                    left join form_cut_reject b on s.form_reject_id = b.id
                                                    left join form_cut_piece c on s.form_piece_id = c.id
                                                    left join part_detail p on s.part_detail_id = p.id
                                                    left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                    left join master_part mp on p.master_part_id = mp.id
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY DALAM' and
                                                    pds.urutan = '".$multiSecondaryCurrentSecondary->urutan."'
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
                                                mp.nama_part,
                                                ".$multiSecondaryCurrentSecondary->qty_in." qty_awal,
                                                s.lokasi lokasi_tujuan,
                                                s.tempat tempat_tujuan,
                                                ".$multiSecondaryCurrentSecondary->urutan." as urutan
                                            from
                                                stocker_input s
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join form_cut_input a on s.form_cut_id = a.id
                                                left join form_cut_reject b on s.form_reject_id = b.id
                                                left join form_cut_piece c on s.form_piece_id = c.id
                                                left join part_detail p on s.part_detail_id = p.id
                                                left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                left join master_part mp on p.master_part_id = mp.id
                                                left join master_secondary ms on ms.id = pds.master_secondary_id
                                                left join marker_input mi on a.id_marker = mi.kode
                                                left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                            where
                                                s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                ms.tujuan = 'SECONDARY DALAM' and
                                                pds.urutan = '".$multiSecondaryCurrentSecondary->urutan."'
                                        ");

                                        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                    }
                                } else {
                                    return "Secondary Inhouse belum ada";
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
                                                        mp.nama_part,
                                                        ".$multiSecondaryBeforeSecondaryIn->qty_in." qty_awal,
                                                        s.lokasi lokasi_tujuan,
                                                        s.tempat tempat_tujuan,
                                                        ".$currentPartDetailSecondary->urutan." as urutan
                                                    from
                                                        stocker_input s
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join form_cut_input a on s.form_cut_id = a.id
                                                        left join form_cut_reject b on s.form_reject_id = b.id
                                                        left join form_cut_piece c on s.form_piece_id = c.id
                                                        left join part_detail p on s.part_detail_id = p.id
                                                        left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                        left join master_part mp on p.master_part_id = mp.id
                                                        left join master_secondary ms on ms.id = pds.master_secondary_id
                                                        left join marker_input mi on a.id_marker = mi.kode
                                                        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                    where
                                                        s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                        ms.tujuan = 'SECONDARY LUAR' and
                                                        pds.urutan = '".$currentPartDetailSecondary->urutan."'
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
                                                        mp.nama_part,
                                                        ".$multiSecondaryBeforeSecondary->qty_in." qty_awal,
                                                        s.lokasi lokasi_tujuan,
                                                        s.tempat tempat_tujuan,
                                                        ".$multiSecondaryBefore->urutan." as urutan
                                                    from
                                                        stocker_input s
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join form_cut_input a on s.form_cut_id = a.id
                                                        left join form_cut_reject b on s.form_reject_id = b.id
                                                        left join form_cut_piece c on s.form_piece_id = c.id
                                                        left join part_detail p on s.part_detail_id = p.id
                                                        left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                        left join master_part mp on p.master_part_id = mp.id
                                                        left join master_secondary ms on ms.id = pds.master_secondary_id
                                                        left join marker_input mi on a.id_marker = mi.kode
                                                        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                    where
                                                        s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                        ms.tujuan = 'SECONDARY DALAM' and
                                                        pds.urutan = '".$multiSecondaryBefore->urutan."'
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
                                                    mp.nama_part,
                                                    ".$multiSecondaryBeforeSecondaryIn->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$currentPartDetailSecondary->urutan." as urutan
                                                from
                                                    stocker_input s
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join form_cut_input a on s.form_cut_id = a.id
                                                    left join form_cut_reject b on s.form_reject_id = b.id
                                                    left join form_cut_piece c on s.form_piece_id = c.id
                                                    left join part_detail p on s.part_detail_id = p.id
                                                    left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                    left join master_part mp on p.master_part_id = mp.id
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY LUAR' and
                                                    pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                            ");

                                            return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                        } else {
                                            return "You should never got here, how could you.";
                                        }
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
                                            coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
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
                                        where
                                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                            and ifnull(si.id_qr_stocker,'x') = 'x'
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
                                                    mp.nama_part,
                                                    ".$multiSecondaryBeforeSecondary->qty_in." qty_awal,
                                                    s.lokasi lokasi_tujuan,
                                                    s.tempat tempat_tujuan,
                                                    ".$multiSecondaryBefore->urutan." as urutan
                                                from
                                                    stocker_input s
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join form_cut_input a on s.form_cut_id = a.id
                                                    left join form_cut_reject b on s.form_reject_id = b.id
                                                    left join form_cut_piece c on s.form_piece_id = c.id
                                                    left join part_detail p on s.part_detail_id = p.id
                                                    left join part_detail_secondary pds on pds.part_detail_id = p.id
                                                    left join master_part mp on p.master_part_id = mp.id
                                                    left join master_secondary ms on ms.id = pds.master_secondary_id
                                                    left join marker_input mi on a.id_marker = mi.kode
                                                    left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
                                                    left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
                                                where
                                                    s.id_qr_stocker = '" . $request->txtqrstocker . "' and
                                                    ms.tujuan = 'SECONDARY DALAM' and
                                                    pds.urutan = '".$multiSecondaryBefore->urutan."'
                                            ");

                                            return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                        } else {
                                            return "Data Secondary In sudah ada";
                                        }
                                    } else {
                                        return "Data Secondary Dalam belum ada";
                                    }
                                } else {
                                    // Check the secondary in data
                                    return "when there is no step after and the step before was secondary in then you could not be able to scan the secondary in again, I mean you got yourself here from secondary in already.";
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
                                        coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
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
                                    where
                                        dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                        and ifnull(si.id_qr_stocker,'x') = 'x'
                                ");

                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }
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
                            coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
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
            "txtqtyreject" => "required"
        ]);

        $lastStep = Stocker::selectRaw("MAX(part_detail_secondary.urutan) as urutan")->
            leftJoin("part_detail_secondary", function ($join) {
                $join->on("part_detail_secondary.part_detail_id", "=", "stocker_input.part_detail_id");
                $join->on("part_detail_secondary.urutan", "=", "stocker_input.urutan");
            })->
            where("stocker_input.id_qr_stocker", $request['txtno_stocker'])->
            groupBy("stocker_input.id")->
            value("urutan");

        // Update Rak/Trolley (One Step Before Loading) On Last Step/No Step at all
        if (!$lastStep || $lastStep == $request->txturutan) {
            if ($request['cborak']) {
                $rak = DB::table('rack_detail')
                ->select('id')
                ->where('nama_detail_rak', '=', $request['cborak'])
                ->get();
                $rak_data = $rak ? $rak[0]->id : null;

                $insert_rak = RackDetailStocker::create([
                    'nm_rak' => $request['cborak'],
                    'detail_rack_id' => $rak_data,
                    'stocker_id' => $request['txtno_stocker'],
                    'qty_in' => $request['txtqtyin'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            if ($request['cbotrolley']) {
                $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                $trolleyStockArr = [];

                $thisStocker = Stocker::whereRaw("id_qr_stocker = '" . $request['txtno_stocker'] . "'")->first();
                $thisTrolley = Trolley::where("nama_trolley", $request['cbotrolley'])->first();
                if ($thisTrolley && $thisStocker) {
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

                    $thisStocker->status = "trolley";
                    $thisStocker->latest_alokasi = Carbon::now();
                    $thisStocker->save();
                }
            }
        }

        $savein = SecondaryIn::updateOrCreate(
            ['id_qr_stocker' => $request['txtno_stocker'], 'urutan' => $request->txturutan],
            [
                'tgl_trans' => $tgltrans,
                'qty_awal' => $request['txtqtyawal'],
                'qty_reject' => $request['txtqtyreject'],
                'qty_replace' => $request['txtqtyreplace'],
                'qty_in' => $request['txtqtyawal'] - $request['txtqtyreject'] + $request['txtqtyreplace'],
                'user' => Auth::user()->name,
                'ket' => $request['txtket'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::update(
            "update stocker_input set status = 'non secondary' ".($request->txturutan ? ", urutan = '".($request->txturutan + 1)."' " : "")." where id_qr_stocker = '" . $request->txtno_stocker . "'"
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
            $cekdata =  DB::select("
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
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                if ($d->tempat_tujuan == 'TROLLEY') {
                    $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                    $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                    $trolleyStockArr = [];

                    $thisStocker = Stocker::whereRaw("id_qr_stocker = '" . $d->id_qr_stocker . "'")->first();
                    $thisTrolley = Trolley::where("nama_trolley", $d->lokasi_tujuan)->first();
                    if ($thisTrolley && $thisStocker) {
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

                        $thisStocker->status = "trolley";
                        $thisStocker->latest_alokasi = Carbon::now();
                        $thisStocker->save();
                    }
                }

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

    public function update(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

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

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportSecondaryIn($request->from, $request->to), 'Laporan sec in '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    public function exportExcelDetail(Request $request)
    {
        return Excel::download(new ExportSecondaryInDetail($request->from, $request->to), 'Laporan sec in detail '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
