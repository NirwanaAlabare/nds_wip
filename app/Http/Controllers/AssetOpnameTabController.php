<?php

namespace App\Http\Controllers;

use App\Exports\ExportAssetTab;
use App\Services\AssetTabService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

// Opname Tab: sapu semua tag di ruangan IT lalu bandingkan dengan status di sistem
class AssetOpnameTabController extends Controller
{
    // Urutan = urutan tampil di detail & export (masalah dulu)
    const HASIL_LABELS = [
        'TIDAK_ADA' => 'Tidak terbaca (seharusnya di ruangan)',
        'BELUM_KEMBALI' => 'Ada di ruangan, status masih dibawa',
        'REPAIR_ADA' => 'Status repair, ada di ruangan',
        'ADA' => 'Ada di ruangan',
        'DI_LAPANGAN' => 'Sedang dibawa',
        'REPAIR' => 'Sedang repair',
    ];

    protected $tabService;

    public function __construct(AssetTabService $tabService)
    {
        $this->tabService = $tabService;
    }

    public function asset_opname_tab()
    {
        return view('asset_management.opname_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_opname_tab',
            'containerFluid' => true,
            'hasilLabels' => self::HASIL_LABELS,
        ]);
    }

    // Seluruh master tab dikirim sekali ke browser, jadi pencocokan saat scan tidak perlu request ke server
    // dan tetap cepat walau tag dibaca beruntun
    public function master_opname_tab()
    {
        $tabs = DB::table('asset_master_tab')
            ->select('rfid_code', 'line_code', 'tab_code', 'status', 'lokasi')
            ->orderBy('tab_code')
            ->get();

        $holders = $this->tabService->holders();

        return response()->json([
            'tabs' => $tabs->map(fn($tab) => [
                'rfid_code' => strtoupper($tab->rfid_code),
                'line_code' => $tab->line_code,
                'tab_code' => $tab->tab_code,
                'status' => $tab->status,
                'lokasi' => $tab->lokasi,
                'holder' => $this->tabService->holderPayload($holders[strtoupper($tab->rfid_code)] ?? null),
            ]),
            'loaded_at' => Carbon::now()->format('H:i:s'),
        ]);
    }

    // Koreksi: tab yang fisiknya ada di ruangan tapi di sistem masih "dibawa" (lupa di-scan Kembalikan)
    public function kembalikan_opname_tab(Request $request)
    {
        $request->validate([
            'codes' => 'required|array|min:1|max:1000',
            'codes.*' => 'required|string|max:100',
        ]);

        $codes = $this->normalizeCodes($request->codes);

        DB::beginTransaction();

        try {
            $tabs = DB::table('asset_master_tab')
                ->select('id', 'rfid_code')
                ->whereIn('rfid_code', $codes)
                ->where('status', 'TAKEN')
                ->lockForUpdate()
                ->get();

            if ($tabs->isEmpty()) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada tab yang masih berstatus dibawa.',
                ], 409);
            }

            $user = Auth::user()->name;
            $timestamp = Carbon::now();

            DB::table('asset_trans_tab')->insert($tabs->map(fn($tab) => [
                'tgl_trans' => $timestamp->format('Y-m-d'),
                'rfid_code' => $tab->rfid_code,
                'status' => 'KEMBALI',
                'tipe_input' => 'OPNAME',
                'keterangan' => 'Koreksi opname: fisik ada di ruangan IT',
                'created_by' => $user,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->all());

            DB::table('asset_master_tab')->whereIn('id', $tabs->pluck('id'))->update([
                'status' => 'IDLE',
                'lokasi' => 'IT',
                'updated_at' => $timestamp,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => $tabs->count() . ' tab dicatat sudah kembali',
            'count' => $tabs->count(),
        ]);
    }

    public function store_opname_tab(Request $request)
    {
        $request->validate([
            'codes' => 'nullable|array|max:5000',
            'codes.*' => 'string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $scanned = array_flip($this->normalizeCodes($request->input('codes', [])));

        if (!$scanned) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada tag yang discan.',
            ], 422);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        DB::beginTransaction();

        try {
            // Hasil dihitung dari status terbaru di server, bukan dari tampilan di browser
            $tabs = DB::table('asset_master_tab')
                ->select('rfid_code', 'line_code', 'tab_code', 'status', 'lokasi')
                ->get();

            $details = [];
            $totals = array_fill_keys(array_keys(self::HASIL_LABELS), 0);
            $registered = [];

            foreach ($tabs as $tab) {
                $code = strtoupper($tab->rfid_code);
                $registered[$code] = true;
                $terbaca = isset($scanned[$code]);

                if ($tab->status === 'TAKEN') {
                    $hasil = $terbaca ? 'BELUM_KEMBALI' : 'DI_LAPANGAN';
                } elseif ($tab->status === 'REPAIR') {
                    $hasil = $terbaca ? 'REPAIR_ADA' : 'REPAIR';
                } else {
                    $hasil = $terbaca ? 'ADA' : 'TIDAK_ADA';
                }

                $totals[$hasil]++;
                $details[] = [
                    'rfid_code' => $tab->rfid_code,
                    'line_code' => $tab->line_code,
                    'tab_code' => $tab->tab_code,
                    'status_sistem' => $tab->status,
                    'lokasi_sistem' => $tab->lokasi,
                    'terbaca' => $terbaca ? 1 : 0,
                    'hasil' => $hasil,
                    'created_at' => $timestamp,
                ];
            }

            // Nomor per hari: OPT/260929/001
            $prefix = 'OPT/' . $timestamp->format('ymd') . '/';
            $lastNo = DB::table('asset_opname_tab')
                ->where('no_opname', 'like', $prefix . '%')
                ->orderByDesc('no_opname')
                ->lockForUpdate()
                ->value('no_opname');
            $noOpname = $prefix . str_pad(($lastNo ? (int) substr($lastNo, strlen($prefix)) : 0) + 1, 3, '0', STR_PAD_LEFT);

            $idOpname = DB::table('asset_opname_tab')->insertGetId([
                'no_opname' => $noOpname,
                'tgl_opname' => $timestamp,
                'total_tab' => $tabs->count(),
                'total_idle' => $totals['ADA'] + $totals['TIDAK_ADA'],
                'total_ada' => $totals['ADA'],
                'total_tidak_ada' => $totals['TIDAK_ADA'],
                'total_belum_kembali' => $totals['BELUM_KEMBALI'],
                'total_repair_ada' => $totals['REPAIR_ADA'],
                'total_tidak_terdaftar' => count(array_diff_key($scanned, $registered)),
                'keterangan' => trim((string) $request->keterangan) ?: null,
                'created_by' => $user,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            foreach (array_chunk($details, 500) as $chunk) {
                DB::table('asset_opname_tab_det')->insert(array_map(fn($row) => $row + ['id_opname' => $idOpname], $chunk));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Hasil opname ' . $noOpname . ' tersimpan',
            'id' => $idOpname,
            'no_opname' => $noOpname,
        ]);
    }

    public function list_opname_tab()
    {
        return response()->json([
            'data' => DB::table('asset_opname_tab')->orderByDesc('id')->limit(100)->get(),
        ]);
    }

    public function detail_opname_tab(Request $request)
    {
        $header = DB::table('asset_opname_tab')->where('id', $request->id)->first();

        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data opname tidak ditemukan.'], 404);
        }

        return response()->json([
            'header' => $header,
            'details' => $this->detailRows($header->id),
        ]);
    }

    public function export_opname_tab(Request $request)
    {
        $header = DB::table('asset_opname_tab')->where('id', $request->id)->first();

        abort_if(!$header, 404);

        $rows = $this->detailRows($header->id)->map(fn($row) => [
            self::HASIL_LABELS[$row->hasil] ?? $row->hasil,
            $row->tab_code,
            $row->line_code,
            $row->rfid_code,
            $row->status_sistem,
            $row->lokasi_sistem,
            $row->terbaca ? 'Ya' : 'Tidak',
        ])->all();

        return Excel::download(new ExportAssetTab('Opname Tab', [
            'Hasil', 'Tab Code', 'Line', 'RFID', 'Status Sistem', 'Lokasi Sistem', 'Terbaca',
        ], $rows), 'Opname Tab ' . str_replace('/', '-', $header->no_opname) . '.xlsx');
    }

    private function detailRows($idOpname)
    {
        $order = implode(',', array_map(fn($key) => "'" . $key . "'", array_keys(self::HASIL_LABELS)));

        return DB::table('asset_opname_tab_det')
            ->where('id_opname', $idOpname)
            ->orderByRaw("FIELD(hasil, $order)")
            ->orderBy('tab_code')
            ->get();
    }

    private function normalizeCodes(array $codes)
    {
        return array_values(array_unique(array_filter(array_map(fn($code) => strtoupper(trim((string) $code)), $codes), 'strlen')));
    }
}
