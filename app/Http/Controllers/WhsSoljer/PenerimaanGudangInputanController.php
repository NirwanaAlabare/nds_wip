<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputan;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanGudangInputanController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = PenerimaanGudangInputan::selectRaw("
                penerimaan_gudang_inputan.id,
                penerimaan_gudang_inputan.no_bpb,
                DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                COALESCE(SUM(penerimaan_gudang_inputan_detail.qty),0) as total_qty,
                penerimaan_gudang_inputan.created_by_username,
                penerimaan_gudang_inputan.cancel,
                CASE 
                    WHEN penerimaan_gudang_inputan.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status,
                EXISTS (
                    SELECT 1
                    FROM penerimaan_gudang_inputan_detail d
                    JOIN pengeluaran_gudang_inputan_detail pd 
                        ON pd.barcode = d.barcode
                    JOIN pengeluaran_gudang_inputan p 
                        ON p.id = pd.pengeluaran_gudang_inputan_id
                    WHERE d.penerimaan_gudang_inputan_id = penerimaan_gudang_inputan.id
                    AND p.cancel = 0
                ) as is_used
            ")
            ->leftJoin("penerimaan_gudang_inputan_detail", "penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id", "=", "penerimaan_gudang_inputan.id")
            ->groupBy(
                "penerimaan_gudang_inputan.id",
                "penerimaan_gudang_inputan.no_bpb",
                "penerimaan_gudang_inputan.tgl_bpb",
                "penerimaan_gudang_inputan.created_by_username",
                "penerimaan_gudang_inputan.cancel"
            );

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->whereRaw("
                    CASE 
                        WHEN penerimaan_gudang_inputan.cancel = 1 THEN 'Cancel'
                        ELSE 'Draft'
                    END LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty)
                        FROM penerimaan_gudang_inputan_detail detail
                        WHERE detail.penerimaan_gudang_inputan_id = penerimaan_gudang_inputan.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('penerimaan_gudang_inputan.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.penerimaan-gudang-inputan.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_bpb = DB::selectOne("
            SELECT 
                CONCAT('WHS/F/IN/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                IF(
                    MAX(no_bpb) IS NULL,
                    '00001',
                    LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                ) AS nomor,

                CONCAT(
                    'WHS/F/IN/',
                    DATE_FORMAT(CURRENT_DATE(), '%m'),
                    DATE_FORMAT(CURRENT_DATE(), '%y'),
                    '/',
                    IF(
                        MAX(no_bpb) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                    )
                ) AS kode

            FROM penerimaan_gudang_inputan
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

        return view("whs-soljer.penerimaan-gudang-inputan.create", [
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

            $header = PenerimaanGudangInputan::create([
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
                FROM penerimaan_gudang_inputan_detail
                WHERE 
                    DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                    AND LEFT(barcode, 2) = 'WF'
            ");

            $counter = $getLast->nomor;

            foreach ($items as $row) {

                $barcode = 'WF' . date('ym') . str_pad($counter, 5, '0', STR_PAD_LEFT);

                PenerimaanGudangInputanDetail::create([
                    'penerimaan_gudang_inputan_id'  => $header->id,
                    'barcode'                       => $barcode,
                    'no_roll'                       => $row['no_roll'],
                    'buyer'                         => $row['buyer'],
                    'jenis_item'                    => $row['jenis_item'],
                    'warna'                         => $row['warna'],
                    'lot'                           => $row['lot'],
                    'qty'                           => $row['qty'],
                    'satuan'                        => $row['satuan'],
                    'lokasi'                        => $row['lokasi'],
                    'keterangan'                    => $row['keterangan'],
                    "created_by"                    => $user ? $user->id : null,
                    "created_by_username"           => $user ? $user->username : null,
                    "created_at"                    => $now,
                ]);

                PenerimaanGudangInputanHistory::create([
                    'penerimaan_gudang_inputan_id'  => $header->id,
                    'barcode'                       => $barcode,
                    'no_roll'                       => $row['no_roll'],
                    'buyer'                         => $row['buyer'],
                    'jenis_item'                    => $row['jenis_item'],
                    'warna'                         => $row['warna'],
                    'lot'                           => $row['lot'],
                    'qty'                           => $row['qty'],
                    'satuan'                        => $row['satuan'],
                    'lokasi'                        => $row['lokasi'],
                    'keterangan'                    => $row['keterangan'],
                    "created_by"                    => $user ? $user->id : null,
                    "created_by_username"           => $user ? $user->username : null,
                    "created_at"                    => $now,
                ]);

                $counter++;
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Penerimaan Gudang Inputan (FABRIC) berhasil disimpan.",
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

        $data = PenerimaanGudangInputan::selectRaw("
            penerimaan_gudang_inputan.id,
            penerimaan_gudang_inputan.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb
        ")
        ->where("penerimaan_gudang_inputan.id", $id)
        ->first();

        $data_detail = PenerimaanGudangInputanDetail::where("penerimaan_gudang_inputan_id", $id)->get();

        return view("whs-soljer.penerimaan-gudang-inputan.update", [
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

            // Delete
            $existingIds = PenerimaanGudangInputanDetail::where('penerimaan_gudang_inputan_id', $id)
                ->pluck('id')
                ->toArray();

            $submittedIds = collect($items)->pluck('id')->toArray();

            $deletedIds = array_diff($existingIds, $submittedIds);

            if (!empty($deletedIds)) {
                PenerimaanGudangInputanDetail::whereIn('id', $deletedIds)->delete();
            }

            // Update and Create
            foreach ($items as $row) {
                $dataDetail = PenerimaanGudangInputanDetail::find($row['id']);

                $oldQty = (float) $dataDetail->qty;
                $newQty = (float) $row['qty'];

                if ($oldQty != $newQty) {
                    PenerimaanGudangInputanHistory::create([
                        'penerimaan_gudang_inputan_id'  => $dataDetail->penerimaan_gudang_inputan_id,
                        'barcode'                       => $dataDetail->barcode,
                        'no_roll'                       => $dataDetail->no_roll,
                        'buyer'                         => $dataDetail->buyer,
                        'jenis_item'                    => $dataDetail->jenis_item,
                        'warna'                         => $dataDetail->warna,
                        'lot'                           => $dataDetail->lot,
                        'qty'                           => $row['qty'],
                        'satuan'                        => $dataDetail->satuan,
                        'lokasi'                        => $dataDetail->lokasi,
                        'keterangan'                    => $dataDetail->keterangan,
                        "created_by"                    => $user ? $user->id : null,
                        "created_by_username"           => $user ? $user->username : null,
                        "created_at"                    => $now,
                    ]);
                    
                    PenerimaanGudangInputanDetail::where('id', $row['id'])
                        ->update([
                            'qty' => $row['qty'],
                            'updated_at' => now(),
                            'updated_by' => auth()->id(),
                        ]);
                }

            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Penerimaan Gudang Inputan (FABRIC) berhasil diupdate.'
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
        $data = PenerimaanGudangInputan::findOrFail($id);

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
        $data = PenerimaanGudangInputan::selectRaw("
            penerimaan_gudang_inputan.id,
            penerimaan_gudang_inputan.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_detail.*
        ")
        ->leftJoin('penerimaan_gudang_inputan_detail', 'penerimaan_gudang_inputan.id', '=', 'penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id')
        ->where("penerimaan_gudang_inputan.id", $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Penerimaan_Gudang_Inputan_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = PenerimaanGudangInputan::selectRaw("
            penerimaan_gudang_inputan.id,
            penerimaan_gudang_inputan.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan.created_at,
            penerimaan_gudang_inputan.created_by_username
        ")
        ->where("penerimaan_gudang_inputan.id", $id)
        ->first();

        $dataDetail = PenerimaanGudangInputanDetail::selectRaw('
            buyer,
            jenis_item,
            warna,
            lot,
            SUM(qty) as qty,
            satuan,
            keterangan,
            lokasi
        ')
        ->where("penerimaan_gudang_inputan_id", $id)
        ->groupBy('buyer', 'jenis_item', 'warna', 'lot', 'satuan', 'keterangan', 'lokasi')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Penerimaan_Gudang_Inputan_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function contohUploadImport()
    {
        $path = public_path('assets/example/contoh-import-penerimaan-gudang-input.xlsx');
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
                'lokasi'      => $row[0],
                'buyer'       => $row[1],
                'keterangan'  => $row[2],
                'jenis_item'  => $row[3],
                'warna'       => $row[4],
                'lot'         => $row[5],
                'no_roll'     => $row[6],
                'qty'         => $row[7],
                'satuan'      => $row[8],
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }
}
