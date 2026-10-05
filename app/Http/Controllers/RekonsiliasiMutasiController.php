<?php

namespace App\Http\Controllers;

use App\Services\RekonsiliasiMutasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekonsiliasiMutasiController extends Controller
{
    // User yang boleh membuka menu ini (juga dipakai navbar untuk menampilkan menunya)
    public const ALLOWED_USERNAMES = ['admin_01', 'tian', 'eva', 'ibrahim','nirwana_it'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!in_array(optional(Auth::user())->username, self::ALLOWED_USERNAMES, true)) {
                abort(403, 'Anda tidak punya akses ke menu Rekonsiliasi Mutasi.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $reports = collect(RekonsiliasiMutasiService::reports())
            ->map(fn($def, $key) => ['key' => $key, 'label' => $def['label']])
            ->values();

        return view('rekonsiliasi-mutasi.index', ['reports' => $reports, 'page' => 'dashboard-warehouse']);
    }

    // Hitung satu laporan. Untuk rentang tanggal panjang bisa lebih lama dari timeout Apache, jadi proses
    // dibiarkan tetap jalan walau koneksi browser putus; hasilnya masuk cache dan dipantau lewat status().
    public function hitung(Request $request)
    {
        $params = $this->validateParams($request, true);

        ignore_user_abort(true);
        set_time_limit(0);

        $result = RekonsiliasiMutasiService::hitung($params['report'], $params['from'], $params['to'], $request->boolean('refresh'));

        return response()->json($this->ringkas($result));
    }

    public function status(Request $request)
    {
        $params = $this->validateParams($request, true);

        $profile = RekonsiliasiMutasiService::getProfile($params['report'], $params['from'], $params['to']);
        if ($profile) {
            return response()->json($this->ringkas(['status' => 'done', 'profile' => $profile]));
        }

        $running = RekonsiliasiMutasiService::isRunning($params['report'], $params['from'], $params['to']);

        return response()->json(['status' => $running ? 'running' : 'none']);
    }

    public function hasil(Request $request)
    {
        $params = $this->validateParams($request, false);

        return response()->json(RekonsiliasiMutasiService::hasil($params['from'], $params['to']));
    }

    private function validateParams(Request $request, $withReport)
    {
        $rules = [
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
        ];
        if ($withReport) {
            $rules['report'] = 'required|in:' . implode(',', array_keys(RekonsiliasiMutasiService::reports()));
        }

        return $request->validate($rules);
    }

    // Kirim ringkasan saja, data per dokumen/barcode tetap di cache
    private function ringkas(array $result)
    {
        if ($result['status'] !== 'done') {
            return ['status' => $result['status']];
        }

        $profile = $result['profile'];

        return [
            'status' => 'done',
            'rows' => $profile['rows'],
            'seconds' => $profile['seconds'],
            'computed_at' => $profile['computed_at'],
            'total' => $profile['total'],
        ];
    }
}
