<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanFGStokMutasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportListLaporanPenerimaanFGStockBPB;
use Illuminate\Http\JsonResponse;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use QrCode;
use PDF;

class FGStokOpnameController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data = DB::select("
                SELECT
                    h.no_opname,
                    h.tgl_opname,
                    CONCAT(
                        DATE_FORMAT(h.tgl_opname, '%d'), '-',
                        LEFT(DATE_FORMAT(h.tgl_opname, '%M'), 3), '-',
                        DATE_FORMAT(h.tgl_opname, '%Y')
                    ) AS tgl_opname_fix,
                    h.periode,
                    DATE_FORMAT(STR_TO_DATE(CONCAT(h.periode, '-01'), '%Y-%m-%d'), '%M %Y') AS periode_fix,
                    h.no_carton,
                    h.no_pallet,
                    h.status,
                    IFNULL(SUM(d.qty), 0) total_qty
                FROM fg_stok_opname_header h
                LEFT JOIN fg_stok_opname_detail d
                    ON d.no_opname = h.no_opname AND (d.cancel = 'N' OR d.cancel IS NULL)
                WHERE h.tgl_opname BETWEEN ? AND ?
                    AND (h.cancel = 'N' OR h.cancel IS NULL)
                GROUP BY h.no_opname, h.tgl_opname, h.periode, h.no_carton, h.no_pallet, h.status
                ORDER BY h.tgl_opname DESC, h.no_opname DESC
            ", [$tgl_awal, $tgl_akhir]);

            return DataTables::of($data)->toJson();
        }

        return view('fg-stock.opname_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-opname", "subPage" => "opname-fg-stock"]);
    }

    public function exportExcel(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        $data = DB::select("
            SELECT
                h.no_opname,
                h.tgl_opname,
                h.periode,
                h.no_carton,
                h.no_pallet,
                h.status,
                m.buyer,
                m.ws,
                m.styleno,
                m.dest,
                m.color,
                m.size,
                d.grade,
                d.qty
            FROM fg_stok_opname_header h
            JOIN fg_stok_opname_detail d ON d.no_opname = h.no_opname AND (d.cancel = 'N' OR d.cancel IS NULL)
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            LEFT JOIN master_size_new ms ON ms.size = m.size
            WHERE h.tgl_opname BETWEEN ? AND ?
                AND (h.cancel = 'N' OR h.cancel IS NULL)
            ORDER BY h.tgl_opname DESC, h.no_opname DESC, ms.urutan ASC, d.id ASC
        ", [$tgl_awal, $tgl_akhir]);

        $fileName = 'laporan-opname-fg-stock';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan Opname FG Stock'],
            [
                'font-style' => 'bold',
                'font-size' => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tgl_awal . ' s/d ' . $tgl_akhir],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
            'No. Opname',
            'Tgl. Opname',
            'Periode',
            'No. Carton',
            'No. Pallet',
            'Status',
            'Buyer',
            'WS',
            'Style',
            'Dest',
            'Color',
            'Size',
            'Grade',
            'Qty',
        ], [
            'font-style' => 'bold',
            'border' => 'thin',
            'halign' => 'center',
            'fill' => '#F4F6FB',
        ]);

        foreach ($data as $row) {
            $sheet->writeRow([
                $row->no_opname ?? '',
                $row->tgl_opname ?? '',
                $row->periode ?? '',
                $row->no_carton ?? '',
                $row->no_pallet ?? '',
                $row->status ?? '',
                $row->buyer ?? '',
                $row->ws ?? '',
                $row->styleno ?? '',
                $row->dest ?? '',
                $row->color ?? '',
                $row->size ?? '',
                $row->grade ?? '',
                (float) ($row->qty ?? 0),
            ], [
                'border' => 'thin',
            ]);
        }

        foreach (range('A', 'N') as $col) {
            $sheet->setColWidth($col, 18);
        }

        return $excel->download();
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        return view('fg-stock.create_opname_fg_stock', [
            'page' => 'dashboard-fg-stock',
            "subPageGroup" => "fgstock-opname",
            "subPage" => "opname-fg-stock",
            "user" => $user,
        ]);
    }

    public function printQr(Request $request)
    {
        $no_carton = $request->no_carton;
        $periode = $request->periode;

        if (!$no_carton) {
            abort(404);
        }

        $header = DB::select("
            SELECT no_pallet FROM fg_stok_opname_header
            WHERE no_carton = ? AND periode = ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$no_carton, $periode]);

        $no_pallet = count($header) > 0 ? $header[0]->no_pallet : null;

        $pdf = PDF::loadView('fg-stock.print_qr_opname_fg_stock', [
            'no_carton' => $no_carton,
            'no_pallet' => $no_pallet,
        ])->setPaper([0, 0, 180, 210]);

        return $pdf->stream('QR-' . $no_carton . '.pdf');
    }

    public function getMasterCarton(Request $request)
    {
        $data = DB::select("
            SELECT no_carton FROM fg_stok_master_carton WHERE cancel = 'N' ORDER BY no_carton
        ");

        return response()->json($data);
    }

    public function getOpnameItems(Request $request)
    {
        $request->validate([
            'no_carton' => 'required|string',
            'periode' => 'required|string',
        ]);

        $header = DB::select("
            SELECT no_opname, status, tgl_opname, no_pallet FROM fg_stok_opname_header
            WHERE periode = ? AND no_carton = ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$request->periode, $request->no_carton]);

        $data = [];

        if (count($header) > 0) {
            $data = DB::select("
                SELECT d.id id_detail, d.qty, d.grade, m.buyer, m.ws, m.styleno, m.dest, m.color, m.size
                FROM fg_stok_opname_detail d
                JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
                WHERE d.no_opname = ?
                AND (d.cancel = 'N' OR d.cancel IS NULL)
                ORDER BY d.id
            ", [$header[0]->no_opname]);
        }

        return response()->json([
            'no_opname' => count($header) > 0 ? $header[0]->no_opname : null,
            'status' => count($header) > 0 ? $header[0]->status : null,
            'tgl_opname' => count($header) > 0 ? $header[0]->tgl_opname : null,
            'no_pallet' => count($header) > 0 ? $header[0]->no_pallet : null,
            'items' => $data,
        ]);
    }

    public function storeMasterCarton(Request $request)
    {
        $request->validate([
            'no_carton' => 'required|string|max:255',
        ]);

        $no_carton = strtoupper(preg_replace('/\s+/', '', $request->no_carton));

        if ($no_carton === '') {
            return response()->json(['message' => 'No. Carton belum diisi!'], 422);
        }

        $exists = DB::select("
            SELECT 1 FROM fg_stok_master_carton WHERE no_carton = ? and cancel = 'N'
        ", [$no_carton]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'No. Carton sudah ada '], 422);
        }

        DB::insert("
            INSERT INTO fg_stok_master_carton (no_carton, cancel, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?)
        ", [$no_carton, 'N', Auth::user()->name, Carbon::now(), Carbon::now()]);

        return response()->json(['message' => 'No. Carton berhasil ditambahkan.']);
    }

    public function getBuyer(Request $request)
    {
        $data = DB::select("
            SELECT buyer FROM master_sb_ws WHERE buyer IS NOT NULL GROUP BY buyer ORDER BY buyer
        ");

        return response()->json($data);
    }

    public function getWs(Request $request)
    {
        $request->validate([
            'buyer' => 'nullable|string',
        ]);

        if ($request->buyer) {
            $data = DB::select("
                SELECT ws FROM master_sb_ws WHERE buyer = ? AND ws IS NOT NULL GROUP BY ws ORDER BY ws
            ", [$request->buyer]);
        } else {
            $data = DB::select("
                SELECT ws FROM master_sb_ws WHERE ws IS NOT NULL GROUP BY ws ORDER BY ws
            ");
        }

        return response()->json($data);
    }

    public function getBuyerByWs(Request $request)
    {
        $request->validate([
            'ws' => 'required|string',
        ]);

        $data = DB::select("
            SELECT buyer FROM master_sb_ws WHERE ws = ? AND buyer IS NOT NULL LIMIT 1
        ", [$request->ws]);

        return response()->json([
            'buyer' => count($data) > 0 ? $data[0]->buyer : null,
        ]);
    }

    public function getStyle(Request $request)
    {
        $request->validate([
            'buyer' => 'required|string',
            'ws' => 'required|string',
        ]);

        $data = DB::select("
            SELECT styleno FROM master_sb_ws
            WHERE buyer = ? AND ws = ? AND styleno IS NOT NULL
            GROUP BY styleno ORDER BY styleno
        ", [$request->buyer, $request->ws]);

        return response()->json($data);
    }

    public function getDest(Request $request)
    {
        $request->validate([
            'buyer' => 'required|string',
            'ws' => 'required|string',
            'styleno' => 'required|string',
        ]);

        $data = DB::select("
            SELECT dest FROM master_sb_ws
            WHERE buyer = ? AND ws = ? AND styleno = ?
            GROUP BY dest ORDER BY dest
        ", [$request->buyer, $request->ws, $request->styleno]);

        return response()->json($data);
    }

    public function getColor(Request $request)
    {
        $request->validate([
            'buyer' => 'required|string',
            'ws' => 'required|string',
            'styleno' => 'required|string',
            'dest' => 'nullable|string',
        ]);

        $data = DB::select("
            SELECT color FROM master_sb_ws
            WHERE buyer = ? AND ws = ? AND styleno = ? AND dest = ? AND color IS NOT NULL
            GROUP BY color ORDER BY color
        ", [$request->buyer, $request->ws, $request->styleno, $request->dest ?? '']);

        return response()->json($data);
    }

    public function getSize(Request $request)
    {
        $request->validate([
            'ws' => 'required|string',
            'styleno' => 'required|string',
            'dest' => 'nullable|string',
            'color' => 'required|string',
        ]);

        $data = DB::select("
            SELECT m.size, MIN(m.id_so_det) id_so_det
            FROM master_sb_ws m
            LEFT JOIN master_size_new ms ON ms.size = m.size
            WHERE m.ws = ? AND m.styleno = ? AND m.dest = ? AND m.color = ? AND m.size IS NOT NULL
            GROUP BY m.size
            ORDER BY ms.urutan ASC, m.size ASC
        ", [$request->ws, $request->styleno, $request->dest ?? '', $request->color]);

        return response()->json($data);
    }

    public function getGrade(Request $request)
    {
        $data = DB::select("
            SELECT grade FROM fg_stok_master_grade WHERE cancel = 'N' ORDER BY grade
        ");

        return response()->json($data);
    }

    public function storeOpname(Request $request)
    {
        $request->validate([
            'no_carton' => 'required|string',
            'periode' => 'required|string',
            'id_so_det' => 'required',
            'qty' => 'required|numeric|min:1',
            'grade' => 'required|string',
        ]);

        $no_carton = $request->no_carton;
        $periode = $request->periode;
        $user = Auth::user()->name;
        $now = Carbon::now();

        $header = DB::select("
            SELECT no_opname, status FROM fg_stok_opname_header
            WHERE periode = ? AND no_carton = ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$periode, $no_carton]);

        if (count($header) > 0) {
            if ($header[0]->status === 'CLOSED') {
                return response()->json(['message' => 'Opname untuk carton ini sudah CLOSED, tidak bisa menambah item!'], 422);
            }

            $no_opname = $header[0]->no_opname;
            $status = $header[0]->status;
        } else {
            $request->validate([
                'tgl_opname' => 'required|date',
                'no_pallet' => 'required|string',
            ]);

            if (date('Y-m', strtotime($request->tgl_opname)) !== $periode) {
                return response()->json(['message' => 'Tgl. Opname harus dalam bulan periode yang dipilih!'], 422);
            }

            $counter = DB::select("
                SELECT IF(MAX(no_opname) IS NULL, 1, MAX(RIGHT(no_opname, 5)) + 1) nomor
                FROM fg_stok_opname_header
                WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                AND LEFT(no_opname, 3) = 'OPN'
            ");

            $no_opname = 'OPN' . date('ym') . str_pad($counter[0]->nomor, 5, '0', STR_PAD_LEFT);

            DB::insert("
                INSERT INTO fg_stok_opname_header (no_opname, tgl_opname, periode, no_carton, no_pallet, cancel, status, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'N', 'OPEN', ?, ?, ?)
            ", [$no_opname, $request->tgl_opname, $periode, $no_carton, $request->no_pallet, $user, $now, $now]);

            $status = 'OPEN';
        }

        $exists = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND id_so_det = ? AND grade = ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$no_opname, $request->id_so_det, $request->grade]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'Item dengan Grade ini sudah ada di carton, gunakan tombol Update untuk mengubah qty-nya!'], 422);
        }

        DB::insert("
            INSERT INTO fg_stok_opname_detail (no_opname, id_so_det, qty, grade, cancel, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'N', ?, ?, ?)
        ", [$no_opname, $request->id_so_det, $request->qty, $request->grade, $user, $now, $now]);

        $id_detail = DB::getPdo()->lastInsertId();

        return response()->json([
            'message' => 'Item berhasil disimpan.',
            'no_opname' => $no_opname,
            'status' => $status,
            'id_detail' => $id_detail,
        ]);
    }

    public function updateOpnameItem(Request $request)
    {
        $request->validate([
            'id_detail' => 'required|integer',
            'qty' => 'required|numeric|min:1',
            'grade' => 'required|string',
        ]);

        $header = DB::select("
            SELECT h.status, d.no_opname, d.id_so_det FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            WHERE d.id = ?
            LIMIT 1
        ", [$request->id_detail]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Item tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname untuk carton ini sudah CLOSED, tidak bisa mengubah item!'], 422);
        }

        $exists = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND id_so_det = ? AND grade = ? AND id != ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$header[0]->no_opname, $header[0]->id_so_det, $request->grade, $request->id_detail]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'Item dengan Grade ini sudah ada di carton!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET qty = ?, grade = ?, updated_at = ? WHERE id = ?
        ", [$request->qty, $request->grade, Carbon::now(), $request->id_detail]);

        return response()->json(['message' => 'Item berhasil diupdate.']);
    }

    public function cancelOpnameItem(Request $request)
    {
        $request->validate([
            'id_detail' => 'required|integer',
        ]);

        $header = DB::select("
            SELECT h.status FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            WHERE d.id = ?
            LIMIT 1
        ", [$request->id_detail]);

        if (count($header) > 0 && $header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname untuk carton ini sudah CLOSED, tidak bisa menghapus item!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET cancel = 'Y', updated_at = ? WHERE id = ?
        ", [Carbon::now(), $request->id_detail]);

        return response()->json(['message' => 'Item berhasil dibatalkan.']);
    }

    public function finishOpname(Request $request)
    {
        $request->validate([
            'no_carton' => 'required|string',
            'periode' => 'required|string',
        ]);

        $header = DB::select("
            SELECT no_opname, status FROM fg_stok_opname_header
            WHERE periode = ? AND no_carton = ? AND (cancel = 'N' OR cancel IS NULL)
            LIMIT 1
        ", [$request->periode, $request->no_carton]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname untuk carton ini sudah CLOSED!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_header SET status = 'CLOSED', updated_at = ? WHERE no_opname = ?
        ", [Carbon::now(), $header[0]->no_opname]);

        return response()->json([
            'message' => 'Opname berhasil diselesaikan.',
            'no_opname' => $header[0]->no_opname,
        ]);
    }
}
