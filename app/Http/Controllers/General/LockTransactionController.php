<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Middleware\LockTransactionMiddleware;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LockTransactionController extends Controller
{
    public function index()
    {
        // Daftar nama route untuk pilihan route_name di detail
        $routeNames = collect(Route::getRoutes()->getRoutesByName())
            ->keys()
            ->sort()
            ->values();

        return view("general.tools.lock-transaction", [
            'routeNames' => $routeNames,
        ]);
    }

    // ===== Lock Transaction (header) =====

    public function getData(Request $request)
    {
        $query = DB::table('lock_transaction as a')
            ->selectRaw("
                a.id,
                a.transaction_name,
                a.is_locked,
                CASE WHEN a.is_locked = '1' THEN 'Locked' ELSE 'Unlocked' END AS status,
                (SELECT COUNT(*) FROM lock_transaction_detail d WHERE d.transaction_id = a.id) AS detail_count,
                a.created_by_username,
                a.created_at,
                a.updated_at
            ")
            ->orderBy('a.id', 'desc');

        return DataTables::query($query)
            ->filterColumn('transaction_name', fn ($q, $keyword) => $q->where('a.transaction_name', 'like', "%$keyword%"))
            ->filterColumn('status', fn ($q, $keyword) => $q->whereRaw("CASE WHEN a.is_locked = '1' THEN 'Locked' ELSE 'Unlocked' END LIKE ?", ["%$keyword%"]))
            ->filterColumn('created_by_username', fn ($q, $keyword) => $q->where('a.created_by_username', 'like', "%$keyword%"))
            ->filterColumn('created_at', fn ($q, $keyword) => $q->where('a.created_at', 'like', "%$keyword%"))
            ->toJson();
    }

    // Tambah (tanpa id) atau ubah nama transaksi (dengan id)
    public function store(Request $request)
    {
        $request->validate([
            'id'               => 'nullable|integer|exists:lock_transaction,id',
            'transaction_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lock_transaction', 'transaction_name')->ignore($request->id),
            ],
        ]);

        $now = now()->format('Y-m-d H:i:s');

        if ($request->id) {
            $old = DB::table('lock_transaction')->where('id', $request->id)->first();

            DB::table('lock_transaction')
                ->where('id', $request->id)
                ->update([
                    'transaction_name' => $request->transaction_name,
                    'updated_at'       => $now,
                ]);

            $id = $request->id;
            $message = 'Transaksi berhasil diubah';
        } else {
            $old = null;

            $id = DB::table('lock_transaction')->insertGetId([
                'transaction_name'    => $request->transaction_name,
                'is_locked'           => '0',
                'created_by'          => auth()->user()->id,
                'created_by_username' => auth()->user()->username,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            $message = 'Transaksi berhasil disimpan';
        }

        LockTransactionMiddleware::clearCache();
        logHistory($id, [
            'old' => $old ? (array) $old : null,
            'new' => (array) DB::table('lock_transaction')->where('id', $id)->first(),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => $message,
        ]);
    }

    // Hapus transaksi beserta detailnya (hanya jika belum di-lock)
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $old = DB::table('lock_transaction')->where('id', $request->id)->first();

        if (!$old) {
            return response()->json([
                'status'  => 404,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($old->is_locked == '1') {
            return response()->json([
                'status'  => 422,
                'message' => 'Transaksi masih di-lock, unlock terlebih dahulu',
            ], 422);
        }

        $oldDetails = DB::table('lock_transaction_detail')->where('transaction_id', $request->id)->get();

        DB::transaction(function () use ($request) {
            DB::table('lock_transaction_detail')->where('transaction_id', $request->id)->delete();
            DB::table('lock_transaction')->where('id', $request->id)->delete();
        });

        LockTransactionMiddleware::clearCache();
        logHistory($request->id, [
            'old' => (array) $old + ['details' => $oldDetails->toArray()],
            'new' => null,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }

    public function locked(Request $request)
    {
        return $this->setLocked($request, '1', 'Transaksi berhasil di-lock');
    }

    public function unlocked(Request $request)
    {
        return $this->setLocked($request, '0', 'Transaksi berhasil di-unlock');
    }

    private function setLocked(Request $request, $isLocked, $message)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $old = DB::table('lock_transaction')->where('id', $request->id)->first();

        if (!$old) {
            return response()->json([
                'status'  => 404,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::table('lock_transaction')
            ->where('id', $request->id)
            ->update([
                'is_locked'  => $isLocked,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        LockTransactionMiddleware::clearCache();
        logHistory($request->id, [
            'old' => (array) $old,
            'new' => (array) DB::table('lock_transaction')->where('id', $request->id)->first(),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => $message,
        ]);
    }

    // ===== Lock Transaction Detail =====

    public function getDetail(Request $request)
    {
        $query = DB::table('lock_transaction_detail as d')
            ->selectRaw("
                d.id,
                d.transaction_id,
                d.transaction_detail_name,
                d.route_name,
                d.created_by_username,
                d.created_at,
                d.updated_at
            ")
            ->where('d.transaction_id', $request->transaction_id)
            ->orderBy('d.id', 'desc');

        return DataTables::query($query)
            ->filterColumn('transaction_detail_name', fn ($q, $keyword) => $q->where('d.transaction_detail_name', 'like', "%$keyword%"))
            ->filterColumn('route_name', fn ($q, $keyword) => $q->where('d.route_name', 'like', "%$keyword%"))
            ->filterColumn('created_by_username', fn ($q, $keyword) => $q->where('d.created_by_username', 'like', "%$keyword%"))
            ->filterColumn('created_at', fn ($q, $keyword) => $q->where('d.created_at', 'like', "%$keyword%"))
            ->toJson();
    }

    // Tambah banyak route sekaligus (tanpa id) atau ubah 1 detail (dengan id)
    // Nama detail kosong => diisi nama route-nya
    public function storeDetail(Request $request)
    {
        $request->validate([
            'id'                      => 'nullable|integer|exists:lock_transaction_detail,id',
            'transaction_id'          => 'required|integer|exists:lock_transaction,id',
            'transaction_detail_name' => 'nullable|string|max:255',
            'route_names'             => 'required|array|min:1',
            'route_names.*'           => 'required|string|max:255',
        ], [
            'route_names.required' => 'Route name wajib dipilih',
        ]);

        $routeNames = array_values(array_unique($request->route_names));

        if ($request->id && count($routeNames) > 1) {
            return response()->json([
                'status'  => 422,
                'message' => 'Saat edit hanya boleh memilih 1 route',
            ], 422);
        }

        $notFound = array_values(array_filter($routeNames, fn ($routeName) => !Route::has($routeName)));

        if ($notFound) {
            return response()->json([
                'status'  => 422,
                'message' => 'Route tidak ditemukan: ' . implode(', ', $notFound),
            ], 422);
        }

        // Route yang sama tidak boleh didaftarkan dua kali di transaksi yang sama
        $registered = DB::table('lock_transaction_detail')
            ->where('transaction_id', $request->transaction_id)
            ->whereIn('route_name', $routeNames)
            ->when($request->id, fn ($q) => $q->where('id', '!=', $request->id))
            ->pluck('route_name')
            ->all();

        if ($registered) {
            return response()->json([
                'status'  => 422,
                'message' => 'Route sudah terdaftar di transaksi ini: ' . implode(', ', $registered),
            ], 422);
        }

        $detailName = trim((string) $request->transaction_detail_name);
        $now = now()->format('Y-m-d H:i:s');

        if ($request->id) {
            $old = DB::table('lock_transaction_detail')->where('id', $request->id)->first();

            DB::table('lock_transaction_detail')
                ->where('id', $request->id)
                ->update([
                    'transaction_detail_name' => $detailName !== '' ? $detailName : $routeNames[0],
                    'route_name'              => $routeNames[0],
                    'updated_at'              => $now,
                ]);

            LockTransactionMiddleware::clearCache();
            logHistory($request->id, [
                'old' => (array) $old,
                'new' => (array) DB::table('lock_transaction_detail')->where('id', $request->id)->first(),
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Detail berhasil diubah',
            ]);
        }

        $ids = DB::transaction(function () use ($request, $routeNames, $detailName, $now) {
            $ids = [];

            foreach ($routeNames as $routeName) {
                $ids[] = DB::table('lock_transaction_detail')->insertGetId([
                    'transaction_id'          => $request->transaction_id,
                    'transaction_detail_name' => $detailName !== '' ? $detailName : $routeName,
                    'route_name'              => $routeName,
                    'created_by'              => auth()->user()->id,
                    'created_by_username'     => auth()->user()->username,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);
            }

            return $ids;
        });

        LockTransactionMiddleware::clearCache();
        foreach ($ids as $id) {
            logHistory($id, [
                'old' => null,
                'new' => (array) DB::table('lock_transaction_detail')->where('id', $id)->first(),
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => count($ids) . ' detail berhasil disimpan',
        ]);
    }

    public function deleteDetail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $old = DB::table('lock_transaction_detail')->where('id', $request->id)->first();

        if (!$old) {
            return response()->json([
                'status'  => 404,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::table('lock_transaction_detail')->where('id', $request->id)->delete();

        LockTransactionMiddleware::clearCache();
        logHistory($request->id, [
            'old' => (array) $old,
            'new' => null,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Detail berhasil dihapus',
        ]);
    }
}
