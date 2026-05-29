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

class InjectAdjustmentController extends Controller
{

    public function index() {
        return view("general.tools.inject-adjustment");
    }

    public function contohUploadImport()
    {
        $path = public_path('assets/example/contoh-import-inject-adjustment.xlsx');
        return response()->download($path);
    }

    public function importData(Request $request)
    {
        $file = $request->file('file');

        $rows = Excel::toArray([], $file)[0];

        $data = [];

        foreach ($rows as $i => $row) {
            if ($i == 0) continue;

            $data[] = [
                'tgl_saldo'     => Date::excelToDateTimeObject($row[0])->format('d-m-Y'),
                'type_report'   => $row[1] ?? null,
                'buyer'         => $row[2] ?? null,
                'ws'            => $row[3] ?? null,
                'style'         => $row[4] ?? null,
                'color'         => $row[5] ?? null,
                'size'          => $row[6] ?? null,
                'panel'         => $row[7] ?? null,
                'part'          => $row[8] ?? null,
                'qty'           => $row[9] ?? null,
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $source = $request->source;
        $items = $request->items;

        foreach ($items as $item) {

            if($source == 'NDS'){
                DB::connection('mysql')
                ->table('wip_adjustment')
                ->insert([
                    'tgl_saldo' => date('Y-m-d', strtotime($item['tgl_saldo'])),
                    'type_report' => $item['type_report'],
                    'buyer' => $item['buyer'],
                    'no_ws' => $item['ws'],
                    'style' => $item['style'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'panel' => $item['panel'],
                    'part' => $item['part'],
                    'qty' => $item['qty'],
                    'status' => 'Y'
                ]);
            }else{
                DB::connection('mysql_sb')
                ->table('wip_adjustment')
                ->insert([
                    'tgl_saldo' => date('Y-m-d', strtotime($item['tgl_saldo'])),
                    'type_report' => $item['type_report'],
                    'buyer' => $item['buyer'],
                    'no_ws' => $item['ws'],
                    'style' => $item['style'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'panel' => $item['panel'],
                    'part' => $item['part'],
                    'qty' => $item['qty'],
                    'status' => 'Y'
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil disimpan'
        ]);
    }

    public function delete(Request $request)
    {
        if($request->source == 'NDS'){
            DB::connection('mysql')
                ->table('wip_adjustment')
                ->whereIn('id', $request->ids)
                ->delete();
        }else{
            DB::connection('mysql_sb')
                ->table('wip_adjustment')
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
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_saldo
                FROM
                    wip_adjustment
                WHERE
                    tgl_saldo BETWEEN '$tglAwal' AND '$tglAkhir'
                ORDER BY
                    id DESC
            ");
        }else{
            $data = DB::connection("mysql_sb")->select("
                SELECT
                    *,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_saldo
                FROM
                    wip_adjustment
                WHERE
                    tgl_saldo BETWEEN '$tglAwal' AND '$tglAkhir'
                ORDER BY
                    id DESC
            ");
        }

        return DataTables::of($data)->toJson();
    }
}