<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\MutasiRak;
use App\Models\WhsSoljer\MutasiRakDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputan;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanHistory;
use App\Models\WhsSoljer\PengeluaranGudangInputan;
use App\Models\WhsSoljer\PengeluaranGudangInputanDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputanHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class MutasiRakController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = MutasiRak::selectRaw("
                mutasi_rak.id,
                mutasi_rak.no_mutasi,
                DATE_FORMAT(mutasi_rak.tgl_mutasi, '%d-%m-%Y') AS tgl_mutasi,
                mutasi_rak.lokasi_tujuan,
                mutasi_rak.keterangan,
                mutasi_rak.created_by_username,
                mutasi_rak.cancel,
                CASE 
                    WHEN mutasi_rak.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status,
                EXISTS (
                    SELECT 1
                    FROM mutasi_rak_detail md
                    JOIN pengeluaran_gudang_inputan_detail pd
                        ON pd.barcode = md.barcode
                        AND pd.lokasi = md.lokasi_tujuan
                    JOIN pengeluaran_gudang_inputan p ON p.id = pd.pengeluaran_gudang_inputan_id
                    WHERE md.mutasi_rak_id = mutasi_rak.id
                    AND p.cancel = '0'
                ) as is_used
            ");

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("mutasi_rak.tgl_mutasi >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("mutasi_rak.tgl_mutasi <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_mutasi', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(mutasi_rak.tgl_mutasi, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('mutasi_rak.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.mutasi-rak.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_mutasi = DB::selectOne("
            SELECT 
                CONCAT('MT/F/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                IF(
                    MAX(no_mutasi) IS NULL,
                    '00001',
                    LPAD(MAX(RIGHT(no_mutasi, 5)) + 1, 5, 0)
                ) AS nomor,

                CONCAT(
                    'MT/F/',
                    DATE_FORMAT(CURRENT_DATE(), '%m'),
                    DATE_FORMAT(CURRENT_DATE(), '%y'),
                    '/',
                    IF(
                        MAX(no_mutasi) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_mutasi, 5)) + 1, 5, 0)
                    )
                ) AS kode

            FROM mutasi_rak
            WHERE 
                MONTH(tgl_mutasi) = MONTH(CURRENT_DATE())
                AND YEAR(tgl_mutasi) = YEAR(CURRENT_DATE())
                AND LEFT(no_mutasi, 2) = 'MT'
        ");

        $lokasi = DB::connection('mysql_sb')->select("
            SELECT
                idx,
                lokasi
            FROM 
                masterlokasi
        ");

        return view("whs-soljer.mutasi-rak.create", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            "subPage" => "mutasi-rak",
            "no_mutasi" => $no_mutasi,
            "lokasi" => $lokasi,
            'containerFluid' => true
        ]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $header = MutasiRak::create([
                'no_mutasi'             => $request->no_mutasi,
                'tgl_mutasi'            => $request->tgl_mutasi,
                'lokasi_tujuan'         => $request->lokasi_tujuan,
                'keterangan'            => $request->keterangan,
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);

            // PENERIMAAN
            $no_bpb_penerimaan = DB::selectOne("
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

            $headerPenerimaan = PenerimaanGudangInputan::create([
                'no_bpb'                => $no_bpb_penerimaan->kode,
                'tgl_bpb'               => $request->tgl_mutasi,
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);

            // PENGELUARAN
            $no_bpb_pengeluaran = DB::selectOne("
                SELECT 
                    CONCAT('WHS/F/OUT/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                    IF(
                        MAX(no_bpb) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                    ) AS nomor,

                    CONCAT(
                        'WHS/F/OUT/',
                        DATE_FORMAT(CURRENT_DATE(), '%m'),
                        DATE_FORMAT(CURRENT_DATE(), '%y'),
                        '/',
                        IF(
                            MAX(no_bpb) IS NULL,
                            '00001',
                            LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                        )
                    ) AS kode

                FROM pengeluaran_gudang_inputan
                WHERE 
                    MONTH(tgl_bpb) = MONTH(CURRENT_DATE())
                    AND YEAR(tgl_bpb) = YEAR(CURRENT_DATE())
                    AND LEFT(no_bpb, 3) = 'WHS'
            ");

            $headerPengeluaran = PengeluaranGudangInputan::create([
                'no_bpb'                => $no_bpb_pengeluaran->kode,
                'tgl_bpb'               => $request->tgl_mutasi,
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);

            $items = json_decode($request->items, true);

            foreach ($items as $row) {

                $mutasiDetail = MutasiRakDetail::create([
                    'mutasi_rak_id'                 => $header->id,
                    'barcode'                       => $row['barcode'],
                    'no_roll'                       => $row['no_roll'],
                    'buyer'                         => $row['buyer'],
                    'jenis_item'                    => $row['jenis_item'],
                    'warna'                         => $row['warna'],
                    'lot'                           => $row['lot'],
                    'qty'                           => $row['qty'],
                    'satuan'                        => $row['satuan'],
                    'lokasi_asal'                   => $row['lokasi_asal'],
                    'lokasi_tujuan'                 => $row['lokasi_tujuan'],
                    'keterangan'                    => $row['keterangan'],
                    "created_by"                    => $user ? $user->id : null,
                    "created_by_username"           => $user ? $user->username : null,
                    "created_at"                    => $now,
                ]);

                $this->createPenerimaan($headerPenerimaan->id, $row, $mutasiDetail->id, $user, $now);
                $this->createPengeluaran($headerPengeluaran->id, $row, $mutasiDetail->id, $user, $now);
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Mutasi Rak (FABRIC) berhasil disimpan.",
                "additional" => [],
            );

        } catch (Exception $e) {
            DB::rollBack();

            dd($e->getMessage());

            return array(
                "status" => 400,
                "message" => "Terjadi Kesalahan",
                "additional" => [],
            );
        }
    }

    private function createPenerimaan($headerId, $row, $mutasiRakDetailId, $user, $now)
    {
        $data = [
            'penerimaan_gudang_inputan_id'  => $headerId,
            'mutasi_rak_detail_id'          => $mutasiRakDetailId,
            'barcode'                       => $row['barcode'],
            'no_roll'                       => $row['no_roll'],
            'buyer'                         => $row['buyer'],
            'jenis_item'                    => $row['jenis_item'],
            'warna'                         => $row['warna'],
            'lot'                           => $row['lot'],
            'qty'                           => $row['qty'],
            'satuan'                        => $row['satuan'],
            'lokasi'                        => $row['lokasi_tujuan'],
            'keterangan'                    => $row['keterangan'],
            "created_by"                    => $user ? $user->id : null,
            "created_by_username"           => $user ? $user->username : null,
            "created_at"                    => $now,
        ];

        PenerimaanGudangInputanDetail::create($data);
        PenerimaanGudangInputanHistory::create($data);
    }

    private function createPengeluaran($headerId, $row, $mutasiRakDetailId, $user, $now)
    {
        $data = [
            'pengeluaran_gudang_inputan_id' => $headerId,
            'mutasi_rak_detail_id'          => $mutasiRakDetailId,
            'barcode'                       => $row['barcode'],
            'qty_act'                       => $row['qty'],
            'qty_out'                       => $row['qty'],
            'lokasi'                        => $row['lokasi_asal'],
            'tujuan'                        => $row['tujuan'],
            "created_by"                    => $user ? $user->id : null,
            "created_by_username"           => $user ? $user->username : null,
            "created_at"                    => $now,
        ];

        PengeluaranGudangInputanDetail::create($data);
        PengeluaranGudangInputanHistory::create($data);
    }

    public function edit($id){

        $data = MutasiRak::selectRaw("
            mutasi_rak.id,
            mutasi_rak.no_mutasi,
            DATE_FORMAT(mutasi_rak.tgl_mutasi, '%d-%m-%Y') AS tgl_mutasi,
            mutasi_rak.lokasi_tujuan,
            mutasi_rak.keterangan
        ")
        ->where("mutasi_rak.id", $id)
        ->first();

        $data_detail = MutasiRakDetail::where("mutasi_rak_id", $id)->get();

        $lokasi = DB::connection('mysql_sb')->select("
            SELECT
                idx,
                lokasi
            FROM 
                masterlokasi
        ");

        return view("whs-soljer.mutasi-rak.update", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "penerimaan-whs-soljer",
            "data" => $data,
            "data_detail" => $data_detail,
            "lokasi" => $lokasi,
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

            MutasiRak::where('id', $id)
                ->update([
                    'lokasi_tujuan' => $request->lokasi_tujuan,
                    'keterangan'    => $request->keterangan,
                    'updated_at'    => $now,
                    'updated_by'    => $user->id,
                ]);

            // Delete
            $existingIds = MutasiRakDetail::where('mutasi_rak_id', $id)
                ->pluck('id')
                ->toArray();

            $submittedIds = collect($items)->pluck('id')->toArray();

            $deletedIds = array_diff($existingIds, $submittedIds);

            if (!empty($deletedIds)) {
                MutasiRakDetail::whereIn('id', $deletedIds)->delete();
            }

            // Update and Create
            foreach ($items as $row) {
                    
                MutasiRakDetail::where('id', $row['id'])
                    ->update([
                        'lokasi_tujuan' => $row['lokasi_tujuan'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                PenerimaanGudangInputanDetail::where('mutasi_rak_detail_id', $row['id'])
                    ->update([
                        'lokasi' => $row['lokasi_tujuan'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                PenerimaanGudangInputanHistory::where('mutasi_rak_detail_id', $row['id'])
                    ->update([
                        'lokasi' => $row['lokasi_tujuan'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Mutasi Rak (FABRIC) berhasil diupdate.'
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
        $data = MutasiRak::findOrFail($id);
        $data->update(['cancel' => 1]);

        $penerimaanId = PenerimaanGudangInputanDetail::join(
            'mutasi_rak_detail',
            'mutasi_rak_detail.id',
            '=',
            'penerimaan_gudang_inputan_detail.mutasi_rak_detail_id'
        )
        ->where('mutasi_rak_detail.mutasi_rak_id', $id)
        ->value('penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id');

        if ($penerimaanId) {
            PenerimaanGudangInputan::where('id', $penerimaanId)
                ->update([
                    'cancel' => 1
                ]);
        }

        // Cancel Pengeluaran
        $pengeluaranId = PengeluaranGudangInputanDetail::join(
                'mutasi_rak_detail',
                'mutasi_rak_detail.id',
                '=',
                'pengeluaran_gudang_inputan_detail.mutasi_rak_detail_id'
            )
            ->where('mutasi_rak_detail.mutasi_rak_id', $id)
            ->value('pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id');

        if ($pengeluaranId) {
            PengeluaranGudangInputan::where('id', $pengeluaranId)
                ->update([
                    'cancel' => 1
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dicancel'
        ]);
    }

    public function printBarcode($id)
    {
        $data = MutasiRakDetail::selectRaw("
            mutasi_rak.id,
            mutasi_rak.no_mutasi,
            DATE_FORMAT(mutasi_rak.tgl_mutasi, '%d-%m-%Y') AS tgl_mutasi,
            mutasi_rak_detail.*
        ")
        ->leftJoin('mutasi_rak', 'mutasi_rak.id', '=', 'mutasi_rak_detail.mutasi_rak_id')
        ->where('mutasi_rak.id', $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.mutasi-rak.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Mutasi_Rak_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = MutasiRak::selectRaw("
            mutasi_rak.id,
            mutasi_rak.no_mutasi,
            DATE_FORMAT(mutasi_rak.tgl_mutasi, '%d-%m-%Y') AS tgl_mutasi,
            mutasi_rak.lokasi_tujuan,
            mutasi_rak.keterangan,
            mutasi_rak.created_at,
            mutasi_rak.created_by_username
        ")
        ->where("mutasi_rak.id", $id)
        ->first();

        $dataDetail = MutasiRakDetail::selectRaw('
            buyer,
            jenis_item,
            warna,
            lot,
            SUM(qty) as qty,
            satuan,
            keterangan,
            lokasi_asal,
            lokasi_tujuan
        ')
        ->where("mutasi_rak_id", $id)
        ->groupBy('buyer', 'jenis_item', 'warna', 'lot', 'satuan', 'keterangan', 'lokasi_asal', 'lokasi_tujuan')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.mutasi-rak.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Mutasi_Rak_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function getDataBarcode(Request $request)
    {
        $data = DB::table(DB::raw("
            (
                SELECT 
                    penerimaan.id,
                    penerimaan.barcode,
                    penerimaan.lokasi,
                    penerimaan.buyer,
                    penerimaan.keterangan,
                    penerimaan.jenis_item,
                    penerimaan.warna,
                    penerimaan.lot,
                    penerimaan.no_roll,
                    penerimaan.satuan,
                    pengeluaran.tujuan,
                    penerimaan.qty,
                    ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini
                FROM penerimaan_gudang_inputan_detail penerimaan
                LEFT JOIN penerimaan_gudang_inputan h ON h.id = penerimaan.penerimaan_gudang_inputan_id
                LEFT JOIN (
                        SELECT 
                            barcode,
                            tujuan,
                            SUM(qty_out) AS total_keluar
                        FROM pengeluaran_gudang_inputan_detail
                        LEFT JOIN pengeluaran_gudang_inputan ON pengeluaran_gudang_inputan.id = pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id
                WHERE pengeluaran_gudang_inputan.cancel = '0'
                        GROUP BY barcode
                ) pengeluaran ON pengeluaran.barcode = penerimaan.barcode
                WHERE h.cancel = '0'
                AND ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) > 0
            ) as results
        "))
        ->where('barcode', $request->barcode)
        ->first();

        if (!$data) {
            return response()->json([
                'status' => 404,
                'message' => 'Data barcode $request->barcode tidak ditemukan'
            ]);
        }

        return response()->json([
            'id' => $data->id,
            'barcode' => $data->barcode,
            'no_roll' => $data->no_roll,
            'buyer' => $data->buyer,
            'jenis_item' => $data->jenis_item,
            'warna' => $data->warna,
            'lot' => $data->lot,
            'qty' => $data->qty_saat_ini,
            'satuan' => $data->satuan,
            'tujuan' => $data->tujuan,
            'lokasi' => $data->lokasi,
            'keterangan' => $data->keterangan,
        ]);
    }
}
