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
                    h.ket,
                    h.status,
                    COUNT(DISTINCT d.no_carton) total_carton,
                    IFNULL(SUM(d.qty), 0) total_qty
                FROM fg_stok_opname_header h
                LEFT JOIN fg_stok_opname_detail d
                    ON d.no_opname = h.no_opname AND d.cancel = 'N'
                WHERE h.tgl_opname BETWEEN ? AND ?
                    AND h.cancel = 'N'
                GROUP BY h.no_opname, h.tgl_opname, h.periode, h.ket, h.status
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
                h.ket,
                d.no_carton,
                d.no_pallet,
                d.status,
                m.buyer,
                m.ws,
                m.styleno,
                m.dest,
                m.color,
                m.size,
                m.product_item,
                d.grade,
                d.qty
            FROM fg_stok_opname_header h
            JOIN fg_stok_opname_detail d ON d.no_opname = h.no_opname AND d.cancel = 'N'
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            LEFT JOIN master_size_new ms ON ms.size = m.size
            WHERE h.tgl_opname BETWEEN ? AND ?
                AND h.cancel = 'N'
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
            'Keterangan',
            'No. Carton',
            'No. Pallet',
            'Status',
            'Buyer',
            'WS',
            'Style',
            'Dest',
            'Color',
            'Size',
            'Product Item',
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
                $row->ket ?? '',
                $row->no_carton ?? '',
                $row->no_pallet ?? '',
                $row->status ?? '',
                $row->buyer ?? '',
                $row->ws ?? '',
                $row->styleno ?? '',
                $row->dest ?? '',
                $row->color ?? '',
                $row->size ?? '',
                $row->product_item ?? '',
                $row->grade ?? '',
                (float) ($row->qty ?? 0),
            ], [
                'border' => 'thin',
            ]);
        }

        foreach (range('A', 'P') as $col) {
            $sheet->setColWidth($col, 18);
        }

        return $excel->download();
    }

    public function dashboardAnalytics(Request $request)
    {
        return view('fg-stock.dashboard_opname_fg_stock', [
            'page' => 'dashboard-fg-stock',
            'subPageGroup' => 'fgstock-opname',
            'subPage' => 'opname-fg-stock',
        ]);
    }

    public function getOpnameList(Request $request)
    {
        $data = DB::select("
            SELECT
                no_opname,
                ket,
                status,
                CONCAT(
                    DATE_FORMAT(tgl_opname, '%d'), '-',
                    LEFT(DATE_FORMAT(tgl_opname, '%M'), 3), '-',
                    DATE_FORMAT(tgl_opname, '%Y')
                ) AS tgl_opname_fix
            FROM fg_stok_opname_header
            WHERE cancel = 'N'
            ORDER BY tgl_opname DESC, no_opname DESC
        ");

        return response()->json($data);
    }

    public function getSummary(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $no_opname = $request->no_opname;

        $summary = DB::select("
            SELECT
                IFNULL(SUM(d.qty), 0) total_qty,
                COUNT(DISTINCT d.no_pallet) total_pallet,
                COUNT(DISTINCT d.no_carton) total_karton,
                IFNULL(SUM(CASE WHEN d.grade = 'A' THEN d.qty ELSE 0 END), 0) grade_a_qty
            FROM fg_stok_opname_header h
            LEFT JOIN fg_stok_opname_detail d
                ON d.no_opname = h.no_opname AND d.cancel = 'N'
            WHERE h.no_opname = ? AND h.cancel = 'N'
        ", [$no_opname]);

        $topBuyer = DB::select("
            SELECT m.buyer, SUM(d.qty) qty
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
            GROUP BY m.buyer
            ORDER BY qty DESC
            LIMIT 1
        ", [$no_opname]);

        $totalQty = $summary[0]->total_qty ?? 0;
        $gradeAQty = $summary[0]->grade_a_qty ?? 0;

        return response()->json([
            'total_qty' => (float) $totalQty,
            'total_pallet' => (int) ($summary[0]->total_pallet ?? 0),
            'total_karton' => (int) ($summary[0]->total_karton ?? 0),
            'grade_a_qty' => (float) $gradeAQty,
            'grade_a_pct' => $totalQty > 0 ? round(($gradeAQty / $totalQty) * 100) : 0,
            'top_buyer' => count($topBuyer) > 0 ? $topBuyer[0]->buyer : null,
            'top_buyer_qty' => count($topBuyer) > 0 ? (float) $topBuyer[0]->qty : 0,
        ]);
    }

    public function getChartGrade(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $data = DB::select("
            SELECT d.grade, SUM(d.qty) qty
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
            GROUP BY d.grade
            ORDER BY d.grade
        ", [$request->no_opname]);

        return response()->json($data);
    }

    public function getChartBuyer(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $data = DB::select("
            SELECT m.buyer, SUM(d.qty) qty
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
            GROUP BY m.buyer
            ORDER BY qty DESC
            LIMIT 10
        ", [$request->no_opname]);

        return response()->json($data);
    }

    public function getMasterData(Request $request)
    {
        $no_opname = $request->no_opname;

        $data = DB::select("
            SELECT
                h.no_opname,
                d.no_pallet,
                d.no_carton,
                h.ket,
                m.buyer,
                m.styleno,
                m.product_item,
                d.grade,
                d.qty
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
            ORDER BY h.tgl_opname DESC, h.no_opname DESC, d.id ASC
        ", [$no_opname]);

        return DataTables::of($data)->toJson();
    }

    public function getWarehouseMap(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $data = DB::select("
            SELECT
                d.no_pallet,
                COUNT(DISTINCT d.no_carton) total_karton,
                IFNULL(SUM(d.qty), 0) total_qty,
                GROUP_CONCAT(DISTINCT d.no_carton SEPARATOR ', ') no_carton_list,
                GROUP_CONCAT(DISTINCT m.buyer SEPARATOR ', ') buyer_list,
                GROUP_CONCAT(DISTINCT m.ws SEPARATOR ', ') ws_list,
                GROUP_CONCAT(DISTINCT m.styleno SEPARATOR ', ') styleno_list,
                GROUP_CONCAT(DISTINCT m.color SEPARATOR ', ') color_list,
                GROUP_CONCAT(DISTINCT m.product_item SEPARATOR ', ') product_item_list
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            LEFT JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
                AND d.no_pallet IS NOT NULL AND d.no_pallet != ''
            GROUP BY d.no_pallet
        ", [$request->no_opname]);

        $zones = [];

        foreach ($data as $row) {
            $parts = explode('.', $row->no_pallet);
            $zona = strtoupper(trim($parts[0]));

            if ($zona === '') {
                continue;
            }

            $baris = isset($parts[1]) ? (int) $parts[1] : 1;
            $kolom = isset($parts[2]) ? (int) $parts[2] : 1;
            $baris = $baris > 0 ? $baris : 1;
            $kolom = $kolom > 0 ? $kolom : 1;

            if (!isset($zones[$zona])) {
                $zones[$zona] = [
                    'zona' => $zona,
                    'max_baris' => 1,
                    'max_kolom' => 1,
                    'cells' => [],
                ];
            }

            $zones[$zona]['max_baris'] = max($zones[$zona]['max_baris'], $baris);
            $zones[$zona]['max_kolom'] = max($zones[$zona]['max_kolom'], $kolom);

            $zones[$zona]['cells'][] = [
                'no_pallet' => $row->no_pallet,
                'baris' => $baris,
                'kolom' => $kolom,
                'total_karton' => (int) $row->total_karton,
                'total_qty' => (float) $row->total_qty,
                'no_carton_list' => $row->no_carton_list ?? '',
                'buyer_list' => $row->buyer_list ?? '',
                'ws_list' => $row->ws_list ?? '',
                'styleno_list' => $row->styleno_list ?? '',
                'color_list' => $row->color_list ?? '',
                'product_item_list' => $row->product_item_list ?? '',
            ];
        }

        ksort($zones);

        return response()->json(array_values($zones));
    }

    public function getWarehouseFilterOptions(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $data = DB::select("
            SELECT DISTINCT m.buyer, m.ws, m.styleno, m.color, m.product_item
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND h.cancel = 'N' AND d.cancel = 'N'
                AND d.no_pallet IS NOT NULL AND d.no_pallet != ''
        ", [$request->no_opname]);

        $pluck = function ($field) use ($data) {
            $values = [];

            foreach ($data as $row) {
                $val = $row->$field;

                if ($val !== null && $val !== '' && !in_array($val, $values)) {
                    $values[] = $val;
                }
            }

            sort($values);

            return $values;
        };

        return response()->json([
            'buyer' => $pluck('buyer'),
            'ws' => $pluck('ws'),
            'styleno' => $pluck('styleno'),
            'color' => $pluck('color'),
            'product_item' => $pluck('product_item'),
        ]);
    }

    public function getPalletDetail(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $data = DB::select("
            SELECT
                h.no_opname,
                d.no_carton,
                d.status,
                m.buyer,
                m.ws,
                m.styleno,
                m.product_item,
                d.grade,
                d.qty
            FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE h.no_opname = ? AND d.no_pallet = ?
                AND h.cancel = 'N' AND d.cancel = 'N'
            ORDER BY d.no_carton, d.id
        ", [$request->no_opname, $request->no_pallet]);

        return response()->json($data);
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
        $no_opname = $request->no_opname;

        if (!$no_carton || !$no_opname) {
            abort(404);
        }

        $detail = DB::select("
            SELECT no_pallet FROM fg_stok_opname_detail
            WHERE no_carton = ? AND no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$no_carton, $no_opname]);

        $no_pallet = count($detail) > 0 ? $detail[0]->no_pallet : null;

        $pdf = PDF::loadView('fg-stock.print_qr_opname_fg_stock', [
            'no_carton' => $no_carton,
            'no_pallet' => $no_pallet,
        ])->setPaper([0, 0, 209.76, 297.64]);

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
            'no_opname' => 'required|string',
        ]);

        $data = DB::select("
            SELECT d.id id_detail, d.no_carton, d.no_pallet, d.status, d.qty, d.grade, d.created_by, d.created_at, d.updated_at, m.buyer, m.ws, m.styleno, m.dest, m.color, m.size
            FROM fg_stok_opname_detail d
            JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            WHERE d.no_opname = ?
            AND d.cancel = 'N'
            ORDER BY d.id
        ", [$request->no_opname]);

        $cartons = DB::select("
            SELECT DISTINCT no_carton, no_pallet
            FROM fg_stok_opname_detail
            WHERE no_opname = ? AND cancel = 'N'
        ", [$request->no_opname]);

        return response()->json([
            'items' => $data,
            'cartons' => $cartons,
        ]);
    }

    public function getCartonList(Request $request)
    {
        $no_opname = $request->no_opname;

        if (!$no_opname) {
            return DataTables::of([])->toJson();
        }

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header WHERE no_opname = ? AND cancel = 'N' LIMIT 1
        ", [$no_opname]);

        $sessionClosed = count($header) > 0 && $header[0]->status === 'CLOSED';
        $canChangeCartonStatus = Auth::user()->roles()->whereIn('nama_role', ['superadmin', 'accounting'])->exists();

        $data = DB::select("
            SELECT
                d.no_carton,
                d.no_pallet,
                MAX(d.status) status,
                SUM(IFNULL(d.qty, 0)) qty,
                SUM(CASE WHEN d.id_so_det IS NOT NULL THEN 1 ELSE 0 END) item_count,
                MIN(d.created_at) created_at,
                MAX(d.updated_at) updated_at,
                SUBSTRING_INDEX(GROUP_CONCAT(d.created_by ORDER BY d.created_at ASC SEPARATOR '||'), '||', 1) created_by
            FROM fg_stok_opname_detail d
            WHERE d.no_opname = ? AND d.cancel = 'N'
            GROUP BY d.no_carton, d.no_pallet
            ORDER BY MIN(d.id) ASC
        ", [$no_opname]);

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('created_at', fn ($row) => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-')
            ->editColumn('updated_at', fn ($row) => $row->updated_at ? Carbon::parse($row->updated_at)->format('d-m-Y H:i') : '-')
            ->editColumn('qty', fn ($row) => '<span class="qty-pill">' . (float) $row->qty . '</span>')
            ->addColumn('status_badge', function ($row) {
                $cls = $row->status === 'CLOSED' ? 'badge-status-closed' : 'badge-status-open';
                return '<span class="badge ' . $cls . '">' . $row->status . '</span>';
            })
            ->addColumn('aksi', function ($row) use ($sessionClosed, $canChangeCartonStatus) {
                $hasItems = $row->item_count > 0;
                $rowClosed = $sessionClosed || $row->status === 'CLOSED';
                $isiBtnClass = $rowClosed ? 'btn-outline-secondary' : ($hasItems ? 'btn-outline-primary' : 'btn-primary');
                $isiIcon = $rowClosed ? 'fa-eye' : ($hasItems ? 'fa-edit' : 'fa-plus');
                $isiLabel = $rowClosed ? 'View' : 'Isi Item';

                $qrBtn = $row->status === 'CLOSED'
                    ? '<button type="button" class="btn btn-outline-success btn-sm" onclick="printQr(\'' . e($row->no_carton) . '\')" title="Cetak QR"><i class="fas fa-qrcode fa-sm"></i></button>'
                    : '';

                $statusBtn = '';
                if ($canChangeCartonStatus && $hasItems) {
                    if ($row->status === 'CLOSED') {
                        $statusBtn = '<button type="button" class="btn btn-outline-info btn-sm" onclick="reopenCartonModal(\'' . e($row->no_carton) . '\', \'' . e($row->no_pallet) . '\')" title="Buka Kembali"><i class="fas fa-lock-open fa-sm"></i> Buka</button>';
                    } elseif (!$sessionClosed) {
                        $statusBtn = '<button type="button" class="btn btn-outline-warning btn-sm" onclick="closeCartonModal(\'' . e($row->no_carton) . '\', \'' . e($row->no_pallet) . '\')" title="Tutup Carton"><i class="fas fa-lock fa-sm"></i> Tutup</button>';
                    }
                }

                $hapusStyle = $rowClosed ? 'style="display:none;"' : '';

                return '<div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn ' . $isiBtnClass . ' btn-sm" onclick="bukaIsiItem(\'' . e($row->no_carton) . '\', \'' . e($row->no_pallet) . '\')">
                        <i class="fas ' . $isiIcon . ' fa-sm"></i> ' . $isiLabel . '
                    </button>
                    ' . $statusBtn . '
                    ' . $qrBtn . '
                    <button type="button" class="btn btn-outline-danger btn-sm" ' . $hapusStyle . ' onclick="hapusCartonRow(\'' . e($row->no_carton) . '\', \'' . e($row->no_pallet) . '\')">
                        <i class="fas fa-trash fa-sm"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['qty', 'status_badge', 'aksi'])
            ->toJson();
    }

    public function storeCartonHeader(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header WHERE no_opname = ? AND cancel = 'N' LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED!'], 422);
        }

        $exists = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'No. Carton & No. Pallet ini sudah ada di list!'], 422);
        }

        $usedElsewhere = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet != ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($usedElsewhere) > 0) {
            return response()->json(['message' => 'No. Carton ini sudah terdaftar di Pallet lain pada opname ini!'], 422);
        }

        DB::insert("
            INSERT INTO fg_stok_opname_detail (no_opname, no_carton, no_pallet, cancel, status, created_by, created_at, updated_at)
            VALUES (?, ?, ?, 'N', 'OPEN', ?, ?, ?)
        ", [$request->no_opname, $request->no_carton, $request->no_pallet, Auth::user()->name, Carbon::now(), Carbon::now()]);

        return response()->json(['message' => 'No. Carton berhasil ditambahkan ke list.']);
    }

    public function hapusCarton(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header WHERE no_opname = ? AND cancel = 'N' LIMIT 1
        ", [$request->no_opname]);

        if (count($header) > 0 && $header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname sudah CLOSED, tidak bisa menghapus!'], 422);
        }

        $rowClosed = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N' AND status = 'CLOSED'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($rowClosed) > 0) {
            return response()->json(['message' => 'Carton ini sudah CLOSED, tidak bisa menghapus!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET cancel = 'Y', updated_at = ?
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname, $request->no_carton, $request->no_pallet]);

        return response()->json(['message' => 'No. Carton berhasil dihapus.']);
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

    public function getMasterPallet(Request $request)
    {
        $data = DB::select("
            SELECT no_pallet FROM fg_stok_master_pallet WHERE cancel = 'N' ORDER BY no_pallet
        ");

        return response()->json($data);
    }

    public function storeMasterPallet(Request $request)
    {
        $request->validate([
            'zone' => 'required|string|max:3',
            'baris' => 'required|numeric',
            'kolom' => 'required|numeric',
        ]);

        $zone = strtoupper(preg_replace('/\s+/', '', $request->zone));
        $baris = $request->baris;
        $kolom = $request->kolom;
        $no_pallet = $zone . '.' . $baris . '.' . $kolom;

        $exists = DB::select("
            SELECT 1 FROM fg_stok_master_pallet WHERE no_pallet = ? AND cancel = 'N'
        ", [$no_pallet]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'No. Pallet sudah ada'], 422);
        }

        DB::insert("
            INSERT INTO fg_stok_master_pallet (no_pallet, zone, baris, kolom, cancel, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'N', ?, ?, ?)
        ", [$no_pallet, $zone, $baris, $kolom, Auth::user()->name, Carbon::now(), Carbon::now()]);

        return response()->json([
            'message' => 'No. Pallet berhasil ditambahkan.',
            'no_pallet' => $no_pallet,
        ]);
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

    public function getBuyerWs(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string',
        ]);

        $q = trim((string) $request->q);

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $words = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);

        $whereParts = [];
        $bindings = [];

        foreach ($words as $word) {
            $whereParts[] = '(buyer LIKE ? OR ws LIKE ? OR styleno LIKE ? OR product_item LIKE ?)';
            $like = '%' . $word . '%';
            array_push($bindings, $like, $like, $like, $like);
        }

        $whereSql = implode(' AND ', $whereParts);

        $data = DB::select("
            SELECT product_item, buyer, ws, styleno FROM master_sb_ws
            WHERE buyer IS NOT NULL AND ws IS NOT NULL
            AND ({$whereSql})
            GROUP BY buyer, ws, styleno, product_item
            ORDER BY buyer, ws
        ", $bindings);

        return response()->json($data);
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

    public function storeOpnameHeader(Request $request)
    {
        $request->validate([
            'periode' => 'required|string',
            'tgl_opname' => 'required|date',
            'ket' => 'nullable|string',
        ]);

        if (date('Y-m', strtotime($request->tgl_opname)) !== $request->periode) {
            return response()->json(['message' => 'Tgl. Opname harus dalam bulan periode yang dipilih!'], 422);
        }

        $user = Auth::user()->name;
        $now = Carbon::now();

        $counter = DB::select("
            SELECT IF(MAX(no_opname) IS NULL, 1, MAX(RIGHT(no_opname, 5)) + 1) nomor
            FROM fg_stok_opname_header
            WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
            AND LEFT(no_opname, 3) = 'OPN'
        ");

        $no_opname = 'OPN' . date('ym') . str_pad($counter[0]->nomor, 5, '0', STR_PAD_LEFT);

        DB::insert("
            INSERT INTO fg_stok_opname_header (no_opname, tgl_opname, periode, ket, cancel, status, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'N', 'OPEN', ?, ?, ?)
        ", [$no_opname, $request->tgl_opname, $request->periode, $request->ket, $user, $now, $now]);

        return response()->json([
            'message' => 'Data opname ' . $no_opname . ' berhasil dibuat.',
            'no_opname' => $no_opname,
        ]);
    }

    public function getOpnameHeader(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $header = DB::select("
            SELECT no_opname, tgl_opname, periode, ket, status FROM fg_stok_opname_header
            WHERE no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 404);
        }

        return response()->json($header[0]);
    }

    public function storeOpname(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
            'id_so_det' => 'required',
            'qty' => 'required|numeric|min:1',
            'grade' => 'required|string',
        ]);

        $user = Auth::user()->name;
        $now = Carbon::now();

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header
            WHERE no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED, tidak bisa menambah item!'], 422);
        }

        $cartonStatus = DB::select("
            SELECT status FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($cartonStatus) > 0 && $cartonStatus[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Carton ini sudah CLOSED, tidak bisa menambah item!'], 422);
        }

        $cartonOnOtherPallet = DB::select("
            SELECT DISTINCT no_pallet FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet != ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($cartonOnOtherPallet) > 0) {
            return response()->json(['message' => 'No. Carton ' . $request->no_carton . ' sudah terdaftar di Pallet ' . $cartonOnOtherPallet[0]->no_pallet . ' pada opname ini!'], 422);
        }

        $exists = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND id_so_det = ? AND grade = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname, $request->no_carton, $request->id_so_det, $request->grade]);

        if (count($exists) > 0) {
            return response()->json(['message' => 'Item dengan Grade ini sudah ada di carton, gunakan tombol Update untuk mengubah qty-nya!'], 422);
        }

        DB::insert("
            INSERT INTO fg_stok_opname_detail (no_opname, id_so_det, qty, grade, no_pallet, no_carton, cancel, status, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'N', 'OPEN', ?, ?, ?)
        ", [$request->no_opname, $request->id_so_det, $request->qty, $request->grade, $request->no_pallet, $request->no_carton, $user, $now, $now]);

        $id_detail = DB::getPdo()->lastInsertId();

        return response()->json([
            'message' => 'Item berhasil disimpan.',
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
            SELECT h.status header_status, d.status item_status, d.no_opname, d.no_carton, d.id_so_det FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            WHERE d.id = ?
            LIMIT 1
        ", [$request->id_detail]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Item tidak ditemukan!'], 422);
        }

        if ($header[0]->header_status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED, tidak bisa mengubah item!'], 422);
        }

        if ($header[0]->item_status === 'CLOSED') {
            return response()->json(['message' => 'Carton ini sudah CLOSED, tidak bisa mengubah item!'], 422);
        }

        $exists = DB::select("
            SELECT id FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND id_so_det = ? AND grade = ? AND id != ? AND cancel = 'N'
            LIMIT 1
        ", [$header[0]->no_opname, $header[0]->no_carton, $header[0]->id_so_det, $request->grade, $request->id_detail]);

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
            SELECT h.status header_status, d.status item_status FROM fg_stok_opname_detail d
            JOIN fg_stok_opname_header h ON h.no_opname = d.no_opname
            WHERE d.id = ?
            LIMIT 1
        ", [$request->id_detail]);

        if (count($header) > 0 && ($header[0]->header_status === 'CLOSED' || $header[0]->item_status === 'CLOSED')) {
            return response()->json(['message' => 'Opname / carton ini sudah CLOSED, tidak bisa menghapus item!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET cancel = 'Y', updated_at = ? WHERE id = ?
        ", [Carbon::now(), $request->id_detail]);

        return response()->json(['message' => 'Item berhasil dibatalkan.']);
    }

    public function updateCartonPallet(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'id_details' => 'required|array|min:1',
            'id_details.*' => 'integer',
            'no_pallet_baru' => 'required|string',
        ]);

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header
            WHERE no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED, tidak bisa mengubah pallet!'], 422);
        }

        $placeholders = implode(',', array_fill(0, count($request->id_details), '?'));

        $items = DB::select("
            SELECT id, no_carton, status FROM fg_stok_opname_detail
            WHERE no_opname = ? AND id IN ({$placeholders}) AND cancel = 'N'
        ", array_merge([$request->no_opname], $request->id_details));

        if (count($items) === 0) {
            return response()->json(['message' => 'Carton tidak ditemukan!'], 422);
        }

        if (collect($items)->contains(fn ($item) => $item->status === 'CLOSED')) {
            return response()->json(['message' => 'Carton ini sudah CLOSED, tidak bisa mengubah pallet!'], 422);
        }

        $noCarton = $items[0]->no_carton;

        DB::update("
            UPDATE fg_stok_opname_detail SET no_pallet = ?, updated_at = ?
            WHERE no_opname = ? AND id IN ({$placeholders})
        ", array_merge([$request->no_pallet_baru, Carbon::now(), $request->no_opname], $request->id_details));

        return response()->json([
            'message' => 'No. Pallet carton ' . $noCarton . ' berhasil diubah.',
            'no_pallet' => $request->no_pallet_baru,
        ]);
    }

    public function finishOpname(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
        ]);

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header
            WHERE no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED!'], 422);
        }

        $cartonBelumClosed = DB::select("
            SELECT no_carton, no_pallet
            FROM fg_stok_opname_detail
            WHERE no_opname = ?
                AND cancel = 'N'
                AND (status IS NULL OR status != 'CLOSED')
            GROUP BY no_carton, no_pallet
        ", [$request->no_opname]);

        if (count($cartonBelumClosed) > 0) {
            return response()->json([
                'message' => 'Masih ada ' . count($cartonBelumClosed) . ' carton yang belum CLOSED. Selesaikan semua carton terlebih dahulu!',
            ], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_header
            SET status = 'CLOSED', updated_at = ?
            WHERE no_opname = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname]);

        return response()->json([
            'message' => 'Opname berhasil diselesaikan.',
            'no_opname' => $request->no_opname,
        ]);
    }

    public function finishOpnameCarton(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $header = DB::select("
            SELECT status FROM fg_stok_opname_header
            WHERE no_opname = ? AND cancel = 'N'
            LIMIT 1
        ", [$request->no_opname]);

        if (count($header) === 0) {
            return response()->json(['message' => 'Data opname tidak ditemukan!'], 422);
        }

        if ($header[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Opname ini sudah CLOSED!'], 422);
        }

        $items = DB::select("
            SELECT id, status FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N' AND id_so_det IS NOT NULL
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($items) === 0) {
            return response()->json(['message' => 'Belum ada item pada carton ini!'], 422);
        }

        if ($items[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Carton ini sudah CLOSED!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET status = 'CLOSED', updated_at = ?
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname, $request->no_carton, $request->no_pallet]);

        return response()->json(['message' => 'Carton berhasil diselesaikan.']);
    }

    public function changeCartonStatus(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user->roles()->whereIn('nama_role', ['superadmin', 'accounting'])->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah status carton!'], 403);
        }

        $items = DB::select("
            SELECT id, status FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($items) === 0) {
            return response()->json(['message' => 'Carton tidak ditemukan!'], 422);
        }

        if ($items[0]->status === 'CLOSED') {
            return response()->json(['message' => 'Carton ini sudah CLOSED!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET status = 'CLOSED', updated_at = ?
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname, $request->no_carton, $request->no_pallet]);

        return response()->json(['message' => 'Status carton berhasil diubah menjadi CLOSED.']);
    }

    public function reopenCarton(Request $request)
    {
        $request->validate([
            'no_opname' => 'required|string',
            'no_carton' => 'required|string',
            'no_pallet' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user->roles()->whereIn('nama_role', ['superadmin', 'accounting'])->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah status carton!'], 403);
        }

        $items = DB::select("
            SELECT id, status FROM fg_stok_opname_detail
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [$request->no_opname, $request->no_carton, $request->no_pallet]);

        if (count($items) === 0) {
            return response()->json(['message' => 'Carton tidak ditemukan!'], 422);
        }

        if ($items[0]->status !== 'CLOSED') {
            return response()->json(['message' => 'Carton ini belum CLOSED!'], 422);
        }

        DB::update("
            UPDATE fg_stok_opname_detail SET status = 'OPEN', updated_at = ?
            WHERE no_opname = ? AND no_carton = ? AND no_pallet = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname, $request->no_carton, $request->no_pallet]);

        DB::update("
            UPDATE fg_stok_opname_header SET status = 'OPEN', updated_at = ?
            WHERE no_opname = ? AND cancel = 'N'
        ", [Carbon::now(), $request->no_opname]);

        return response()->json(['message' => 'Status carton dan opname berhasil diubah menjadi OPEN.']);
    }
}
