<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputanAccesories;
use App\Models\WhsSoljer\PengeluaranGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputanAccesoriesHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PengeluaranGudangInputanAccesoriesController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = PengeluaranGudangInputanAccesories::selectRaw("
                pengeluaran_gudang_inputan_accesories.id,
                pengeluaran_gudang_inputan_accesories.no_bpb,
                DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                COALESCE(SUM(pengeluaran_gudang_inputan_accesories_detail.qty_out),0) as total_qty,
                COALESCE(SUM(pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out),0) as total_qty_kgm,
                pengeluaran_gudang_inputan_accesories.created_by_username,
                pengeluaran_gudang_inputan_accesories.cancel,
                CASE 
                    WHEN pengeluaran_gudang_inputan_accesories.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status
            ")
            ->leftJoin("pengeluaran_gudang_inputan_accesories_detail", "pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id", "=", "pengeluaran_gudang_inputan_accesories.id")
            ->groupBy(
                "pengeluaran_gudang_inputan_accesories.id",
                "pengeluaran_gudang_inputan_accesories.no_bpb",
                "pengeluaran_gudang_inputan_accesories.tgl_bpb",
                "pengeluaran_gudang_inputan_accesories.created_by_username",
                "pengeluaran_gudang_inputan_accesories.cancel"
            );

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("pengeluaran_gudang_inputan_accesories.tgl_bpb >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("pengeluaran_gudang_inputan_accesories.tgl_bpb <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->whereRaw("
                    CASE 
                        WHEN pengeluaran_gudang_inputan_accesories.cancel = 1 THEN 'Cancel'
                        ELSE 'Draft'
                    END LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty_out)
                        FROM pengeluaran_gudang_inputan_accesories_detail detail
                        WHERE detail.pengeluaran_gudang_inputan_accesories_id = pengeluaran_gudang_inputan_accesories.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty_kgm', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty_kgm_out)
                        FROM pengeluaran_gudang_inputan_accesories_detail detail
                        WHERE detail.pengeluaran_gudang_inputan_accesories_id = pengeluaran_gudang_inputan_accesories.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('pengeluaran_gudang_inputan_accesories.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.pengeluaran-gudang-inputan-accesories.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "pengeluaran-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_bpb = DB::selectOne("
            SELECT 
                CONCAT('WHS/A/OUT/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                IF(
                    MAX(no_bpb) IS NULL,
                    '00001',
                    LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                ) AS nomor,

                CONCAT(
                    'WHS/A/OUT/',
                    DATE_FORMAT(CURRENT_DATE(), '%m'),
                    DATE_FORMAT(CURRENT_DATE(), '%y'),
                    '/',
                    IF(
                        MAX(no_bpb) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_bpb, 5)) + 1, 5, 0)
                    )
                ) AS kode

            FROM pengeluaran_gudang_inputan_accesories
            WHERE 
                MONTH(tgl_bpb) = MONTH(CURRENT_DATE())
                AND YEAR(tgl_bpb) = YEAR(CURRENT_DATE())
                AND LEFT(no_bpb, 3) = 'WHS'
        ");

        return view("whs-soljer.pengeluaran-gudang-inputan-accesories.create", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "pengeluaran-whs-soljer",
            "no_bpb" => $no_bpb,
            'containerFluid' => true
        ]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $header = PengeluaranGudangInputanAccesories::create([
                'no_bpb'                => $request->no_bpb,
                'tgl_bpb'               => date('Y-m-d'),
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);


            $items = json_decode($request->items, true);
            foreach ($items as $row) {
                PengeluaranGudangInputanAccesoriesDetail::create([
                    'pengeluaran_gudang_inputan_accesories_id' => $header->id,
                    'barcode'                          => $row['barcode'],
                    'qty_act'                          => $row['qty'],
                    'qty_out'                          => $row['qty_out'],
                    'qty_kgm_act'                      => $row['qty_kgm'],
                    'qty_kgm_out'                      => $row['qty_kgm_out'],
                    "created_by"                       => $user ? $user->id : null,
                    "created_by_username"              => $user ? $user->username : null,
                    "created_at"                       => $now,
                ]);

                PengeluaranGudangInputanAccesoriesHistory::create([
                    'pengeluaran_gudang_inputan_accesories_id' => $header->id,
                    'barcode'                          => $row['barcode'],
                    'qty_act'                          => $row['qty'],
                    'qty_out'                          => $row['qty_out'],
                    'qty_kgm_act'                      => $row['qty_kgm'],
                    'qty_kgm_out'                      => $row['qty_kgm_out'],
                    "created_by"                       => $user ? $user->id : null,
                    "created_by_username"              => $user ? $user->username : null,
                    "created_at"                       => $now,
                ]);
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Pengeluaran Gudang Inputan (ACCESORIES) berhasil disimpan.",
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

        $data = PengeluaranGudangInputanAccesories::selectRaw("
            pengeluaran_gudang_inputan_accesories.id,
            pengeluaran_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb
        ")
        ->where("pengeluaran_gudang_inputan_accesories.id", $id)
        ->first();


        $data_detail = PengeluaranGudangInputanAccesoriesDetail::selectRaw("
            pengeluaran_gudang_inputan_accesories_detail.id,
            pengeluaran_gudang_inputan_accesories_detail.barcode,
            penerimaan_gudang_inputan_accesories_detail.no_box,
            penerimaan_gudang_inputan_accesories_detail.buyer,
            penerimaan_gudang_inputan_accesories_detail.worksheet,
            penerimaan_gudang_inputan_accesories_detail.nama_barang,
            penerimaan_gudang_inputan_accesories_detail.kode,
            penerimaan_gudang_inputan_accesories_detail.warna,
            penerimaan_gudang_inputan_accesories_detail.size,
            pengeluaran_gudang_inputan_accesories_detail.qty_act,
            penerimaan_gudang_inputan_accesories_detail.satuan,
            pengeluaran_gudang_inputan_accesories_detail.qty_kgm_act,
            penerimaan_gudang_inputan_accesories_detail.keterangan,
            penerimaan_gudang_inputan_accesories_detail.lokasi,
            pengeluaran_gudang_inputan_accesories_detail.qty_out,
            pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out
        ")
        ->lefTJoin("penerimaan_gudang_inputan_accesories_detail", "penerimaan_gudang_inputan_accesories_detail.barcode", "=", "pengeluaran_gudang_inputan_accesories_detail.barcode")
        ->where("pengeluaran_gudang_inputan_accesories_id", $id)
        ->get();

        return view("whs-soljer.pengeluaran-gudang-inputan-accesories.update", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "pengeluaran-whs-soljer",
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
            $existingIds = PengeluaranGudangInputanAccesoriesDetail::where('pengeluaran_gudang_inputan_accesories_id', $id)
                ->pluck('id')
                ->toArray();

            $submittedIds = collect($items)->pluck('id')->toArray();

            $deletedIds = array_diff($existingIds, $submittedIds);

            if (!empty($deletedIds)) {
                PengeluaranGudangInputanAccesoriesDetail::whereIn('id', $deletedIds)->delete();
            }

            // Update and Create
            foreach ($items as $row) {
                $dataDetail = PengeluaranGudangInputanAccesoriesDetail::find($row['id']);

                $oldQty = (float) $dataDetail->qty_out;
                $newQty = (float) $row['qty_out'];

                $oldKgm = (float) $dataDetail->qty_kgm_out;
                $newKgm = (float) $row['qty_kgm_out'];

                if ($oldQty != $newQty || $oldKgm != $newKgm) {
                    PengeluaranGudangInputanAccesoriesHistory::create([
                        'pengeluaran_gudang_inputan_accesories_id' => $dataDetail->pengeluaran_gudang_inputan_accesories_id,
                        'barcode'                          => $dataDetail->barcode,
                        'qty_act'                          => $dataDetail->qty_act,
                        'qty_out'                          => $row['qty_out'],
                        'qty_kgm_act'                      => $dataDetail->qty_kgm_act,
                        'qty_kgm_out'                      => $row['qty_kgm_out'],
                        "created_by"                       => $user ? $user->id : null,
                        "created_by_username"              => $user ? $user->username : null,
                        "created_at"                       => $now,
                    ]);

                    PengeluaranGudangInputanAccesoriesDetail::where('id', $row['id'])
                        ->update([
                            'qty_out' => $row['qty_out'],
                            'qty_kgm_out' => $row['qty_kgm_out'],
                            'updated_at' => now(),
                            'updated_by' => auth()->id(),
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Pengeluaran Gudang Inputan (ACCESORIES) berhasil diupdate.'
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
        $data = PengeluaranGudangInputanAccesories::findOrFail($id);

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
        $data = PengeluaranGudangInputanAccesories::selectRaw("
            pengeluaran_gudang_inputan_accesories.id,
            pengeluaran_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_accesories_detail.barcode,
            penerimaan_gudang_inputan_accesories_detail.no_box,
            penerimaan_gudang_inputan_accesories_detail.buyer,
            penerimaan_gudang_inputan_accesories_detail.worksheet,
            penerimaan_gudang_inputan_accesories_detail.nama_barang,
            penerimaan_gudang_inputan_accesories_detail.kode,
            penerimaan_gudang_inputan_accesories_detail.warna,
            penerimaan_gudang_inputan_accesories_detail.size,
            pengeluaran_gudang_inputan_accesories_detail.qty_act,
            penerimaan_gudang_inputan_accesories_detail.satuan,
            pengeluaran_gudang_inputan_accesories_detail.qty_kgm_act,
            penerimaan_gudang_inputan_accesories_detail.keterangan,
            penerimaan_gudang_inputan_accesories_detail.lokasi,
            pengeluaran_gudang_inputan_accesories_detail.qty_out,
            pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out
        ")
        ->leftJoin('pengeluaran_gudang_inputan_accesories_detail', 'pengeluaran_gudang_inputan_accesories.id', '=', 'pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id')
        ->leftJoin('penerimaan_gudang_inputan_accesories_detail', 'penerimaan_gudang_inputan_accesories_detail.barcode', '=', 'pengeluaran_gudang_inputan_accesories_detail.barcode')
        ->where("pengeluaran_gudang_inputan_accesories.id", $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.pengeluaran-gudang-inputan-accesories.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Pengeluaran_Gudang_Inputan_Accesories_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = PengeluaranGudangInputanAccesories::selectRaw("
            pengeluaran_gudang_inputan_accesories.id,
            pengeluaran_gudang_inputan_accesories.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            pengeluaran_gudang_inputan_accesories.created_at,
            pengeluaran_gudang_inputan_accesories.created_by_username
        ")
        ->where("pengeluaran_gudang_inputan_accesories.id", $id)
        ->first();

        $dataDetail = PengeluaranGudangInputanAccesoriesDetail::selectRaw('
            penerimaan_gudang_inputan_accesories_detail.buyer,
            penerimaan_gudang_inputan_accesories_detail.worksheet,
            penerimaan_gudang_inputan_accesories_detail.nama_barang,
            penerimaan_gudang_inputan_accesories_detail.kode,
            penerimaan_gudang_inputan_accesories_detail.warna,
            penerimaan_gudang_inputan_accesories_detail.size,
            SUM(pengeluaran_gudang_inputan_accesories_detail.qty_act) as qty_act,
            SUM(pengeluaran_gudang_inputan_accesories_detail.qty_out) as qty_out,
            SUM(pengeluaran_gudang_inputan_accesories_detail.qty_kgm_act) as qty_kgm_act,
            SUM(pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out) as qty_kgm_out,
            penerimaan_gudang_inputan_accesories_detail.satuan,
            penerimaan_gudang_inputan_accesories_detail.keterangan,
            penerimaan_gudang_inputan_accesories_detail.lokasi
        ')
        ->leftJoin("penerimaan_gudang_inputan_accesories_detail", "penerimaan_gudang_inputan_accesories_detail.barcode", "=", "pengeluaran_gudang_inputan_accesories_detail.barcode")
        ->where("pengeluaran_gudang_inputan_accesories_id", $id)
        ->groupBy('buyer', 'worksheet', 'nama_barang', 'kode', 'warna', 'size', 'satuan', 'keterangan', 'lokasi')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.pengeluaran-gudang-inputan-accesories.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Pengeluaran_Gudang_Inputan_Accesories_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function getDataBarcode(Request $request)
    {
        $data = PenerimaanGudangInputanAccesoriesDetail::select("penerimaan_gudang_inputan_accesories_detail.*")
            ->leftJoin("penerimaan_gudang_inputan_accesories", "penerimaan_gudang_inputan_accesories.id", "=", "penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id")
            ->where('penerimaan_gudang_inputan_accesories_detail.barcode', $request->barcode)
            ->where('penerimaan_gudang_inputan_accesories.cancel', 0)
            ->first();

        if (!$data) {
            return response()->json(['status' => 404]);
        }

        $qty_out = PengeluaranGudangInputanAccesoriesDetail::selectRaw('COALESCE(SUM(pengeluaran_gudang_inputan_accesories_detail.qty_out),0) as total')
            ->leftJoin(
                'pengeluaran_gudang_inputan_accesories',
                'pengeluaran_gudang_inputan_accesories.id',
                '=',
                'pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id'
            )
            ->where('pengeluaran_gudang_inputan_accesories_detail.barcode', $request->barcode)
            ->where('pengeluaran_gudang_inputan_accesories.cancel', 0)
            ->value('total');

        $qty_sisa = max(0, $data->qty - $qty_out);

        if ($qty_sisa <= 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Stok sudah habis'
            ]);
        }

        $qty_kgm_out = PengeluaranGudangInputanAccesoriesDetail::selectRaw('COALESCE(SUM(pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out),0) as total')
            ->leftJoin(
                'pengeluaran_gudang_inputan_accesories',
                'pengeluaran_gudang_inputan_accesories.id',
                '=',
                'pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id'
            )
            ->where('pengeluaran_gudang_inputan_accesories_detail.barcode', $request->barcode)
            ->where('pengeluaran_gudang_inputan_accesories.cancel', 0)
            ->value('total');

        $qty_kgm_sisa = max(0, $data->qty_kgm - $qty_kgm_out);

        if ($qty_kgm_sisa <= 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Stok KGM sudah habis'
            ]);
        }

        return response()->json([
            'id' => $data->id,
            'barcode' => $data->barcode,
            'no_box' => $data->no_box,
            'buyer' => $data->buyer,
            'worksheet' => $data->worksheet,
            'nama_barang' => $data->nama_barang,
            'kode' => $data->kode,
            'warna' => $data->warna,
            'size' => $data->size,
            'qty' => $qty_sisa,
            'satuan' => $data->satuan,
            'qty_kgm' => $qty_kgm_sisa,
            'lokasi' => $data->lokasi,
            'keterangan' => $data->keterangan,
        ]);
    }
}
