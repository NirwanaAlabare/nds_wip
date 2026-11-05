<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Exports\WorkTrackerExport;
use App\Services\WorkTrackerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

// Work Tracker: plan, bug, dan request per programmer. Perhitungan pace ada di WorkTrackerService.
class WorkTrackerController extends Controller
{
    const ARCHIVE_AFTER_DAYS = 60;

    protected $tracker;

    public function __construct(WorkTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    public function index()
    {
        return view('general.tools.work-tracker', ['containerFluid' => true]);
    }

    public function data(Request $request)
    {
        $today = Carbon::today();

        if ($request->boolean('demo')) {
            return response()->json($this->tracker->demo($today));
        }

        $query = DB::table('work_items')->whereNull('deleted_at')->orderBy('target_date')->orderBy('id');

        if (!$request->boolean('archive')) {
            $cutoff = $today->copy()->subDays(self::ARCHIVE_AFTER_DAYS);
            $query->where(function ($q) use ($cutoff) {
                $q->whereIn('status', WorkTrackerService::ACTIVE)->orWhere('updated_at', '>=', $cutoff);
            });
        }

        return response()->json($this->tracker->board($query->get(), $this->activeProgrammers(), $today));
    }

    public function report(Request $request)
    {
        return response()->json(['items' => $this->reportRows($request)]);
    }

    public function export(Request $request)
    {
        $rows = $this->reportRows($request);

        return Excel::download(new WorkTrackerExport($rows), "work-tracker_{$request->from}_{$request->to}.xlsx");
    }

    protected function reportRows(Request $request)
    {
        $filters = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'programmer' => 'nullable|string|max:20',
            'type' => ['nullable', Rule::in(array_keys(WorkTrackerService::TYPE_LABEL))],
            'status' => ['nullable', Rule::in(array_merge(['open'], array_keys(WorkTrackerService::STATUS_LABEL)))],
            'level' => ['nullable', Rule::in(array_merge(['none'], array_keys(WorkTrackerService::LEVEL_LABEL)))],
            'health' => ['nullable', Rule::in(array_keys(WorkTrackerService::HEALTH_LABEL))],
            'search' => 'nullable|string|max:100',
        ], ['to.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal dari']);

        $today = Carbon::today();

        if ($request->boolean('demo')) {
            return $this->tracker->demoReport($today, $filters);
        }

        $names = DB::table('work_programmers')
            ->join('users', 'users.id', '=', 'work_programmers.user_id')
            ->pluck('users.name', 'work_programmers.id');

        return $this->tracker->report(DB::table('work_items')->whereNull('deleted_at')->get(), $names, $today, $filters);
    }

    public function users()
    {
        $taken = DB::table('work_programmers')->where('is_active', true)->pluck('user_id');

        return response()->json(
            DB::table('users')->whereNull('deleted_at')->whereNotIn('id', $taken)->orderBy('name')->get(['id', 'name', 'username'])
        );
    }

    public function storeProgrammer(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        DB::table('work_programmers')->updateOrInsert(
            ['user_id' => $validated['user_id']],
            ['color' => $validated['color'], 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['status' => 200, 'message' => 'Programmer ditambahkan']);
    }

    // Programmer dinonaktifkan, bukan dihapus. Kerjaan aktifnya kembali ke belum ditugaskan.
    public function deleteProgrammer(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer|exists:work_programmers,id']);

        DB::transaction(function () use ($validated) {
            DB::table('work_programmers')->where('id', $validated['id'])->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('work_items')->where('programmer_id', $validated['id'])->whereIn('status', WorkTrackerService::ACTIVE)
                ->update(['programmer_id' => null, 'updated_at' => now()]);
        });

        return response()->json(['status' => 200, 'message' => 'Programmer dinonaktifkan']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:work_items,id',
            'type' => ['required', Rule::in(array_keys(WorkTrackerService::PREFIX))],
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'module' => 'nullable|string|max:100',
            'requester' => 'nullable|string|max:100',
            'requester_dept' => 'nullable|string|max:100',
            'level' => ['nullable', Rule::in(['major', 'minor', 'tidak'])],
            'programmer_id' => 'nullable|integer|exists:work_programmers,id',
            'start_date' => 'nullable|date',
            'target_date' => 'nullable|date|after_or_equal:start_date',
            'estimate_days' => 'nullable|numeric|min:0.5|max:999',
            'reject_reason' => 'nullable|required_if:level,tidak|string|max:500',
        ], [
            'reject_reason.required_if' => 'Alasan wajib diisi kalau request tidak dikerjakan',
            'target_date.after_or_equal' => 'Target tidak boleh sebelum tanggal mulai',
        ]);

        $id = $validated['id'] ?? null;
        unset($validated['id']);

        if ($validated['type'] === 'plan') {
            $validated['level'] = null;
        }

        $user = auth()->user()->username;
        $rejected = ($validated['level'] ?? null) === 'tidak';
        $validated['reject_reason'] = $rejected ? $validated['reject_reason'] : null;
        $validated['updated_by'] = $user;
        $validated['updated_at'] = now();

        DB::transaction(function () use (&$id, $validated, $rejected, $user) {
            if (!$id) {
                $validated['status'] = $rejected ? 'ditolak' : 'baru';
                $validated['created_by'] = $user;
                $validated['created_at'] = now();
                $id = DB::table('work_items')->insertGetId($validated);
                $this->log($id, null, 0, $validated['status'], $rejected ? $validated['reject_reason'] : 'Kerjaan dicatat');
                return;
            }

            $current = DB::table('work_items')->where('id', $id)->whereNull('deleted_at')->lockForUpdate()->first();
            abort_if(!$current, 404, 'Kerjaan tidak ditemukan');

            if ($rejected && $current->status !== 'ditolak') {
                $validated['status'] = 'ditolak';
                $validated['finished_at'] = null;
                $this->log($id, $current->progress, $current->progress, 'ditolak', $validated['reject_reason']);
            } elseif (!$rejected && $current->status === 'ditolak') {
                if ($validated['type'] === 'plan') {
                    // Plan tidak punya pilihan level, jadi membukanya kembali hanya lewat status
                    unset($validated['reject_reason']);
                } else {
                    $validated['status'] = 'baru';
                    $this->log($id, $current->progress, $current->progress, 'baru', 'Dibuka kembali');
                }
            }

            DB::table('work_items')->where('id', $id)->update($validated);
        });

        return response()->json(['status' => 200, 'message' => 'Kerjaan tersimpan', 'id' => $id]);
    }

    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'progress' => 'required|integer|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $item = DB::table('work_items')->where('id', $validated['id'])->whereNull('deleted_at')->lockForUpdate()->first();
            abort_if(!$item, 404, 'Kerjaan tidak ditemukan');
            abort_if($item->status === 'ditolak', 422, 'Kerjaan yang ditolak tidak bisa di-update progressnya');

            $progress = (int) $validated['progress'];
            $changes = ['progress' => $progress, 'updated_by' => auth()->user()->username, 'updated_at' => now()];

            if ($progress >= 100) {
                $changes['status'] = 'selesai';
                $changes['finished_at'] = $item->status === 'selesai' ? $item->finished_at : Carbon::today()->toDateString();
            } else {
                $changes['status'] = $progress > 0 ? 'dikerjakan' : ($item->status === 'selesai' ? 'dikerjakan' : $item->status);
                $changes['finished_at'] = null;
            }

            if ($progress > 0 && !$item->start_date) {
                $changes['start_date'] = Carbon::today()->toDateString();
            }

            DB::table('work_items')->where('id', $item->id)->update($changes);
            $this->log($item->id, $item->progress, $progress, $changes['status'], $validated['note'] ?? null);

            return ['done' => $changes['status'] === 'selesai' && $item->status !== 'selesai'];
        });

        return response()->json(['status' => 200, 'message' => 'Progress tersimpan', 'just_finished' => $result['done']]);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'status' => ['required', Rule::in(['baru', 'dikerjakan', 'selesai', 'ditolak'])],
            'reason' => 'nullable|required_if:status,ditolak|string|max:500',
        ], ['reason.required_if' => 'Alasan wajib diisi']);

        $result = DB::transaction(function () use ($validated) {
            $item = DB::table('work_items')->where('id', $validated['id'])->whereNull('deleted_at')->lockForUpdate()->first();
            abort_if(!$item, 404, 'Kerjaan tidak ditemukan');

            $status = $validated['status'];
            $changes = ['status' => $status, 'updated_by' => auth()->user()->username, 'updated_at' => now()];

            if ($status === 'selesai') {
                $changes['progress'] = 100;
                $changes['finished_at'] = $item->status === 'selesai' ? $item->finished_at : Carbon::today()->toDateString();
            } else {
                $changes['finished_at'] = null;
                if ($item->status === 'selesai') {
                    $changes['progress'] = 90;
                }
            }

            if ($status === 'dikerjakan' && !$item->start_date) {
                $changes['start_date'] = Carbon::today()->toDateString();
            }

            if ($status === 'ditolak') {
                $changes['reject_reason'] = $validated['reason'];
                $changes['level'] = $item->type === 'plan' ? null : 'tidak';
            } elseif ($item->status === 'ditolak') {
                $changes['reject_reason'] = null;
                $changes['level'] = null;
            }

            DB::table('work_items')->where('id', $item->id)->update($changes);
            $this->log($item->id, $item->progress, $changes['progress'] ?? $item->progress, $status, $validated['reason'] ?? null);

            return ['done' => $status === 'selesai' && $item->status !== 'selesai'];
        });

        return response()->json(['status' => 200, 'message' => 'Status diubah', 'just_finished' => $result['done']]);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'programmer_id' => ['nullable', 'integer', Rule::exists('work_programmers', 'id')->where('is_active', true)],
        ]);

        $programmerId = $validated['programmer_id'] ?? null;

        DB::transaction(function () use ($validated, $programmerId) {
            $item = DB::table('work_items')->where('id', $validated['id'])->whereNull('deleted_at')->lockForUpdate()->first();
            abort_if(!$item, 404, 'Kerjaan tidak ditemukan');
            abort_if(!in_array($item->status, WorkTrackerService::ACTIVE), 422, 'Hanya kerjaan yang belum selesai yang bisa ditugaskan');

            if ((int) $item->programmer_id === (int) $programmerId) {
                return;
            }

            DB::table('work_items')->where('id', $item->id)->update([
                'programmer_id' => $programmerId,
                'updated_by' => auth()->user()->username,
                'updated_at' => now(),
            ]);

            $name = $programmerId
                ? DB::table('work_programmers')->join('users', 'users.id', '=', 'work_programmers.user_id')->where('work_programmers.id', $programmerId)->value('users.name')
                : null;

            $this->log($item->id, $item->progress, $item->progress, $item->status, $name ? "Ditugaskan ke {$name}" : 'Penugasan dilepas');
        });

        return response()->json(['status' => 200, 'message' => $programmerId ? 'Kerjaan ditugaskan' : 'Penugasan dilepas']);
    }

    public function delete(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer']);

        DB::table('work_items')->where('id', $validated['id'])->update([
            'deleted_at' => now(),
            'updated_by' => auth()->user()->username,
        ]);

        return response()->json(['status' => 200, 'message' => 'Kerjaan dihapus']);
    }

    public function logs($id)
    {
        return response()->json(
            DB::table('work_item_logs')->where('work_item_id', $id)->orderByDesc('id')->limit(100)->get()
        );
    }

    protected function activeProgrammers()
    {
        return DB::table('work_programmers')
            ->join('users', 'users.id', '=', 'work_programmers.user_id')
            ->where('work_programmers.is_active', true)
            ->orderBy('users.name')
            ->get(['work_programmers.id', 'work_programmers.user_id', 'work_programmers.color', 'users.name']);
    }

    protected function log($itemId, $before, $after, $status, $note)
    {
        DB::table('work_item_logs')->insert([
            'work_item_id' => $itemId,
            'progress_before' => $before,
            'progress_after' => $after,
            'status' => $status,
            'note' => $note,
            'created_by' => auth()->user()->username,
            'created_at' => now(),
        ]);
    }
}
