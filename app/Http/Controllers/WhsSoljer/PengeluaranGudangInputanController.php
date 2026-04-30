<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputan;
use App\Models\WhsSoljer\PengeluaranGudangInputanDetail;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class PengeluaranGudangInputanController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = PengeluaranGudangInputan::selectRaw("
                pengeluaran_gudang_inputan.id,
                pengeluaran_gudang_inputan.no_bpb,
                DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                COALESCE(SUM(pengeluaran_gudang_inputan_detail.qty_out),0) as total_qty,
                pengeluaran_gudang_inputan.created_by_username,
                pengeluaran_gudang_inputan.cancel,
                CASE 
                    WHEN pengeluaran_gudang_inputan.cancel = 1 THEN 'Cancel'
                    ELSE 'Draft'
                END as status
            ")
            ->leftJoin("pengeluaran_gudang_inputan_detail", "pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id", "=", "pengeluaran_gudang_inputan.id")
            ->groupBy(
                "pengeluaran_gudang_inputan.id",
                "pengeluaran_gudang_inputan.no_bpb",
                "pengeluaran_gudang_inputan.tgl_bpb",
                "pengeluaran_gudang_inputan.created_by_username",
                "pengeluaran_gudang_inputan.cancel"
            );

            return DataTables::eloquent($data)->filter(function ($query) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if ($tglAwal) {
                    $query->whereRaw("pengeluaran_gudang_inputan.tgl_bpb >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("pengeluaran_gudang_inputan.tgl_bpb <= '" . $tglAkhir . "'");
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) {
                $query->whereRaw("
                    DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d/%m/%Y') LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->whereRaw("
                    CASE 
                        WHEN pengeluaran_gudang_inputan.cancel = 1 THEN 'Cancel'
                        ELSE 'Draft'
                    END LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->filterColumn('total_qty', function($query, $keyword) {
                $query->whereRaw("
                    (
                        SELECT SUM(detail.qty_out)
                        FROM pengeluaran_gudang_inputan_detail detail
                        WHERE detail.pengeluaran_gudang_inputan_id = pengeluaran_gudang_inputan.id
                    ) LIKE ?
                ", ["%{$keyword}%"]);
            })
            ->order(function ($query) {
                $query->orderBy('pengeluaran_gudang_inputan.created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.pengeluaran-gudang-inputan.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "pengeluaran-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function create(){

        $no_bpb = DB::selectOne("
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

        return view("whs-soljer.pengeluaran-gudang-inputan.create", [
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

            $header = PengeluaranGudangInputan::create([
                'no_bpb'                => $request->no_bpb,
                'tgl_bpb'               => date('Y-m-d'),
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);


            $items = json_decode($request->items, true);
            foreach ($items as $row) {
                PengeluaranGudangInputanDetail::create([
                    'pengeluaran_gudang_inputan_id' => $header->id,
                    'barcode'                       => $row['barcode'],
                    'qty_act'                       => $row['qty'],
                    'qty_out'                       => $row['qty_out'],
                    "created_by"                    => $user ? $user->id : null,
                    "created_by_username"           => $user ? $user->username : null,
                    "created_at"                    => $now,
                ]);
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Pengeluaran Gudang Inputan (FABRIC) berhasil disimpan.",
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

        $data = PengeluaranGudangInputan::selectRaw("
            pengeluaran_gudang_inputan.id,
            pengeluaran_gudang_inputan.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb
        ")
        ->where("pengeluaran_gudang_inputan.id", $id)
        ->first();


        $data_detail = PengeluaranGudangInputanDetail::selectRaw("
            pengeluaran_gudang_inputan_detail.id,
            pengeluaran_gudang_inputan_detail.barcode,
            penerimaan_gudang_inputan_detail.lokasi,
            penerimaan_gudang_inputan_detail.buyer,
            penerimaan_gudang_inputan_detail.keterangan,
            penerimaan_gudang_inputan_detail.jenis_item,
            penerimaan_gudang_inputan_detail.warna,
            penerimaan_gudang_inputan_detail.lot,
            penerimaan_gudang_inputan_detail.no_roll,
            pengeluaran_gudang_inputan_detail.qty_act,
            penerimaan_gudang_inputan_detail.satuan,
            pengeluaran_gudang_inputan_detail.qty_out
        ")
        ->lefTJoin("penerimaan_gudang_inputan_detail", "penerimaan_gudang_inputan_detail.barcode", "=", "pengeluaran_gudang_inputan_detail.barcode")
        ->where("pengeluaran_gudang_inputan_id", $id)
        ->get();

        return view("whs-soljer.pengeluaran-gudang-inputan.update", [
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

            $items = json_decode($request->items, true);

            if (!$items || !is_array($items)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Data items kosong / tidak valid'
                ]);
            }

            foreach ($items as $row) {
                PengeluaranGudangInputanDetail::where('id', $row['id'])
                    ->update([
                        'qty_out' => $row['qty_out'],
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data Pengeluaran Gudang Inputan (FABRIC) berhasil diupdate.'
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
        $data = PengeluaranGudangInputan::findOrFail($id);

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
        $data = PengeluaranGudangInputan::selectRaw("
            pengeluaran_gudang_inputan.id,
            pengeluaran_gudang_inputan.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            penerimaan_gudang_inputan_detail.barcode,
            penerimaan_gudang_inputan_detail.lokasi,
            penerimaan_gudang_inputan_detail.buyer,
            penerimaan_gudang_inputan_detail.keterangan,
            penerimaan_gudang_inputan_detail.jenis_item,
            penerimaan_gudang_inputan_detail.warna,
            penerimaan_gudang_inputan_detail.lot,
            penerimaan_gudang_inputan_detail.no_roll,
            pengeluaran_gudang_inputan_detail.qty_act,
            penerimaan_gudang_inputan_detail.satuan,
            pengeluaran_gudang_inputan_detail.qty_out
        ")
        ->leftJoin('pengeluaran_gudang_inputan_detail', 'pengeluaran_gudang_inputan.id', '=', 'pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id')
        ->leftJoin('penerimaan_gudang_inputan_detail', 'penerimaan_gudang_inputan_detail.barcode', '=', 'pengeluaran_gudang_inputan_detail.barcode')
        ->where("pengeluaran_gudang_inputan.id", $id)
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.pengeluaran-gudang-inputan.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Pengeluaran_Gudang_Inputan_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function printSj($id)
    {
        $dataHeader = PengeluaranGudangInputan::selectRaw("
            pengeluaran_gudang_inputan.id,
            pengeluaran_gudang_inputan.no_bpb,
            DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            pengeluaran_gudang_inputan.created_at,
            pengeluaran_gudang_inputan.created_by_username
        ")
        ->where("pengeluaran_gudang_inputan.id", $id)
        ->first();

        $dataDetail = PengeluaranGudangInputanDetail::selectRaw('
            penerimaan_gudang_inputan_detail.buyer,
            penerimaan_gudang_inputan_detail.jenis_item,
            penerimaan_gudang_inputan_detail.warna,
            penerimaan_gudang_inputan_detail.lot,
            SUM(pengeluaran_gudang_inputan_detail.qty_act) as qty_act,
            SUM(pengeluaran_gudang_inputan_detail.qty_out) as qty_out,
            penerimaan_gudang_inputan_detail.satuan,
            penerimaan_gudang_inputan_detail.keterangan,
            penerimaan_gudang_inputan_detail.lokasi
        ')
        ->leftJoin("penerimaan_gudang_inputan_detail", "penerimaan_gudang_inputan_detail.barcode", "=", "pengeluaran_gudang_inputan_detail.barcode")
        ->where("pengeluaran_gudang_inputan_id", $id)
        ->groupBy('buyer', 'jenis_item', 'warna', 'lot', 'satuan', 'keterangan', 'lokasi')
        ->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.pengeluaran-gudang-inputan.print-sj', ["dataHeader" => $dataHeader, "dataDetail" => $dataDetail])->setPaper('a4', 'potrait');

        $fileName = 'Pengeluaran_Gudang_Inputan_Fabric_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function getDataBarcode(Request $request)
    {
        $data = PenerimaanGudangInputanDetail::select("penerimaan_gudang_inputan_detail.*")
            ->leftJoin("penerimaan_gudang_inputan", "penerimaan_gudang_inputan.id", "=", "penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id")
            ->where('penerimaan_gudang_inputan_detail.barcode', $request->barcode)
            ->where('penerimaan_gudang_inputan.cancel', 0)
            ->first();

        if (!$data) {
            return response()->json(['status' => 404]);
        }

        $qty_out = PengeluaranGudangInputanDetail::selectRaw('COALESCE(SUM(pengeluaran_gudang_inputan_detail.qty_out),0) as total')
            ->leftJoin(
                'pengeluaran_gudang_inputan',
                'pengeluaran_gudang_inputan.id',
                '=',
                'pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id'
            )
            ->where('pengeluaran_gudang_inputan_detail.barcode', $request->barcode)
            ->where('pengeluaran_gudang_inputan.cancel', 0)
            ->value('total');

        $qty_sisa = max(0, $data->qty - $qty_out);

        if ($qty_sisa <= 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Stok sudah habis'
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
            'qty' => $qty_sisa,
            'satuan' => $data->satuan,
            'lokasi' => $data->lokasi,
            'keterangan' => $data->keterangan,
        ]);
    }
}
