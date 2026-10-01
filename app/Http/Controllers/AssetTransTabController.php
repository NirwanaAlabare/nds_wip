<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use App\Services\AssetTabService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetTransTabController extends Controller
{
    protected $tabService;

    public function __construct(AssetTabService $tabService)
    {
        $this->tabService = $tabService;
    }

    public function asset_trans_tab(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT det.id, main.main_lokasi, det.sub_lokasi, det.divisi
                FROM asset_master_lokasi_det det
                LEFT JOIN asset_master_main_lokasi main ON main.id = det.id_main_lokasi
                ORDER BY det.id DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        $mainLokasiList = DB::select("SELECT id, main_lokasi FROM asset_master_main_lokasi ORDER BY main_lokasi ASC");
        $subLokasiList = DB::select("
            SELECT DISTINCT sub_lokasi
            FROM asset_master_lokasi_det
            WHERE sub_lokasi IS NOT NULL AND sub_lokasi != ''
            ORDER BY sub_lokasi ASC
        ");
        $divisiList = DB::select("
            SELECT DISTINCT divisi
            FROM asset_master_lokasi_det
            WHERE divisi IS NOT NULL AND divisi != ''
            ORDER BY divisi ASC
        ");

        $idleCount = DB::table('asset_master_tab')->where('status', 'IDLE')->count();
        $takenCount = DB::table('asset_master_tab')->where('status', 'TAKEN')->count();

        // For non-AJAX (initial page load)
        return view('asset_management.trans_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_trans_tab',
            'containerFluid' => true,
            'mainLokasiList' => $mainLokasiList,
            'subLokasiList' => $subLokasiList,
            'divisiList' => $divisiList,
            'idleCount' => $idleCount,
            'takenCount' => $takenCount,
            'overdueCount' => $this->tabService->overdueCount(),
            'overdueHours' => AssetTabService::OVERDUE_HOURS,
        ]);
    }

    public function nik_suggest_trans_tab(Request $request)
    {
        $term = trim($request->q);

        if ($term === '') {
            return response()->json([]);
        }

        $data = DB::connection('mysql_hris')->select("
            SELECT enroll_id, nik, employee_name
            FROM employee_atribut
            WHERE enroll_id LIKE ? AND status_aktif = 'AKTIF'
            ORDER BY enroll_id ASC
            LIMIT 10
        ", [$term . '%']);

        return response()->json($data);
    }

    // Cek apakah tiap tag boleh diambil/dikembalikan. Dipakai saat scan dan saat simpan supaya aturannya sama.
    // $lock = true untuk mengunci baris tag selama transaksi simpan (mencegah 1 tag diambil 2x bersamaan).
    // Return: [KODE => ['ok' => bool, 'message' => ?string, 'tag' => ?object]]
    private function validateTags(array $codes, $action, $lock = false)
    {
        $query = DB::table('asset_master_tab')
            ->select('rfid_code', 'line_code', 'tab_code', 'status')
            ->whereIn('rfid_code', $codes);

        if ($lock) {
            $query->lockForUpdate();
        }

        $tags = $query->get()->keyBy(fn($tag) => strtoupper($tag->rfid_code));

        $result = [];
        foreach ($codes as $code) {
            $tag = $tags[$code] ?? null;
            $message = null;

            if (!$tag) {
                $message = 'Tidak terdaftar di Master Tab';
            } elseif ($tag->status === 'REPAIR') {
                $message = 'Sedang REPAIR';
            } elseif ($action === 'ambil' && $tag->status === 'TAKEN') {
                $message = 'Sedang dibawa (taken), tidak bisa diambil lagi';
            } elseif ($action === 'kembalikan' && $tag->status !== 'TAKEN') {
                $message = 'Tidak sedang dibawa (idle), tidak bisa dikembalikan';
            }

            $result[$code] = ['ok' => $message === null, 'message' => $message, 'tag' => $tag];
        }

        return $result;
    }

    // Kode RFID berupa hex, disamakan huruf besar supaya pembacaan yang sama tidak terhitung 2 tag
    private function normalizeCodes(array $codes)
    {
        return array_values(array_unique(array_filter(array_map(fn($code) => strtoupper(trim((string) $code)), $codes), 'strlen')));
    }

    public function check_rfid_trans_tab(Request $request)
    {
        $rfidCode = strtoupper(trim($request->rfid_code));
        $check = $this->validateTags([$rfidCode], $request->action)[$rfidCode];

        if (!$check['ok']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tag ' . $rfidCode . ': ' . $check['message'] . '.',
            ], $check['tag'] ? 409 : 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $check['tag'],
        ]);
    }

    // Cek banyak tag sekaligus dalam 1 request. Dipakai scan beruntun (continuous scan) supaya kotak scan
    // tidak perlu menunggu jawaban server untuk setiap tag.
    public function check_rfid_batch_trans_tab(Request $request)
    {
        $request->validate([
            'action' => 'required|in:ambil,kembalikan',
            'codes' => 'required|array|min:1|max:1000',
            'codes.*' => 'required|string|max:100',
        ]);

        $codes = $this->normalizeCodes($request->codes);
        $checks = $this->validateTags($codes, $request->action);

        // Pemegang hanya dicari untuk tag yang sedang dibawa: ditampilkan saat Kembalikan,
        // dan jadi keterangan kenapa tag tidak bisa diambil lagi
        $takenCodes = array_keys(array_filter($checks, fn($check) => $check['tag'] && $check['tag']->status === 'TAKEN'));
        $holders = $this->tabService->holders(array_map('strval', $takenCodes));

        $results = [];
        foreach ($checks as $code => $check) {
            $code = (string) $code; // kode RFID yang isinya angka semua jadi key integer di array PHP
            $results[] = [
                'code' => $code,
                'ok' => $check['ok'],
                'registered' => (bool) $check['tag'], // false = tidak ada di Master Tab (mis. tag RFID lain yang ikut terbaca)
                'message' => $check['message'],
                'line_code' => $check['tag']->line_code ?? null,
                'tab_code' => $check['tag']->tab_code ?? null,
                'holder' => $this->tabService->holderPayload($holders[$code] ?? null),
            ];
        }

        return response()->json(['status' => 'success', 'results' => $results]);
    }

    public function store_trans_tab(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:single,bulk',
            'action' => 'required|in:ambil,kembalikan',
            'enroll_id' => 'required_if:mode,single',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|string',
            'id_tujuan' => 'required_if:action,ambil|nullable|integer',
        ]);

        $tags = $this->normalizeCodes($request->tags);

        // Single maupun Bulk cukup minimal 1 tag
        if (!$tags) {
            return response()->json([
                'status' => 'error',
                'message' => 'Scan minimal 1 tag.',
            ], 422);
        }

        $enrollId = null;

        if ($request->mode === 'single') {
            $employee = DB::connection('mysql_hris')->select("
                SELECT enroll_id FROM employee_atribut WHERE enroll_id = ?
            ", [$request->enroll_id]);

            if (empty($employee)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Enroll ID tidak ditemukan di HRIS.',
                ], 404);
            }

            $enrollId = $employee[0]->enroll_id;
        }

        $tujuan = null;

        if ($request->action === 'ambil') {
            $lokasi = DB::selectOne("SELECT main_lokasi FROM asset_master_main_lokasi WHERE id = ?", [$request->id_tujuan]);

            if (!$lokasi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tujuan tidak ditemukan.',
                ], 404);
            }

            $tujuan = $lokasi->main_lokasi;
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $rows = [];
        foreach ($tags as $rfidCode) {
            $rows[] = [
                'tgl_trans' => $timestamp->format('Y-m-d'),
                'rfid_code' => $rfidCode,
                'enroll_id' => $enrollId,
                'tujuan' => $tujuan,
                'status' => $request->action === 'ambil' ? 'AMBIL' : 'KEMBALI',
                'tipe_input' => strtoupper($request->mode),
                'created_by' => $user,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::beginTransaction();

        try {
            // Status dicek ulang di sini (dengan kunci baris), karena bisa berubah sejak tag discan,
            // mis. tag yang sama diambil dari perangkat lain. Kalau ada yang tidak valid, tidak ada yang disimpan.
            $invalid = array_filter($this->validateTags($tags, $request->action, true), fn($check) => !$check['ok']);

            if ($invalid) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => count($invalid) . ' tag tidak bisa diproses, transaksi dibatalkan.',
                    'invalid' => array_map(fn($code, $check) => ['code' => (string) $code, 'message' => $check['message']], array_keys($invalid), $invalid),
                ], 409);
            }

            DB::table('asset_trans_tab')->insert($rows);

            DB::table('asset_master_tab')
                ->whereIn('rfid_code', $tags)
                ->update([
                    'status' => $request->action === 'ambil' ? 'TAKEN' : 'IDLE',
                    'lokasi' => $request->action === 'ambil' ? $tujuan : 'IT',
                    'updated_at' => $timestamp,
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => count($tags) . ' tag berhasil diproses',
            'count' => count($tags),
        ]);
    }
}
