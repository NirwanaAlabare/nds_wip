<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RackDetailStocker;
use App\Models\SecondaryIn;
use App\Models\Trolley;
use App\Models\TrolleyStocker;
use App\Models\Stocker;
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
                    $request->search['value'] .
                    "%'
                    )
                ";
            }

            if ($request->sec_filter_tipe && count($request->sec_filter_tipe) > 0) {
                $additionalQuery .= " and (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) in (".addQuotesAround(implode("\n", $request->sec_filter_tipe)).")";
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
                $additionalQuery .= " and f.no_cut in (".addQuotesAround(implode("\n", $request->sec_filter_no_cut)).")";
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
                (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                dc.tujuan,
                dc.lokasi,
                s.lokasi lokasi_rak,
                a.qty_awal,
                a.qty_reject,
                a.qty_replace,
                a.qty_in,
                a.created_at,
                f.no_cut,
                COALESCE(msb.size, s.size) size,
                a.user,
                mp.nama_part
                from secondary_in_input a
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on pd.part_id = p.id
                left join master_part mp on mp.id = pd.master_part_id
                left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
                left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
                where
                a.tgl_trans is not null
                ".$additionalQuery."
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
            (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            a.tgl_trans,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            dc.tujuan,
            dc.lokasi,
            s.lokasi lokasi_rak,
            a.qty_awal,
            a.qty_reject,
            a.qty_replace,
            a.qty_in,
            a.created_at,
            f.no_cut,
            COALESCE(msb.size, s.size) size,
            a.user,
            mp.nama_part
            from secondary_in_input a
            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on fr.id = s.form_reject_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on pd.part_id = p.id
            left join master_part mp on mp.id = pd.master_part_id
            left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
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

    public function cek_data_stocker_in(Request $request)
    {
        $cekdata =  DB::select("
        select
        s.id_qr_stocker,
        s.act_costing_ws,
        msb.buyer,
        no_cut,
        msb.styleno as style,
        s.color,
        COALESCE(msb.size, s.size) size,
        dc.tujuan,
        dc.lokasi,
        mp.nama_part,
        if(dc.tujuan = 'SECONDARY LUAR', (dc.qty_awal - dc.qty_reject + dc.qty_replace), (si.qty_awal - si.qty_reject + si.qty_replace)) qty_awal,
        s.lokasi lokasi_tujuan,
        s.tempat tempat_tujuan
        from
        (
        select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2  from dc_in_input dc
        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
        left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
        where dc.tujuan = 'SECONDARY DALAM' and
        ifnull(si.id_qr_stocker,'x') != 'x' and ifnull(sii.id_qr_stocker,'x') = 'x'
        union
        select dc.id_qr_stocker, 'x' cek_1, if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') cek_2  from dc_in_input dc
        left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
        where dc.tujuan = 'SECONDARY LUAR'	and	if(sii.id_qr_stocker is null ,dc.id_qr_stocker,'x') != 'x'
        ) md
        left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        left join form_cut_input a on s.form_cut_id = a.id
        left join part_detail p on s.part_detail_id = p.id
        left join master_part mp on p.master_part_id = mp.id
        left join marker_input mi on a.id_marker = mi.kode
        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
        where s.id_qr_stocker =     '" . $request->txtqrstocker . "'
        ");
        return json_encode($cekdata[0]);
    }

    public function cek_data_stocker_in_edit(Request $request)
    {
        $cekdata =  DB::select("
        select
        s.id_qr_stocker,
        s.act_costing_ws,
        msb.buyer,
        no_cut,
        msb.styleno as style,
        s.color,
        COALESCE(msb.size, s.size) size,
        dc.tujuan,
        dc.lokasi,
        mp.nama_part,
        sii.qty_awal,
        sii.qty_reject,
        sii.qty_replace,
        sii.qty_in,
        s.lokasi lokasi_tujuan,
        s.tempat tempat_tujuan
        from
        (
        select dc.id_qr_stocker,ifnull(si.id_qr_stocker,'x') cek_1, ifnull(sii.id_qr_stocker,'x') cek_2  from dc_in_input dc
        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
        left join secondary_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
        where dc.tujuan = 'SECONDARY DALAM' and
        ifnull(si.id_qr_stocker,'x') != 'x'
        ) md
        left join stocker_input s on md.id_qr_stocker = s.id_qr_stocker
        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        left join form_cut_input a on s.form_cut_id = a.id
        left join part_detail p on s.part_detail_id = p.id
        left join master_part mp on p.master_part_id = mp.id
        left join marker_input mi on a.id_marker = mi.kode
        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
        left join secondary_inhouse_input si on s.id_qr_stocker = si.id_qr_stocker
        left join secondary_in_input sii on s.id_qr_stocker = sii.id_qr_stocker
        where s.id_qr_stocker = '" . $request->txtqrstocker . "'
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

        $saveinhouse = SecondaryIn::updateOrCreate(
            ['id_qr_stocker' => $request['txtno_stocker']],
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
            "update stocker_input set status = 'non secondary' where id_qr_stocker = '" . $request->txtno_stocker . "'"
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

        $thisStocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.act_costing_ws, stocker_input.color, form_cut_input.no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            where("id_qr_stocker", $request['txtno_stocker'])->
            first();

        if ($thisStocker) {
            $cekdata =  DB::select("
                SELECT
                    s.id_qr_stocker,
                    s.act_costing_ws,
                    msb.buyer,
                    no_cut,
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
