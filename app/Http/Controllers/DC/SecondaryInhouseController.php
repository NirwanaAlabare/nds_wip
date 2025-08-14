<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dc\SecondaryInhouse;
use App\Exports\DC\ExportSecondaryInHouse;
use App\Exports\DC\ExportSecondaryInHouseDetail;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;

class SecondaryInhouseController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
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
                    $request->search['value'] .
                    "%'
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
            if ($request->size_filter && count($request->size_filter) > 0) {
                $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->size_filter)).")";
            }

            $data_input = DB::select("
                SELECT a.*,
                (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                a.qty_awal,
                a.qty_reject,
                a.qty_replace,
                a.qty_in,
                a.created_at,
                dc.tujuan,
                dc.lokasi,
                dc.tempat,
                COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                COALESCE(msb.size, s.size) size,
                a.user,
                mp.nama_part,
                CONCAT(s.range_awal, ' - ', s.range_akhir, (CASE WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ') ELSE ' (0)' END)) stocker_range
                from secondary_inhouse_input a
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join form_cut_piece fp on fp.id = s.form_piece_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on pd.part_id = p.id
                left join master_part mp on mp.id = pd.master_part_id
                left join (select id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat from dc_in_input) dc on a.id_qr_stocker = dc.id_qr_stocker
                where
                a.tgl_trans is not null
                ".$additionalQuery."
                order by a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryInhouse(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
        }

        $data_input = collect(DB::select("
            SELECT a.*,
            (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            a.tgl_trans,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            a.qty_awal,
            a.qty_reject,
            a.qty_replace,
            a.qty_in,
            a.created_at,
            dc.tujuan,
            dc.lokasi,
            dc.tempat,
            COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
            COALESCE(msb.size, s.size) size,
            a.user,
            mp.nama_part,
            CONCAT(s.range_awal, ' - ', s.range_akhir, (CASE WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ') ELSE ' (0)' END)) stocker_range
            from secondary_inhouse_input a
            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on fr.id = s.form_reject_id
            left join form_cut_piece fp on fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on pd.part_id = p.id
            left join master_part mp on mp.id = pd.master_part_id
            left join (select id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat from dc_in_input) dc on a.id_qr_stocker = dc.id_qr_stocker
            where
            a.tgl_trans is not null
            ".$additionalQuery."
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

    public function detail_stocker_inhouse(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and (sii.tgl_trans >= '" . $request->dateFrom . "') ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (sii.tgl_trans <= '" . $request->dateTo . "') ";
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

            $data_detail = DB::select("
                select
                    s.act_costing_ws, m.buyer,s.color,  styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(sii.qty_reject), 0) qty_reject, COALESCE(sum(sii.qty_replace), 0) qty_replace, COALESCE(sum(sii.qty_in), 0) qty_out, COALESCE((sum(sii.qty_in) - sum(dc.qty_awal - dc.qty_reject + dc.qty_replace)), 0) balance, dc.lokasi
                from
                    dc_in_input dc
                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws m on s.so_det_id = m.id_so_det
                    left join secondary_inhouse_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                where
                    dc.tujuan = 'SECONDARY DALAM' ".$additionalQuery."
                group by
                    m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            ");

            return DataTables::of($data_detail)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryInhouse(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and (sii.tgl_trans >= '" . $request->dateFrom . "') ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and (sii.tgl_trans <= '" . $request->dateTo . "') ";
        }

        $data_detail = collect(DB::select("
            select
                s.act_costing_ws, m.buyer,s.color,  styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(sii.qty_reject), 0) qty_reject, COALESCE(sum(sii.qty_replace), 0) qty_replace, COALESCE(sum(sii.qty_in), 0) qty_out, COALESCE((sum(sii.qty_in) - sum(dc.qty_awal - dc.qty_reject + dc.qty_replace)), 0) balance, dc.lokasi
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws m on s.so_det_id = m.id_so_det
                left join secondary_inhouse_input sii on dc.id_qr_stocker = sii.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY DALAM' ".$additionalQuery."
            group by
                m.ws,m.buyer,m.styleno,m.color,dc.lokasi
        "));

        $act_costing_ws = $data_detail->groupBy("act_costing_ws")->keys();
        $color = $data_detail->groupBy("color")->keys();
        $buyer = $data_detail->groupBy("buyer")->keys();
        $style = $data_detail->groupBy("styleno")->keys();
        $lokasi = $data_detail->groupBy("lokasi")->keys();

        return  array(
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "lokasi" => $lokasi
        );
    }

    public function cek_data_stocker_inhouse(Request $request)
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
        coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
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
        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
        where dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
        and ifnull(si.id_qr_stocker,'x') = 'x'
        ");
        return json_encode($cekdata[0]);
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

        $saveinhouse = SecondaryInhouse::create([
            'tgl_trans' => $tgltrans,
            'id_qr_stocker' => $request['txtno_stocker'],
            'qty_awal' => $request['txtqtyawal'],
            'qty_reject' => $request['txtqtyreject'],
            'qty_replace' => $request['txtqtyreplace'],
            'qty_in' => $request['txtqtyawal'] - $request['txtqtyreject'] + $request['txtqtyreplace'],
            'user' => Auth::user()->name,
            'ket' => $request['txtket'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

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
                    LEFT JOIN secondary_inhouse_input si ON dc.id_qr_stocker = si.id_qr_stocker
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
        return Excel::download(new ExportSecondaryInHouse($request->from, $request->to), 'Laporan sec inhouse '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    public function exportExcelDetail(Request $request)
    {
        return Excel::download(new ExportSecondaryInHouseDetail($request->from, $request->to), 'Laporan sec inhouse detail '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }
}
