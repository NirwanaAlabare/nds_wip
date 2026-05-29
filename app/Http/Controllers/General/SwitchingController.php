<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Part\Part;
use App\Models\Stocker\Stocker;
use DB;
use Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yajra\DataTables\Facades\DataTables;

class SwitchingController extends Controller
{
    public function index() {

        $wsList = DB::table('master_sb_ws')->select('tgl_kirim', 'id_act_cost', 'ws', 'buyer', 'styleno')->distinct()->orderBy('tgl_kirim', 'desc')->limit(1000)->get();

        return view("general.tools.switching", ['wsList' => $wsList]);
    }

    public function getDataColor(Request $request)
    {
        $colors = DB::table('master_sb_ws')
            ->where('ws', $request->ws)
            ->select('color')
            ->distinct()
            ->get();

        return response()->json($colors);
    }

    public function getDataSize(Request $request)
    {
        $sizes = DB::table('master_sb_ws')
            ->where('ws', $request->ws)
            ->where('color', $request->color)
            ->select('size')
            ->distinct()
            ->get();

        return response()->json($sizes);
    }

    public function getDataPanel(Request $request)
    {
        $panels = DB::table('part')
            ->where('act_costing_ws', $request->ws)
            ->where('color', $request->color)
            ->select('panel')
            ->distinct()
            ->get();

        return response()->json($panels);
    }

    public function getDataPart(Request $request)
    {
        $parts = DB::table('master_part')
            ->join('part_detail', 'part_detail.master_part_id', '=', 'master_part.id')
            ->join('part', 'part.id', '=', 'part_detail.part_id')
            ->where('part.act_costing_ws', $request->ws)
            ->where('part.color', $request->color)
            ->where('part.panel', $request->panel)
            ->select('master_part.nama_part')
            ->distinct()
            ->get();

        return response()->json($parts);
    }

    public function store(Request $request)
    {
        $connection = $request->source == 'NDS' ? 'mysql' : 'mysql_sb';

        DB::connection($connection)
            ->table('wip_switching_adj')
            ->insert([
                'type_report' => $request->type_report,
                'from_tgl_saldo' => $request->from_tgl_saldo,
                'from_no_ws' => $request->from_no_ws,
                'from_buyer' => $request->from_buyer,
                'from_style' => $request->from_style,
                'from_color' => $request->from_color,
                'from_size' => $request->from_size,
                'from_panel' => $request->from_panel,
                'from_part' => $request->from_part,
                'from_qty' => $request->from_qty,
                'tgl_saldo' => $request->tgl_saldo,
                'no_ws' => $request->no_ws,
                'buyer' => $request->buyer,
                'style' => $request->style,
                'color' => $request->color,
                'size' => $request->size,
                'panel' => $request->panel,
                'part' => $request->part,
                'qty' => $request->qty,
                'status' => 'Y',
            ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil disimpan'
        ]);
    }

    public function delete(Request $request)
    {
        if($request->source == 'NDS'){
            DB::connection('mysql')
                ->table('wip_switching_adj')
                ->whereIn('id', $request->ids)
                ->delete();
        }else{
            DB::connection('mysql_sb')
                ->table('wip_switching_adj')
                ->whereIn('id', $request->ids)
                ->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function getData(Request $request)
    {
        $tglAwal = $request->dateFrom;
        $tglAkhir = $request->dateTo;
        $source = $request->source;

        if($source == 'NDS'){
            $data = DB::connection("mysql")->select("
                SELECT
                    *,
                    DATE_FORMAT(from_tgl_saldo, '%d-%m-%Y') AS from_tgl_saldo,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_saldo
                FROM
                    wip_switching_adj
                WHERE
                    from_tgl_saldo BETWEEN '$tglAwal' AND '$tglAkhir'
                ORDER BY
                    id DESC
            ");
        }else{
            $data = DB::connection("mysql_sb")->select("
                SELECT
                    *,
                    DATE_FORMAT(from_tgl_saldo, '%d-%m-%Y') AS from_tgl_saldo,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_saldo
                FROM
                    wip_switching_adj
                WHERE
                    from_tgl_saldo BETWEEN '$tglAwal' AND '$tglAkhir'
                ORDER BY
                    id DESC
            ");
        }

        return DataTables::of($data)->toJson();
    }
}