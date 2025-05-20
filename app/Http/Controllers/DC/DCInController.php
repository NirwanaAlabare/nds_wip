<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SecondaryInhouse;
use App\Models\Stocker;
use App\Models\Trolley;
use App\Models\TrolleyStocker;

use App\Exports\DC\ExportDcIn;
use App\Exports\DC\ExportDcInDetail;

use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;

class DCInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
            }

            if ($request->dc_filter_tipe && count($request->dc_filter_tipe) > 0) {
                $additionalQuery .= " and (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) in (".addQuotesAround(implode("\n", $request->dc_filter_tipe)).")";
            }
            if ($request->dc_filter_buyer && count($request->dc_filter_buyer) > 0) {
                $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->dc_filter_buyer)).")";
            }
            if ($request->dc_filter_ws && count($request->dc_filter_ws) > 0) {
                $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->dc_filter_ws)).")";
            }
            if ($request->dc_filter_style && count($request->dc_filter_style) > 0) {
                $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->dc_filter_style)).")";
            }
            if ($request->dc_filter_color && count($request->dc_filter_color) > 0) {
                $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->dc_filter_color)).")";
            }
            if ($request->dc_filter_part && count($request->dc_filter_part) > 0) {
                $additionalQuery .= " and mp.nama_part in (".addQuotesAround(implode("\n", $request->dc_filter_part)).")";
            }
            if ($request->dc_filter_size && count($request->dc_filter_size) > 0) {
                $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->dc_filter_size)).")";
            }
            if ($request->size_filter && count($request->size_filter) > 0) {
                $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->size_filter)).")";
            }
            if ($request->dc_filter_no_cut && count($request->dc_filter_no_cut) > 0) {
                $additionalQuery .= " and f.no_cut in (".addQuotesAround(implode("\n", $request->dc_filter_no_cut)).")";
            }
            if ($request->dc_filter_tujuan && count($request->dc_filter_tujuan) > 0) {
                $additionalQuery .= " and a.tujuan in (".addQuotesAround(implode("\n", $request->dc_filter_tujuan)).")";
            }
            if ($request->dc_filter_tempat && count($request->dc_filter_tempat) > 0) {
                $additionalQuery .= " and a.tempat in (".addQuotesAround(implode("\n", $request->dc_filter_tempat)).")";
            }
            if ($request->dc_filter_lokasi && count($request->dc_filter_lokasi) > 0) {
                $additionalQuery .= " and a.lokasi in (".addQuotesAround(implode("\n", $request->dc_filter_lokasi)).")";
            }

            $data_input = DB::select("
                SELECT
                    UPPER(a.id_qr_stocker) id_qr_stocker,
                    (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                    DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                    a.tgl_trans,
                    s.act_costing_ws,
                    s.color,
                    p.buyer,
                    p.style,
                    a.qty_awal,
                    a.qty_reject,
                    a.qty_replace,
                    (a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
                    a.tujuan,
                    a.lokasi,
                    a.tempat,
                    a.created_at,
                    a.user,
                    f.no_cut,
                    COALESCE(msb.size, s.size) size,
                    mp.nama_part
                from
                    dc_in_input a
                    left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                    left join form_cut_input f on f.id = s.form_cut_id
                    left join form_cut_reject fr on fr.id = s.form_reject_id
                    left join part_detail pd on s.part_detail_id = pd.id
                    left join part p on pd.part_id = p.id
                    left join master_part mp on mp.id = pd.master_part_id
                where
                    a.tgl_trans is not null
                    ".$additionalQuery."
                order by
                    a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('dc.dc-in.dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filter_dc_in(Request $request) {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
        }

        $data_input = collect(DB::select("
            SELECT
                UPPER(a.id_qr_stocker) id_qr_stocker,
                (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                a.qty_awal,
                a.qty_reject,
                a.qty_replace,
                (a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
                a.tujuan,
                a.lokasi,
                a.tempat,
                a.created_at,
                a.user,
                f.no_cut,
                COALESCE(msb.size, s.size) size,
                mp.nama_part
            from
                dc_in_input a
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on pd.part_id = p.id
                left join master_part mp on mp.id = pd.master_part_id
            where
                a.tgl_trans is not null
                ".$additionalQuery."
            order by
                a.tgl_trans desc
        "));

        $tipe = $data_input->groupBy("tipe")->keys();
        $act_costing_ws = $data_input->groupBy("act_costing_ws")->keys();
        $color = $data_input->groupBy("color")->keys();
        $buyer = $data_input->groupBy("buyer")->keys();
        $style = $data_input->groupBy("style")->keys();
        $tujuan = $data_input->groupBy("tujuan")->keys();
        $lokasi = $data_input->groupBy("lokasi")->keys();
        $tempat = $data_input->groupBy("tempat")->keys();
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
            "tempat" => $tempat,
            "part" => $part,
            "no_cut" => $no_cut,
            "size" => $size
        );
    }

    public function total_dc_in(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
        }

        if ($request->tgl_trans) {
            $additionalQuery .= " and a.tgl_trans LIKE '%".$request->tgl_trans."%'";
        }

        if ($request->id_qr) {
            $additionalQuery .= " and a.id_qr_stocker LIKE '%".$request->id_qr."%'";
        }

        if ($request->ws) {
            $additionalQuery .= " and msb.ws LIKE '%".$request->ws."%'";
        }

        if ($request->style) {
            $additionalQuery .= " and msb.styleno LIKE '%".$request->style."%'";
        }

        if ($request->color) {
            $additionalQuery .= " and msb.color LIKE '%".$request->color."%'";
        }

        if ($request->part) {
            $additionalQuery .= " and mp.nama_part LIKE '%".$request->part."%'";
        }

        // if ($request->size) {
        //     $additionalQuery .= " and msb.size LIKE '%".$request->size."%'";
        // }
        if ($request->size_filter && count($request->size_filter) > 0) {
            $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->size_filter)).")";
        }

        if ($request->no_cut) {
            $additionalQuery .= " and f.no_cut LIKE '%".$request->no_cut."%'";
        }

        if ($request->tujuan) {
            $additionalQuery .= " and a.tujuan LIKE '%".$request->tujuan."%'";
        }

        if ($request->tempat) {
            $additionalQuery .= " and a.tempat LIKE '%".$request->tempat."%'";
        }

        if ($request->lokasi) {
            $additionalQuery .= " and a.lokasi LIKE '%".$request->lokasi."%'";
        }

        if ($request->qty_awal) {
            $additionalQuery .= " and a.qty_awal LIKE '%".$request->qty_awal."%'";
        }

        if ($request->qty_reject) {
            $additionalQuery .= " and a.qty_awal LIKE '%".$request->qty_reject."%'";
        }

        if ($request->qty_replace) {
            $additionalQuery .= " and a.qty_replace LIKE '%".$request->qty_replace."%'";
        }

        if ($request->qty_in) {
            $additionalQuery .= " and (a.qty_awal - a.qty_reject + a.qty_replace) LIKE '%".$request->qty_in."%'";
        }

        if ($request->buyer) {
            $additionalQuery .= " and msb.buyer LIKE '%".$request->buyer."%'";
        }

        if ($request->user) {
            $additionalQuery .= " and a.user LIKE '%".$request->user."%'";
        }

        if ($request->dc_filter_tipe && count($request->dc_filter_tipe) > 0) {
            $additionalQuery .= " and (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) in (".addQuotesAround(implode("\n", $request->dc_filter_tipe)).")";
        }
        if ($request->dc_filter_buyer && count($request->dc_filter_buyer) > 0) {
            $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->dc_filter_buyer)).")";
        }
        if ($request->dc_filter_ws && count($request->dc_filter_ws) > 0) {
            $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->dc_filter_ws)).")";
        }
        if ($request->dc_filter_style && count($request->dc_filter_style) > 0) {
            $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->dc_filter_style)).")";
        }
        if ($request->dc_filter_color && count($request->dc_filter_color) > 0) {
            $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->dc_filter_color)).")";
        }
        if ($request->dc_filter_part && count($request->dc_filter_part) > 0) {
            $additionalQuery .= " and mp.nama_part in (".addQuotesAround(implode("\n", $request->dc_filter_part)).")";
        }
        if ($request->dc_filter_size && count($request->dc_filter_size) > 0) {
            $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->dc_filter_size)).")";
        }
        if ($request->dc_filter_no_cut && count($request->dc_filter_no_cut) > 0) {
            $additionalQuery .= " and f.no_cut in (".addQuotesAround(implode("\n", $request->dc_filter_no_cut)).")";
        }
        if ($request->dc_filter_tujuan && count($request->dc_filter_tujuan) > 0) {
            $additionalQuery .= " and a.tujuan in (".addQuotesAround(implode("\n", $request->dc_filter_tujuan)).")";
        }
        if ($request->dc_filter_tempat && count($request->dc_filter_tempat) > 0) {
            $additionalQuery .= " and a.tempat in (".addQuotesAround(implode("\n", $request->dc_filter_tempat)).")";
        }
        if ($request->dc_filter_lokasi && count($request->dc_filter_lokasi) > 0) {
            $additionalQuery .= " and a.lokasi in (".addQuotesAround(implode("\n", $request->dc_filter_lokasi)).")";
        }

        $data_input = DB::select("
            SELECT
                SUM(a.qty_awal) qty_awal,
                SUM(a.qty_reject) qty_reject,
                SUM(a.qty_replace) qty_replace,
                SUM(a.qty_awal - a.qty_reject + a.qty_replace) qty_in
            from
                dc_in_input a
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on pd.part_id = p.id
                left join master_part mp on mp.id = pd.master_part_id
            where
                a.tgl_trans is not null
                ".$additionalQuery."
        ");

        // dd("
        //     SELECT
        //         SUM(a.qty_awal) qty_awal,
        //         SUM(a.qty_reject) qty_reject,
        //         SUM(a.qty_replace) qty_replace,
        //         SUM(a.qty_awal - a.qty_reject + a.qty_replace) qty_in
        //     from
        //         dc_in_input a
        //         left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
        //         left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        //         left join form_cut_input f on f.id = s.form_cut_id
        //         left join part_detail pd on s.part_detail_id = pd.id
        //         left join part p on pd.part_id = p.id
        //         left join master_part mp on mp.id = pd.master_part_id
        //         ".$additionalQuery."
        // ");

        return $data_input;
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportDcIn($request->from, $request->to), 'Laporan dc in '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    public function detail_dc_in(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and (dc.tgl_trans >= '" . $request->dateFrom . "') ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (dc.tgl_trans <= '" . $request->dateTo . "') ";
            }

            if ($request->detail_dc_filter_buyer && count($request->detail_dc_filter_buyer) > 0) {
                $additionalQuery .= " and m.buyer in (".addQuotesAround(implode("\n", $request->detail_dc_filter_buyer)).")";
            }
            if ($request->detail_dc_filter_ws && count($request->detail_dc_filter_ws) > 0) {
                $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->detail_dc_filter_ws)).")";
            }
            if ($request->detail_dc_filter_style && count($request->detail_dc_filter_style) > 0) {
                $additionalQuery .= " and styleno in (".addQuotesAround(implode("\n", $request->detail_dc_filter_style)).")";
            }
            if ($request->detail_dc_filter_color && count($request->detail_dc_filter_color) > 0) {
                $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->detail_dc_filter_color)).")";
            }
            if ($request->detail_dc_filter_lokasi && count($request->detail_dc_filter_lokasi) > 0) {
                $additionalQuery .= " and dc.lokasi in (".addQuotesAround(implode("\n", $request->detail_dc_filter_lokasi)).")";
            }

            $data_detail = DB::select("
                select
                    s.act_costing_ws, m.buyer, s.color, styleno, COALESCE(sum(dc.qty_awal), 0) qty_in, COALESCE(sum(dc.qty_reject), 0) qty_reject, COALESCE(sum(dc.qty_replace), 0) qty_replace, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) balance, dc.lokasi
                from
                    dc_in_input dc
                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                    left join master_sb_ws m on s.so_det_id = m.id_so_det
                where
                    dc.tgl_trans is not null
                    ".$additionalQuery."
                group by
                    m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            ");

            return DataTables::of($data_detail)->toJson();
        }

        return view('dc.dc-in.dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filter_detail_dc_in(Request $request) {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and (dc.tgl_trans >= '" . $request->dateFrom . "') ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and (dc.tgl_trans <= '" . $request->dateTo . "') ";
        }

        $data_detail = collect(DB::select("
            select
                s.act_costing_ws, m.buyer, s.color, styleno, COALESCE(sum(dc.qty_awal), 0) qty_in, COALESCE(sum(dc.qty_reject), 0) qty_reject, COALESCE(sum(dc.qty_replace), 0) qty_replace, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) balance, dc.lokasi
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws m on s.so_det_id = m.id_so_det
            where
                dc.tgl_trans is not null
                ".$additionalQuery."
            group by
                m.ws,m.buyer,m.styleno,m.color,dc.lokasi
        "));

        $act_costing_ws = $data_detail->groupBy("act_costing_ws")->keys();
        $color = $data_detail->groupBy("color")->keys();
        $buyer = $data_detail->groupBy("buyer")->keys();
        $style = $data_detail->groupBy("styleno")->keys();
        $lokasi = $data_detail->groupBy("lokasi")->keys();

        return array(
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "lokasi" => $lokasi,
        );
    }

    public function exportExcelDetail(Request $request)
    {
        return Excel::download(new ExportDcInDetail($request->from, $request->to), 'Laporan dc in detail '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    public function show_data_header(Request $request)
    {
        $data_header = DB::select("
            SELECT
                a.act_costing_ws,
                COALESCE(msb.buyer, m.buyer, fr.buyer) buyer,
                COALESCE(msb.styleno, m.style, fr.style) styleno,
                a.color,
                COALESCE(msb.size, a.size) size,
                a.panel,
                f.no_cut,
                f.id,
                a.shade,
                a.qty_ply,
                a.range_awal,
                a.range_akhir,
                concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode,
                ms.tujuan,
                IF(ms.tujuan = 'NON SECONDARY',a.lokasi,ms.proses) lokasi,
                a.tempat
            FROM
                `stocker_input` a
                left join master_sb_ws msb on msb.id_so_det = a.so_det_id
                left join form_cut_input f on a.form_cut_id = f.id
                left join form_cut_reject fr on a.form_reject_id = fr.id
                left JOIN marker_input m ON m.kode = f.id_marker
                left join part_detail pd on a.part_detail_id = pd.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
            WHERE
                a.id_qr_stocker = '$request->txtqrstocker'
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }

    public function get_tempat(Request $request)
    {
        $tujuan = $request->tujuan;
        if ($tujuan == 'NON SECONDARY') {
            $data_tempat = DB::select("select 'RAK' isi, 'RAK' tampil
            union
            select 'TROLLEY', 'TROLLEY'");
            $html = "<option value=''>Pilih Tempat</option>";
            foreach ($data_tempat as $datatempat) {
                $html .= " <option value='" . $datatempat->tampil . "'>" . $datatempat->tampil . "</option> ";
            }
        } else {
            $data_tempat = DB::select("select '-' isi, '-' tampil");
            $html = "<option value = '-' selected> - </option>";
        }

        return $html;
    }


    public function get_lokasi(Request $request)
    {
        $tujuan = $request->tujuan;
        $tempat = $request->tempat;
        if ($tujuan == 'NON SECONDARY' && $tempat == 'RAK') {
            $data_alokasi = DB::select("select kode isi, nama_detail_rak tampil from rack_detail");
            $html = "<option value=''>Pilih Rak</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else if ($tujuan == 'NON SECONDARY' && $tempat == 'TROLLEY') {
            $data_alokasi = DB::select("select kode isi, nama_trolley tampil from trolley");
            $html = "<option value=''>Pilih Trolley</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else {
            $data_alokasi = DB::select("select proses isi, proses tampil from master_secondary where tujuan = '$tujuan'");
            $html = "<option value=''>Pilih Lokasi</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        }

        return $html;
    }

    public function create(Request $request)
    {
        return view('dc.dc-in.create-dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in"]);
    }

    public function get_tmp_dc_in(Request $request)
    {
        $user = Auth::user()->name;

        // $tmpDcIn = DB::select("
        //     select
        //         ms.id_qr_stocker,
        //         mp.nama_part,
        //         concat(ms.id_qr_stocker,' - ',mp.nama_part) kode_stocker,
        //         ifnull(s.tujuan,'-') tujuan,
        //         ifnull(tmp.tempat,'-') tempat,
        //         ifnull(tmp.lokasi,'-') lokasi,
        //         concat(coalesce(ms.qty_ply_mod, ms.qty_ply) - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0), concat(' (', (coalesce(tmp.qty_replace,0) - coalesce(tmp.qty_reject,0)), ')')) qty_in,
        //         ms.act_costing_ws,
        //         ms.size,
        //         ms.color,
        //         ms.panel,
        //         concat(ms.range_awal, '-', ms.range_akhir) rangeAwalAkhir,
        //         ifnull(tmp.id_qr_stocker,'x') cek_stat
        //     from
        //         (
        //             select
        //                 *,
        //                 concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode
        //             from
        //                 stocker_input
        //         ) ms
        //         left join
        //             (
        //                 select
        //                     concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode
        //                 from
        //                     tmp_dc_in_input_new x
        //                     left join stocker_input y on x.id_qr_stocker = y.id_qr_stocker
        //                 where
        //                     user = '$user'
        //                 group by
        //                     concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)
        //             )
        //         a on ms.kode = a.kode
        //         left join part_detail pd on ms.part_detail_id = pd.id
        //         left join master_part mp  on pd.master_part_id = mp.id
        //         left join master_secondary s on pd.master_secondary_id = s.id
        //         left join tmp_dc_in_input_new tmp on ms.id_qr_stocker = tmp.id_qr_stocker
        //     order by
        //         ifnull(tmp.id_qr_stocker,'x') asc
        // ");

        $tmpDcIn = DB::select("
            SELECT
                ms.id_qr_stocker,
                mp.nama_part,
                concat( ms.id_qr_stocker, ' - ', mp.nama_part ) kode_stocker,
                ifnull( s.tujuan, '-' ) tujuan,
                ifnull( tmp.tempat, '-' ) tempat,
                ifnull( tmp.lokasi, '-' ) lokasi,
                concat(COALESCE ( ms.qty_ply_mod, ms.qty_ply ) - COALESCE ( tmp.qty_reject, 0 ) + COALESCE ( tmp.qty_replace, 0 ),
                concat( ' (', ( COALESCE ( tmp.qty_replace, 0 ) - COALESCE ( tmp.qty_reject, 0 )), ')' )) qty_in,
                ms.act_costing_ws,
                msb.size,
                ms.color,
                ms.panel,
                concat( ms.range_awal, '-', ms.range_akhir ) rangeAwalAkhir,
                ifnull( tmp.id_qr_stocker, 'x' ) cek_stat
            FROM
                tmp_dc_in_input_new x
                left JOIN stocker_input y ON x.id_qr_stocker = y.id_qr_stocker
                LEFT JOIN stocker_input ms ON ms.form_cut_id = y.form_cut_id AND ms.so_det_id = y.so_det_id AND ms.group_stocker = y.group_stocker AND ms.ratio = y.ratio
                LEFT JOIN master_sb_ws msb ON msb.id_so_det = ms.so_det_id
                LEFT JOIN tmp_dc_in_input_new tmp ON tmp.id_qr_stocker = ms.id_qr_stocker
                left JOIN part_detail pd ON ms.part_detail_id = pd.id
                left JOIN master_part mp ON pd.master_part_id = mp.id
                LEFT JOIN master_secondary s ON pd.master_secondary_id = s.id
            WHERE
                x.`user` = '".$user."' and
                y.id is not null and
                ms.id is not null and
                y.form_reject_id is null
            group by
                ms.id_qr_stocker
            UNION
            SELECT
                ms.id_qr_stocker,
                mp.nama_part,
                concat( ms.id_qr_stocker, ' - ', mp.nama_part ) kode_stocker,
                ifnull( s.tujuan, '-' ) tujuan,
                ifnull( tmp.tempat, '-' ) tempat,
                ifnull( tmp.lokasi, '-' ) lokasi,
                concat(COALESCE ( ms.qty_ply_mod, ms.qty_ply ) - COALESCE ( tmp.qty_reject, 0 ) + COALESCE ( tmp.qty_replace, 0 ),
                concat( ' (', ( COALESCE ( tmp.qty_replace, 0 ) - COALESCE ( tmp.qty_reject, 0 )), ')' )) qty_in,
                ms.act_costing_ws,
                msb.size,
                ms.color,
                ms.panel,
                concat( ms.range_awal, '-', ms.range_akhir ) rangeAwalAkhir,
                ifnull( tmp.id_qr_stocker, 'x' ) cek_stat
            FROM
                tmp_dc_in_input_new x
                left JOIN stocker_input y ON x.id_qr_stocker = y.id_qr_stocker
                LEFT JOIN stocker_input ms ON ms.form_reject_id = y.form_reject_id AND ms.so_det_id = y.so_det_id AND ms.shade = y.shade
                LEFT JOIN master_sb_ws msb ON msb.id_so_det = ms.so_det_id
                LEFT JOIN tmp_dc_in_input_new tmp ON tmp.id_qr_stocker = ms.id_qr_stocker
                left JOIN part_detail pd ON ms.part_detail_id = pd.id
                left JOIN master_part mp ON pd.master_part_id = mp.id
                LEFT JOIN master_secondary s ON pd.master_secondary_id = s.id
            WHERE
                x.`user` = '".$user."' and
                y.id is not null and
                ms.id is not null and
                y.form_reject_id is not null
            group by
                ms.id_qr_stocker
        ");

        return DataTables::of($tmpDcIn)->toJson();
    }

    public function insert_tmp_dc_in(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->txttuj_h == 'NON SECONDARY') {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = $request->txttempat_h;
        } else {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = '-';
        }

        $cekdata =  DB::select("
            select
                *
            from
                tmp_dc_in_input_new
                left join dc_in_input on dc_in_input.id_qr_stocker = tmp_dc_in_input_new.id_qr_stocker
            where
                tmp_dc_in_input_new.id_qr_stocker = '" . $request->txtqrstocker . "'
        ");

        $cekdata_fix = $cekdata ? $cekdata[0] : null;
        if ($cekdata_fix ==  null) {

            $cekdata_fix = $cekdata ? $cekdata[0] : null;
            if ($cekdata_fix ==  null) {

                DB::insert("
                    insert into tmp_dc_in_input_new
                    (
                        id_qr_stocker,
                        qty_reject,
                        qty_replace,
                        tujuan,
                        tempat,
                        lokasi,
                        user
                    )
                    values
                    (
                        '" . $request->txtqrstocker . "',
                        '0',
                        '0',
                        '$tujuan',
                        '$tempat',
                        '$lokasi',
                        '$user'
                    )
                ");

                DB::update(
                    "update stocker_input set status = 'dc' where id_qr_stocker = '" . $request->txtqrstocker . "'"
                );
            }
        }
    }

    // mass insert tmp dc in
    public function mass_insert_tmp_dc_in(Request $request)
    {
        $thisStocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.act_costing_ws, stocker_input.color, form_cut_input.no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            where("id_qr_stocker", $request->txtqrstocker)->
            first();

        if ($thisStocker) {
            $data_header = DB::select("
                SELECT
                    a.act_costing_ws,
                    m.buyer,
                    m.style styleno,
                    a.color,
                    COALESCE(msb.size, a.size) size,
                    a.panel,
                    f.no_cut,
                    f.id,
                    a.shade,
                    a.qty_ply,
                    a.range_awal,
                    a.range_akhir,
                    concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode,
                    ms.tujuan,
                    IF(ms.tujuan = 'NON SECONDARY',a.lokasi,ms.proses) lokasi,
                    a.tempat,
                    a.id_qr_stocker
                FROM
                    `stocker_input` a
                    left join master_sb_ws msb on msb.id_so_det = a.so_det_id
                    left join form_cut_input f on a.form_cut_id = f.id
                    left JOIN marker_input m ON m.kode = f.id_marker
                    left join part_detail pd on a.part_detail_id = pd.id
                    left join master_secondary ms on pd.master_secondary_id = ms.id
                WHERE
                    a.act_costing_ws = '".$thisStocker->act_costing_ws."' AND
                    a.color = '".$thisStocker->color."' AND
                    f.no_cut = '".$thisStocker->no_cut."'
            ");

            $user = Auth::user()->name;
            foreach ($data_header as $d) {
                if ($d->tujuan == 'NON SECONDARY') {
                    $tujuan = $d->tujuan;
                    $lokasi = $d->lokasi;
                    $tempat = $d->tempat;
                } else {
                    $tujuan = $d->tujuan;
                    $lokasi = $d->lokasi;
                    $tempat = '-';
                }

                $cekdata =  DB::select("
                    select
                        *
                    from
                        tmp_dc_in_input_new
                        left join dc_in_input on dc_in_input.id_qr_stocker = tmp_dc_in_input_new.id_qr_stocker
                    where
                        tmp_dc_in_input_new.id_qr_stocker = '" . $d->id_qr_stocker . "'
                ");

                $cekdata_fix = $cekdata ? $cekdata[0] : null;
                if ($cekdata_fix ==  null) {

                    $cekdata_fix = $cekdata ? $cekdata[0] : null;
                    if ($cekdata_fix ==  null) {

                        DB::insert("
                            insert into tmp_dc_in_input_new
                            (
                                id_qr_stocker,
                                qty_reject,
                                qty_replace,
                                tujuan,
                                tempat,
                                lokasi,
                                user
                            )
                            values
                            (
                                '" . $d->id_qr_stocker . "',
                                '0',
                                '0',
                                '$tujuan',
                                '$tempat',
                                '$lokasi',
                                '$user'
                            )
                        ");

                        DB::update(
                            "update stocker_input set status = 'dc' where id_qr_stocker = '" . $d->id_qr_stocker . "'"
                        );
                    }
                }
            }

            return array(
                'status' => 200,
                'message' => 'Data Stocker berhasil disimpan',
                'redirect' => '',
                'table' => 'datatable-scan',
                'additional' => [],
                'callback' => 'resetCheckedStocker()'
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data Stocker gagal disimpan',
            'redirect' => '',
            'table' => 'datatable-scan',
            'additional' => [],
            'callback' => 'resetCheckedStocker()'
        );
    }

    public function show_tmp_dc_in(Request $request)
    {
        $data_tmp_dc_in = DB::select("
            SELECT
                s.id_qr_stocker,
                (case when s.qty_ply_mod > 0 THEN s.qty_ply_mod ELSE s.qty_ply END) - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0) qty_in,
                tmp.qty_reject,
                tmp.qty_replace,
                ms.tujuan,
                ms.proses,
                tmp.tempat,
                tmp.lokasi,
                tmp.ket,
                concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode
            from
                stocker_input s
                left join part_detail pd on s.part_detail_id = pd.id
                left join master_part mp  on pd.master_part_id = mp.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
                left join tmp_dc_in_input_new tmp on s.id_qr_stocker = tmp.id_qr_stocker
            where
                s.id_qr_stocker= '$request->id_c'
        ");

        return json_encode($data_tmp_dc_in[0]);
    }

    public function update_tmp_dc_in(Request $request)
    {
        if ($request->txttuj == 'NON SECONDARY') {
            $update_stocker_input = DB::update("
                update
                    stocker_input
                set
                    tempat = '" . $request->cbotempat . "',
                    tujuan = '" . $request->txttuj . "',
                    lokasi = '" . $request->cbolokasi . "'
                where
                    concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'
            ");

            // // Trolley Things
                // if ($request->cbotempat == "TROLLEY") {
                //     $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                //     $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                //     $stockerData = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'")->get();

                //     $trolleyStockArr = [];

                //     $thisTrolley = Trolley::where("nama_trolley", $request->cbolokasi)->first();
                //     if ($thisTrolley) {

                //         $i = 0;
                //         foreach ($stockerData as $stocker) {
                //             $trolleyCheck = TrolleyStocker::where('stocker_id', $stocker->id)->first();
                //             if (!$trolleyCheck) {
                //                 array_push($trolleyStockArr, [
                //                     "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                //                     "trolley_id" => $thisTrolley->id,
                //                     "stocker_id" => $stocker->id,
                //                     "status" => "active",
                //                     "tanggal_alokasi" => date('Y-m-d'),
                //                     "created_at" => Carbon::now(),
                //                     "updated_at" => Carbon::now(),
                //                 ]);

                //                 $i++;
                //             }
                //         }

                //         $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

                //         if (count($trolleyStockArr) > 0) {
                //             $updateStocker = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'")->
                //                 update([
                //                     "status" => "trolley",
                //                     "latest_alokasi" => Carbon::now()
                //                 ]);
                //         }
                //     }
                // }
        }

        $update_tmp_dc_in = DB::table("tmp_dc_in_input_new")->
            where("id_qr_stocker", $request->id_c )->
            update([
                "qty_reject" => $request->txtqtyreject,
                "qty_replace" => $request->txtqtyreplace,
                "tujuan" => $request->txttuj,
                "tempat" => $request->cbotempat,
                "lokasi" => $request->cbolokasi,
                "ket" => $request->txtket
            ]);

        if (!(is_nan($update_tmp_dc_in))) {
            return array(
                'status' => 300,
                'message' => 'Data Stocker "' . $request->id_c . '" berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-scan',
                'additional' => [],
                'callback' => 'resetCheckedStocker()'
            );
        }
    }

    public function update_mass_tmp_dc_in(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $massStockerIds = explode(",", $request->mass_id_c);

        if (count($massStockerIds) > 0) {
            $stockerCodes = Stocker::selectRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode")->whereIn("id_qr_stocker", $massStockerIds)->pluck('kode')->toArray();
            $stockerCodeRaw = "(";
            for ($i = 0; $i < count($stockerCodes); $i++) {
                if ($i > 0) {
                    $stockerCodeRaw .= ", '".$stockerCodes[$i]."'";
                } else {
                    $stockerCodeRaw .= "'".$stockerCodes[$i]."'";
                }
            }
            $stockerCodeRaw .= ")";

            if ($request->mass_txttuj == 'NON SECONDARY') {
                $update_stocker_input = DB::update("
                    update
                        stocker_input
                    set
                        tempat = '" . $request->mass_cbotempat . "',
                        tujuan = '" . $request->mass_txttuj . "',
                        lokasi = '" . $request->mass_cbolokasi . "'
                    where
                        concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw . "
                ");

                // Trolley Things
                    // if ($request->mass_cbotempat == "TROLLEY") {
                    //     $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                    //     $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                    //     $stockerData = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw)->get();

                    //     $trolleyStockArr = [];

                    //     $thisTrolley = Trolley::where("nama_trolley", $request->mass_cbolokasi)->first();
                    //     if ($thisTrolley) {

                    //         $i = 0;
                    //         foreach ($stockerData as $stocker) {
                    //             $trolleyCheck = TrolleyStocker::where('stocker_id', $stocker->id)->first();
                    //             if (!$trolleyCheck) {
                    //                 array_push($trolleyStockArr, [
                    //                     "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                    //                     "trolley_id" => $thisTrolley->id,
                    //                     "stocker_id" => $stocker->id,
                    //                     "status" => "active",
                    //                     "tanggal_alokasi" => date('Y-m-d'),
                    //                     "created_at" => Carbon::now(),
                    //                     "updated_at" => Carbon::now(),
                    //                 ]);

                    //                 $i++;
                    //             }
                    //         }

                    //         $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

                    //         if (count($trolleyStockArr) > 0) {
                    //             $updateStocker = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw)->
                    //                 update([
                    //                     "status" => "trolley",
                    //                     "latest_alokasi" => Carbon::now()
                    //                 ]);
                    //         }
                    //     }
                    // }
            }

            $update_tmp_dc_in = DB::table("tmp_dc_in_input_new")->
                whereIn("id_qr_stocker", $massStockerIds)->
                update([
                    "tujuan" => $request->mass_txttuj,
                    "tempat" => $request->mass_cbotempat,
                    "lokasi" => $request->mass_cbolokasi,
                ]);

            if (!(is_nan($update_tmp_dc_in))) {
                return array(
                    'status' => 300,
                    'message' => 'Data Stocker "' . $request->mass_id_c . '" berhasil diubah',
                    'redirect' => '',
                    'table' => 'datatable-scan',
                    'additional' => [],
                    'callback' => 'resetCheckedStocker()'
                );
            }
        }

        return array(
            'status' => 400,
            'message' => 'Data Stocker "' . $request->mass_id_c . '" gagal diubah',
            'redirect' => '',
            'table' => 'datatable-scan',
            'additional' => [],
            'callback' => 'resetCheckedStocker()'
        );
    }

    public function delete_mass_tmp_dc_in(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $massStockerIds = explode(",", $request->ids);

        if (count($massStockerIds) > 0) {
            $delete_tmp_dc_in = DB::table("tmp_dc_in_input_new")->
                whereIn("id_qr_stocker", $massStockerIds)->
                delete();

            if ($delete_tmp_dc_in) {
                return array(
                    "status" => 200,
                    "message" => "Stock DC berhasil dihapus"
                );
            }
        }

        return array(
            "status" => 200,
            "message" => "Stock DC gagal dihapus"
        );
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        DB::insert("
            REPLACE INTO dc_in_input
            (
                id_qr_stocker,
                tgl_trans,
                tujuan,
                lokasi,
                tempat,
                qty_awal,
                qty_reject,
                qty_replace,
                user,
                status,
                created_at,
                updated_at
            )
            select
                tmp.id_qr_stocker,
                '$tgltrans',
                tmp.tujuan,
                tmp.lokasi,
                tmp.tempat,
                (case when ms.qty_ply_mod > 0 THEN ms.qty_ply_mod ELSE ms.qty_ply END),
                qty_reject,
                qty_replace,
                user,
                'N',
                '$timestamp',
                '$timestamp'
            from
                tmp_dc_in_input_new tmp
                left join stocker_input ms on tmp.id_qr_stocker = ms.id_qr_stocker
            where
                tmp.tujuan > '' and
                tmp.lokasi > '' and
                tmp.tempat > '' and
                user = '$user'
        ");

        DB::insert("
            INSERT INTO rack_detail_stocker
            (
                detail_rack_id,
                nm_rak,
                stocker_id,
                qty_in,
                created_at,
                updated_at
            )
            select
                r.id,nama_detail_rak,
                tmp.id_qr_stocker,
                (case when s.qty_ply_mod > 0 THEN s.qty_ply_mod ELSE s.qty_ply END) - qty_reject + qty_replace qty_in,
                '$timestamp',
                '$timestamp'
            from
                tmp_dc_in_input_new tmp
                left join rack_detail r on tmp.lokasi = r.nama_detail_rak
                left join stocker_input s on tmp.id_qr_stocker = s.id_qr_stocker
            where
                tmp.tujuan = 'NON SECONDARY' and
                tmp.tujuan > '' and
                tmp.lokasi > '' and
                tmp.tempat > '' and
                user = '$user'
            "
        );

        return array(
            'status' => 999,
            'message' => 'Data Sudah Disimpan',
            'redirect' => 'reload',
            'table' => '',
            'additional' => [],
            'callback' => 'cleard()',
        );
    }

    public function destroy(Request $request)
    {
        $user = Auth::user()->name;

        DB::delete(
            "DELETE FROM tmp_dc_in_input_new where tujuan > '' and lokasi > '' and tempat > '' and user = '$user'"
        );
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
