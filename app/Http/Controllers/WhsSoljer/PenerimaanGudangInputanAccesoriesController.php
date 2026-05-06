<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesories;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanGudangInputanAccesoriesController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = PenerimaanGudangInputanAccesories::selectRaw("
                penerimaan_gudang_inputan_accesories.id,
                penerimaan_gudang_inputan_accesories.no_bpb,
                DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                COALESCE(SUM(penerimaan_gudang_inputan_accesories_detail.qty),0) as total_qty,
                COALESCE(SUM(penerimaan_gudang_inputan_accesories_detail.qty_kgm),0) as total_qty_kgm,
                penerimaan_gudang_inputan_accesories.created_by_username,
                penerimaan_gudang_inputan_accesories.cancel,
                CASE 
                    WHEN penerimaan_gudang_inputan_accesories.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status,
                EXISTS (
                    SELECT 1
                    FROM penerimaan_gudang_inputan_accesories_detail d
                    JOIN pengeluaran_gudang_inputan_accesories_detail pd 
                        ON pd.barcode = d.barcode
                    JOIN pengeluaran_gudang_inputan_accesories p 
                        ON p.id = pd.pengeluaran_gudang_inputan_accesories_id
                    WHERE d.penerimaan_gudang_inputan_accesories_id = penerimaan_gudang_inputan_accesories.id
                    AND p.cancel = 0
                ) as is_used
            ")
            ->leftJoin("penerimaan_gudang_inputan_accesories_detail", "penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id", "=", "penerimaan_gudang_inputan_accesories.id")
            ->groupBy(
                "penerimaan_gudang_inputan_accesories.id",
                "penerimaan_gudang_inputan_accesories.no_bpb",
                "penerimaan_gudang_inputan_accesories.tgl_bpb",
                "penerimaan_gudang_inputan_accesories.created_by_username",
                "penerimaan_gudang_inputan_accesories.cancel"
            );

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("penerimaan_gudang_inputan_accesories.tgl_bpb >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("penerimaan_gudang_inputan_accesories.tgl_bpb <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->whereRaw("
                    CASE 
                        WHEN penerimaan_gudang_inputan_accesories.cancel = 1 THEN 'Cancel'
                        ELSE 'Draft'
                    END LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty)
                        FROM penerimaan_gudang_inputan_accesories_detail detail
                        WHERE detail.penerimaan_gudang_inputan_accesories_id = penerimaan_gudang_inputan_accesories.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty_kgm', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty_kgm)
                        FROM penerimaan_gudang_inputan_accesories_detail detail
                        WHERE detail.penerimaan_gudang_inputan_accesories_id = penerimaan_gudang_inputan_accesories.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('penerimaan_gudang_inputan_accesories.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.penerimaan-gudang-inputan-accesories.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_bpb = DB::selectOne("
            SELECT 
                CONCAT('WHS/A/IN/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                IF(
                    MAX(no_bpb) IS NULL,
                    '00001',
                    LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                ) AS nomor,

                CONCAT(
                    'WHS/A/IN/',
                    DATE_FORMAT(CURRENT_DATE(), '%m'),
                    DATE_FORMAT(CURRENT_DATE(), '%y'),
                    '/',
                    IF(
                        MAX(no_bpb) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                    )
                ) AS kode

            FROM penerimaan_gudang_inputan_accesories
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

        return view("whs-soljer.penerimaan-gudang-inputan-accesories.create", [
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

            $header = PenerimaanGudangInputanAccesories::create([
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
                FROM penerimaan_gudang_inputan_accesories_detail
                WHERE 
                    DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                    AND LEFT(barcode, 2) = 'WA'
            ");

            $counter = $getLast->nomor;

            foreach ($items as $row) {

                $barcode = 'WA' . date('ym') . str_pad($counter, 5, '0', STR_PAD_LEFT);

                PenerimaanGudangInputanAccesoriesDetail::create([
                    'penerimaan_gudang_inputan_accesories_id' => $header->id,
                    'barcode'              => $barcode,
                    'no_box'               => $row['no_box'],
                    'buyer'                => $row['buyer'],
                    'worksheet'            => $row['worksheet'],
                    'nama_barang'          => $row['nama_barang'],
                    'kode'                 => $row['kode'],
                    'warna'                => $row['warna'],
                    'size'                 => $row['size'],
                    'qty'                  => $row['qty'],
                    'satuan'               => $row['satuan'],
                    'qty_kgm'              => $row['qty_kgm'],
                    'lokasi'               => $row['lokasi'],
                    'keterangan'           => $row['keterangan'],
                    "created_by"           => $user ? $user->id : null,
                    "created_by_username"  => $user ? $user->username : null,
                    "created_at"           => $now,
                ]);

                PenerimaanGudangInputanAccesoriesHistory::create([
                    'penerimaan_gudang_inputan_accesories_id' => $header->id,
                    'barcode'              => $barcode,
                    'no_box'               => $row['no_box'],
                    'buyer'                => $row['buyer'],
                    'worksheet'            => $row['worksheet'],
                    'nama_barang'          => $row['nama_barang'],
                    'kode'                 => $row['kode'],
                    'warna'                => $row['warna'],
                    'size'                 => $row['size'],
                    'qty'                  => $row['qty'],
                    'satuan'               => $row['satuan'],
                    'qty_kgm'              => $row['qty_kgm'],
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
                "message" => "Data Penerimaan Gudang Inputan (ACCESORIES) berhasil disimpan.",
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

        $data = PenerimaanGudangInputanAccesories::selectRaw("
            penerimaan_gudang_inputan_accesories.id,
            penerimaan_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb
        ")
        ->where("penerimaan_gudang_inputan_accesories.id", $id)
        ->first();

        $data_detail = PenerimaanGudangInputanAccesoriesDetail::where("penerimaan_gudang_inputan_accesories_id", $id)->get();

        return view("whs-soljer.penerimaan-gudang-inputan-accesories.update", [
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
                $dataDetail = PenerimaanGudangInputanAccesoriesDetail::find($row['id']);
                
                PenerimaanGudangInputanAccesoriesHistory::create([
                    'penerimaan_gudang_inputan_accesories_id' => $dataDetail->penerimaan_gudang_inputan_accesories_id,
                    'barcode'              => $dataDetail->barcode,
                    'no_box'               => $dataDetail->no_box,
                    'buyer'                => $dataDetail->buyer,
                    'worksheet'            => $dataDetail->worksheet,
                    'nama_barang'          => $dataDetail->nama_barang,
                    'kode'                 => $dataDetail->kode,
                    'warna'                => $dataDetail->warna,
                    'size'                 => $dataDetail->size,
                    'qty'                  => $row['qty'],
                    'satuan'               => $dataDetail->satuan,
                    'qty_kgm'              => $row['qty_kgm'],
                    'lokasi'               => $dataDetail->lokasi,
                    'keterangan'           => $dataDetail->keterangan,
                    "created_by"           => $user ? $user->id : null,
                    "created_by_username"  => $user ? $user->username : null,
                    "created_at"           => $now,
                ]);

                PenerimaanGudangInputanAccesoriesDetail::where('id', $row['id'])
                    ->update([
                        'qty' => $row['qty'],
                        'qty_kgm' => $row['qty_kgm'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Penerimaan Gudang Inputan (ACCESORIES) berhasil diupdate.'
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
        $data = PenerimaanGudangInputanAccesories::findOrFail($id);

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
        $data = PenerimaanGudangInputanAccesories::selectRaw("
            penerimaan_gudang_inputan_accesories.id,
            penerimaan_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_accesories_detail.*
        ")
        ->leftJoin('penerimaan_gudang_inputan_accesories_detail', 'penerimaan_gudang_inputan_accesories.id', '=', 'penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id')
        ->where("penerimaan_gudang_inputan_accesories.id", $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan-accesories.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Penerimaan_Gudang_Inputan_Accesories_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = PenerimaanGudangInputanAccesories::selectRaw("
            penerimaan_gudang_inputan_accesories.id,
            penerimaan_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_accesories.created_at,
            penerimaan_gudang_inputan_accesories.created_by_username
        ")
        ->where("penerimaan_gudang_inputan_accesories.id", $id)
        ->first();

        $dataDetail = PenerimaanGudangInputanAccesoriesDetail::selectRaw('
            buyer,
            worksheet,
            nama_barang,
            kode,
            warna,
            size,
            SUM(qty) as qty,
            satuan,
            SUM(qty_kgm) as qty_kgm,
            keterangan,
            lokasi
        ')
        ->where("penerimaan_gudang_inputan_accesories_id", $id)
        ->groupBy('buyer', 'worksheet', 'nama_barang', 'kode', 'warna', 'size', 'satuan', 'keterangan', 'lokasi')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.penerimaan-gudang-inputan-accesories.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Penerimaan_Gudang_Inputan_Accesories_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function contohUploadImport()
    {
        $path = public_path('assets/example/contoh-import-penerimaan-gudang-input-accesories.xlsx');
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
                'no_box_koli'   => $row[0],
                'buyer'         => $row[1],
                'worksheet'     => $row[2],
                'nama_barang'   => $row[3],
                'kode'          => $row[4],
                'warna'         => $row[5],
                'size'          => $row[6],
                'qty'           => $row[7],
                'satuan'        => $row[8],
                'qty_kgm'       => $row[9],
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
