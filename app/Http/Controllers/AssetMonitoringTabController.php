<?php

namespace App\Http\Controllers;

use App\Exports\ExportAssetTab;
use App\Services\AssetTabService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

// Monitoring Tab: tab yang sedang dibawa (siapa, ke mana, sejak kapan) dan riwayat seluruh transaksi tab
class AssetMonitoringTabController extends Controller
{
    const STATUS_LABELS = [
        'AMBIL' => 'Ambil',
        'KEMBALI' => 'Kembali',
        'REPAIR' => 'Kirim Repair',
        'SELESAI_REPAIR' => 'Selesai Repair',
    ];

    const EXPORT_LIMIT = 50000;

    protected $tabService;

    public function __construct(AssetTabService $tabService)
    {
        $this->tabService = $tabService;
    }

    public function asset_monitoring_tab(Request $request)
    {
        $counts = DB::table('asset_master_tab')
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('asset_management.monitoring_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_monitoring_tab',
            'containerFluid' => true,
            'idleCount' => $counts['IDLE'] ?? 0,
            'takenCount' => $counts['TAKEN'] ?? 0,
            'repairCount' => $counts['REPAIR'] ?? 0,
            'overdueCount' => $this->tabService->overdueCount(),
            'overdueHours' => AssetTabService::OVERDUE_HOURS,
            'onlyOverdue' => $request->boolean('overdue'),
            'mainLokasiList' => DB::select("SELECT main_lokasi FROM asset_master_main_lokasi ORDER BY main_lokasi ASC"),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function taken_monitoring_tab(Request $request)
    {
        return response()->json(['data' => $this->takenRows($request)]);
    }

    public function export_taken_monitoring_tab(Request $request)
    {
        $rows = array_map(fn($row) => [
            $row['tab_code'],
            $row['line_code'],
            $row['rfid_code'],
            $row['enroll_id'] !== null ? (string) $row['enroll_id'] : '',
            $row['employee_name'] ?? ($row['enroll_id'] === null && $row['taken_at'] ? 'Bulk (tanpa NIK)' : ''),
            $row['tujuan'],
            $row['taken_at'],
            $row['menit'] !== null ? $this->durationText($row['menit']) : '',
            $row['overdue'] ? 'Belum kembali > ' . AssetTabService::OVERDUE_HOURS . ' jam' : 'Normal',
        ], $this->takenRows($request));

        return Excel::download(new ExportAssetTab('Tab Sedang Dibawa', [
            'Tab Code', 'Line', 'RFID', 'Enroll ID', 'Nama', 'Tujuan', 'Diambil', 'Lama Dibawa', 'Status',
        ], $rows), 'Tab Sedang Dibawa ' . Carbon::now()->format('Y-m-d His') . '.xlsx');
    }

    // Riwayat dengan paging dari server (DataTables serverSide), karena transaksi terus bertambah setiap hari
    public function history_monitoring_tab(Request $request)
    {
        $query = $this->historyQuery($request);
        $total = (clone $query)->count();

        $rows = $query->orderByDesc('tt.id')
            ->offset(max(0, (int) $request->start))
            ->limit(min(max(1, (int) ($request->length ?: 10)), 500))
            ->get();

        $this->attachNames($rows);

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ]);
    }

    public function export_history_monitoring_tab(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $rows = $this->historyQuery($request)->orderByDesc('tt.id')->limit(self::EXPORT_LIMIT)->get();
        $this->attachNames($rows);

        $data = $rows->map(fn($row) => [
            Carbon::parse($row->created_at)->format('Y-m-d H:i'),
            self::STATUS_LABELS[$row->status] ?? $row->status,
            $row->tipe_input,
            $row->tab_code,
            $row->line_code,
            $row->rfid_code,
            $row->enroll_id !== null ? (string) $row->enroll_id : '',
            $row->employee_name,
            $row->tujuan,
            $row->keterangan,
            $row->created_by,
        ])->all();

        return Excel::download(new ExportAssetTab('Riwayat Tab', [
            'Waktu', 'Aksi', 'Tipe', 'Tab Code', 'Line', 'RFID', 'Enroll ID', 'Nama', 'Tujuan', 'Keterangan', 'Dibuat Oleh',
        ], $data), 'Riwayat Tab ' . $from . ' sd ' . $to . '.xlsx');
    }

    private function takenRows(Request $request)
    {
        $tabs = DB::table('asset_master_tab')
            ->select('rfid_code', 'line_code', 'tab_code', 'lokasi')
            ->where('status', 'TAKEN')
            ->get();

        $holders = $this->tabService->holders();

        $rows = [];
        foreach ($tabs as $tab) {
            $holder = $this->tabService->holderPayload($holders[strtoupper($tab->rfid_code)] ?? null);

            $rows[] = [
                'rfid_code' => $tab->rfid_code,
                'line_code' => $tab->line_code,
                'tab_code' => $tab->tab_code,
                'tujuan' => $holder['tujuan'] ?? $tab->lokasi,
                'enroll_id' => $holder['enroll_id'] ?? null,
                'employee_name' => $holder['name'] ?? null,
                'tipe_input' => $holder['tipe_input'] ?? null,
                'taken_at' => $holder['taken_at'] ?? null,
                'menit' => $holder['menit'] ?? null,
                'overdue' => $holder['overdue'] ?? false,
            ];
        }

        if ($request->filled('tujuan')) {
            $rows = array_filter($rows, fn($row) => $row['tujuan'] === $request->tujuan);
        }

        if ($request->boolean('overdue')) {
            $rows = array_filter($rows, fn($row) => $row['overdue']);
        }

        // Yang paling lama dibawa tampil paling atas
        usort($rows, fn($a, $b) => ($b['menit'] ?? -1) <=> ($a['menit'] ?? -1));

        return array_values($rows);
    }

    private function historyQuery(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = DB::table('asset_trans_tab as tt')
            ->leftJoin('asset_master_tab as mt', 'mt.rfid_code', '=', 'tt.rfid_code')
            ->select(
                'tt.id', 'tt.created_at', 'tt.status', 'tt.tipe_input', 'tt.rfid_code', 'mt.tab_code', 'mt.line_code',
                'tt.enroll_id', 'tt.tujuan', 'tt.keterangan', 'tt.created_by'
            )
            ->whereBetween('tt.tgl_trans', [$from, $to]);

        if ($request->filled('status')) {
            $query->where('tt.status', $request->status);
        }

        if ($request->filled('tipe')) {
            $query->where('tt.tipe_input', $request->tipe);
        }

        $keyword = trim((string) ($request->input('search.value') ?? $request->q));

        if ($keyword !== '') {
            // Nama karyawan ada di HRIS, jadi dicari dulu enroll ID-nya
            $enrollIds = mb_strlen($keyword) >= 3 ? $this->tabService->enrollIdsByName($keyword) : [];
            $like = '%' . $keyword . '%';

            $query->where(function ($q) use ($like, $enrollIds) {
                $q->where('tt.rfid_code', 'like', $like)
                    ->orWhere('mt.tab_code', 'like', $like)
                    ->orWhere('mt.line_code', 'like', $like)
                    ->orWhere('tt.enroll_id', 'like', $like)
                    ->orWhere('tt.tujuan', 'like', $like)
                    ->orWhere('tt.keterangan', 'like', $like)
                    ->orWhere('tt.created_by', 'like', $like);

                if ($enrollIds) {
                    $q->orWhereIn('tt.enroll_id', $enrollIds);
                }
            });
        }

        return $query;
    }

    // Default 7 hari terakhir
    private function dateRange(Request $request)
    {
        $from = $this->parseDate($request->from) ?? Carbon::today()->subDays(6);
        $to = $this->parseDate($request->to) ?? Carbon::today();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->format('Y-m-d'), $to->format('Y-m-d')];
    }

    private function parseDate($value)
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function attachNames($rows)
    {
        $names = $this->tabService->employeeNames($rows->pluck('enroll_id')->all());

        foreach ($rows as $row) {
            $row->employee_name = $names[$row->enroll_id] ?? null;
        }
    }

    private function durationText($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' menit';
        }

        if ($minutes < 1440) {
            return intdiv($minutes, 60) . ' jam ' . ($minutes % 60) . ' menit';
        }

        return intdiv($minutes, 1440) . ' hari ' . intdiv($minutes % 1440, 60) . ' jam';
    }
}
