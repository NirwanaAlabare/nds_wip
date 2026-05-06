<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputanFg;
use App\Models\WhsSoljer\PenerimaanGudangInputanFgDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanFgHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanGudangInputanFgController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = PenerimaanGudangInputanFg::selectRaw("
                penerimaan_gudang_inputan_fg.id,
                penerimaan_gudang_inputan_fg.no_bpb,
                DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                COALESCE(SUM(penerimaan_gudang_inputan_fg_detail.qty),0) as total_qty,
                penerimaan_gudang_inputan_fg.created_by_username,
                penerimaan_gudang_inputan_fg.cancel,
                CASE 
                    WHEN penerimaan_gudang_inputan_fg.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status,
                EXISTS (
                    SELECT 1
                    FROM penerimaan_gudang_inputan_fg_detail d
                    JOIN pengeluaran_gudang_inputan_fg_detail pd 
                        ON pd.barcode = d.barcode
                    JOIN pengeluaran_gudang_inputan_fg p 
                        ON p.id = pd.pengeluaran_gudang_inputan_fg_id
                    WHERE d.penerimaan_gudang_inputan_fg_id = penerimaan_gudang_inputan_fg.id
                    AND p.cancel = 0
                ) as is_used
            ")
            ->leftJoin("penerimaan_gudang_inputan_fg_detail", "penerimaan_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_id", "=", "penerimaan_gudang_inputan_fg.id")
            ->groupBy(
                "penerimaan_gudang_inputan_fg.id",
                "penerimaan_gudang_inputan_fg.no_bpb",
                "penerimaan_gudang_inputan_fg.tgl_bpb",
                "penerimaan_gudang_inputan_fg.created_by_username",
                "penerimaan_gudang_inputan_fg.cancel"
            );

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("penerimaan_gudang_inputan_fg.tgl_bpb >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("penerimaan_gudang_inputan_fg.tgl_bpb <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->whereRaw("
                    CASE 
                        WHEN penerimaan_gudang_inputan_fg.cancel = 1 THEN 'Cancel'
                        ELSE 'Draft'
                    END LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty)
                        FROM penerimaan_gudang_inputan_fg_detail detail
                        WHERE detail.penerimaan_gudang_inputan_fg_id = penerimaan_gudang_inputan_fg.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('penerimaan_gudang_inputan_fg.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.penerimaan-gudang-inputan-fg.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_bpb = DB::selectOne("
            SELECT 
                CONCAT('WHS/FG/IN/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                IF(
                    MAX(no_bpb) IS NULL,
                    '00001',
                    LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                ) AS nomor,

                CONCAT(
                    'WHS/FG/IN/',
                    DATE_FORMAT(CURRENT_DATE(), '%m'),
                    DATE_FORMAT(CURRENT_DATE(), '%y'),
                    '/',
                    IF(
                        MAX(no_bpb) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                    )
                ) AS kode

            FROM penerimaan_gudang_inputan_fg
            WHERE 
                MONTH(tgl_bpb) = MONTH(CURRENT_DATE())
                AND YEAR(tgl_bpb) = YEAR(CURRENT_DATE())
                AND LEFT(no_bpb, 3) = 'WHS'
        ");

        $satuan = DB::connection('mysql_sb')->select("
            SELECT
                id,
                nama_pilihan
            FROM 
                masterpilihan
            WHERE
                kode_pilihan = 'Satuan'
        ");

        $lokasi = DB::connection('mysql_sb')->select("
            SELECT
                idx,
                lokasi
            FROM 
                masterlokasi
        ");

        return view("whs-soljer.penerimaan-gudang-inputan-fg.create", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            "no_bpb" => $no_bpb,
            "satuan" => $satuan,
            "lokasi" => $lokasi,
            'containerFluid' => true
        ]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $header = PenerimaanGudangInputanFg::create([
                'no_bpb'                => $request->no_bpb,
                'tgl_bpb'               => date('Y-m-d'),
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);

            $items = json_decode($request->items, true);

            $getLast = DB::selectOne("
                SELECT 
                    IF(
                        MAX(barcode) IS NULL,
                        1,
                        MAX(RIGHT(barcode, 5)) + 1
                    ) AS nomor
                FROM penerimaan_gudang_inputan_fg_detail
                WHERE 
                    DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                    AND LEFT(barcode, 3) = 'WFG'
            ");

            $counter = $getLast->nomor;

            foreach ($items as $row) {

                $barcode = 'WFG' . date('ym') . str_pad($counter, 5, '0', STR_PAD_LEFT);

                PenerimaanGudangInputanFgDetail::create([
                    'penerimaan_gudang_inputan_fg_id' => $header->id,
                    'barcode'              => $barcode,
                    'no_koli'              => $row['no_koli'],
                    'buyer'                => $row['buyer'],
                    'no_ws'                => $row['no_ws'],
                    'style'                => $row['style'],
                    'product_item'         => $row['product_item'],
                    'warna'                => $row['warna'],
                    'size'                 => $row['size'],
                    'grade'                => $row['grade'],
                    'qty'                  => $row['qty'],
                    'satuan'               => $row['satuan'],
                    'lokasi'               => $row['lokasi'],
                    'keterangan'           => $row['keterangan'],
                    "created_by"           => $user ? $user->id : null,
                    "created_by_username"  => $user ? $user->username : null,
                    "created_at"           => $now,
                ]);

                PenerimaanGudangInputanFgHistory::create([
                    'penerimaan_gudang_inputan_fg_id' => $header->id,
                    'barcode'              => $barcode,
                    'no_koli'              => $row['no_koli'],
                    'buyer'                => $row['buyer'],
                    'no_ws'                => $row['no_ws'],
                    'style'                => $row['style'],
                    'product_item'         => $row['product_item'],
                    'warna'                => $row['warna'],
                    'size'                 => $row['size'],
                    'grade'                => $row['grade'],
                    'qty'                  => $row['qty'],
                    'satuan'               => $row['satuan'],
                    'lokasi'               => $row['lokasi'],
                    'keterangan'           => $row['keterangan'],
                    "created_by"           => $user ? $user->id : null,
                    "created_by_username"  => $user ? $user->username : null,
                    "created_at"           => $now,
                ]);

                $counter++;
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Penerimaan Gudang Inputan (FG) berhasil disimpan.",
                "additional" => [],
            );

        } catch (Exception $e) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Terjadi Kesalahan",
                "additional" => [],
            );
        }
    }

    public function edit($id){

        $data = PenerimaanGudangInputanFg::selectRaw("
            penerimaan_gudang_inputan_fg.id,
            penerimaan_gudang_inputan_fg.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb
        ")
        ->where("penerimaan_gudang_inputan_fg.id", $id)
        ->first();

        $data_detail = PenerimaanGudangInputanFgDetail::where("penerimaan_gudang_inputan_fg_id", $id)->get();

        return view("whs-soljer.penerimaan-gudang-inputan-fg.update", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            "data" => $data,
            "data_detail" => $data_detail,
            'containerFluid' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $items = json_decode($request->items, true);

            if (!$items || !is_array($items)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Data items kosong / tidak valid'
                ]);
            }

            foreach ($items as $row) {
                $dataDetail = PenerimaanGudangInputanFgDetail::find($row['id']);

                PenerimaanGudangInputanFgHistory::create([
                    'penerimaan_gudang_inputan_fg_id' => $dataDetail->penerimaan_gudang_inputan_fg_id,
                    'barcode'              => $dataDetail->barcode,
                    'no_koli'              => $dataDetail->no_koli,
                    'buyer'                => $dataDetail->buyer,
                    'no_ws'                => $dataDetail->no_ws,
                    'style'                => $dataDetail->style,
                    'product_item'         => $dataDetail->product_item,
                    'warna'                => $dataDetail->warna,
                    'size'                 => $dataDetail->size,
                    'grade'                => $dataDetail->grade,
                    'qty'                  => $row['qty'],
                    'satuan'               => $dataDetail->satuan,
                    'lokasi'               => $dataDetail->lokasi,
                    'keterangan'           => $dataDetail->keterangan,
                    "created_by"           => $user ? $user->id : null,
                    "created_by_username"  => $user ? $user->username : null,
                    "created_at"           => $now,
                ]);

                PenerimaanGudangInputanFgDetail::where('id', $row['id'])
                    ->update([
                        'qty' => $row['qty'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Penerimaan Gudang Inputan (FG) berhasil diupdate.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancel($id)
    {
        $data = PenerimaanGudangInputanFg::findOrFail($id);

        $data->update([
            'cancel' => 1
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dicancel'
        ]);
    }

    public function printBarcode($id)
    {
        $data = PenerimaanGudangInputanFg::selectRaw("
            penerimaan_gudang_inputan_fg.id,
            penerimaan_gudang_inputan_fg.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_fg_detail.*
        ")
        ->leftJoin('penerimaan_gudang_inputan_fg_detail', 'penerimaan_gudang_inputan_fg.id', '=', 'penerimaan_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_id')
        ->where("penerimaan_gudang_inputan_fg.id", $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan-fg.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Penerimaan_Gudang_Inputan_Fg_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = PenerimaanGudangInputanFg::selectRaw("
            penerimaan_gudang_inputan_fg.id,
            penerimaan_gudang_inputan_fg.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_fg.created_at,
            penerimaan_gudang_inputan_fg.created_by_username
        ")
        ->where("penerimaan_gudang_inputan_fg.id", $id)
        ->first();

        $dataDetail = PenerimaanGudangInputanFgDetail::selectRaw('
            buyer,
            no_ws,
            style,
            product_item,
            warna,
            size,
            grade,
            SUM(qty) as qty,
            satuan,
            keterangan,
            lokasi
        ')
        ->where("penerimaan_gudang_inputan_fg_id", $id)
        ->groupBy('buyer', 'no_ws', 'style', 'product_item', 'warna', 'size', 'grade', 'satuan', 'keterangan', 'lokasi')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan-fg.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Penerimaan_Gudang_Inputan_Fg_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function contohUploadImport()
    {
        $path = public_path('assets/example/contoh-import-penerimaan-gudang-input-fg.xlsx');
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
                'no_koli'       => $row[0],
                'buyer'         => $row[1],
                'no_ws'         => $row[2],
                'style'         => $row[3],
                'product_item'  => $row[4],
                'warna'         => $row[5],
                'size'          => $row[6],
                'grade'         => $row[7],
                'qty'           => $row[8],
                'satuan'        => $row[9],
                'keterangan'    => $row[10],
                'lokasi'        => $row[11],
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }
}
