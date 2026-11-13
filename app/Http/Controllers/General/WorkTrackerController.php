<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Exports\WorkTrackerExport;
use App\Services\WorkTrackerDashboardService;
use App\Services\WorkTrackerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

// Work Tracker: plan, bug, dan request per programmer. Perhitungan pace ada di WorkTrackerService.
class WorkTrackerController extends Controller
{
    const ARCHIVE_AFTER_DAYS = 60;
    const MAX_FILES = 10;
    const MAX_FILE_KB = 10240;
    const FILE_TYPES = 'jpg,jpeg,png,webp,gif,pdf,xls,xlsx,doc,docx,txt,csv,zip,html,htm';
    // File yang bisa menjalankan script kalau dibuka di browser: disimpan di luar folder publik dan hanya bisa diunduh
    const PRIVATE_TYPES = ['html', 'htm'];
    const PRIVATE_FOLDER = 'private/work_tracker';

    protected $tracker;

    public function __construct(WorkTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    public function index()
    {
        return view('general.tools.work-tracker', ['containerFluid' => true]);
    }

    public function dashboard()
    {
        return view('general.tools.work-tracker-dashboard', ['containerFluid' => true]);
    }

    // Dunia 3D: posisi pemain dan chat hanya disimpan sementara di cache, tidak ada tabel
    const WORLD_TTL = 12;
    const WORLD_CHAT_KEEP = 40;
    // Pendamping di Dunia 3D: user_id => karakter yang selalu menempel pada user itu (gambar di public/assets)
    const WORLD_COMPANIONS = [
        315 => ['name' => 'ADO', 'owner' => 'Dede', 'image' => 'img/work-tracker/ado.png'],
    ];

    public function world(Request $request)
    {
        $user = $request->user();
        $companions = [];
        foreach (self::WORLD_COMPANIONS as $userId => $companion) {
            $companions[$userId] = array_merge($companion, ['image' => asset($companion['image'])]);
        }

        return view('general.tools.work-tracker-world', [
            'containerFluid' => true,
            'me' => array_merge(['id' => $user->id, 'name' => $user->name], $this->worldProfile($user->id)),
            'companions' => (object) $companions,
        ]);
    }

    // Detak posisi: simpan posisi sendiri, kembalikan pemain lain yang masih aktif dan chat baru
    public function worldPresence(Request $request)
    {
        $validated = $request->validate([
            'x' => 'required|numeric|between:-500,500',
            'z' => 'required|numeric|between:-500,500',
            'ry' => 'required|numeric|between:-20,20',
            'moving' => 'nullable|boolean',
            'after' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $now = microtime(true);
        Cache::put('wt-world:p:' . $user->id, array_merge([
            'id' => $user->id,
            'name' => $user->name,
            'x' => round((float) $validated['x'], 2),
            'z' => round((float) $validated['z'], 2),
            'ry' => round((float) $validated['ry'], 3),
            'moving' => (bool) ($validated['moving'] ?? false),
            't' => $now,
        ], $this->worldProfile($user->id)), self::WORLD_TTL);

        $ids = Cache::get('wt-world:ids', []);
        if (!in_array($user->id, $ids, true)) {
            $ids[] = $user->id;
        }

        $players = [];
        $alive = [];
        foreach ($ids as $id) {
            $player = Cache::get('wt-world:p:' . $id);
            if (!$player || $now - $player['t'] > self::WORLD_TTL) continue;
            $alive[] = $id;
            if ($id !== $user->id) $players[] = $player;
        }
        Cache::put('wt-world:ids', $alive, 3600);

        $after = (int) ($validated['after'] ?? 0);
        $chat = array_values(array_filter(Cache::get('wt-world:chat', []), function ($message) use ($after) {
            return $message['n'] > $after;
        }));

        return response()->json(['players' => $players, 'chat' => $chat]);
    }

    public function worldChat(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:200',
            'cid' => 'required|string|max:40',
        ]);

        $user = $request->user();
        $text = trim(preg_replace('/\s+/', ' ', $validated['text']));
        if ($text === '') {
            return response()->json(['status' => 422, 'message' => 'Pesan kosong'], 422);
        }

        $seq = (int) Cache::get('wt-world:chat-seq', 0) + 1;
        Cache::forever('wt-world:chat-seq', $seq);

        $chat = Cache::get('wt-world:chat', []);
        $message = [
            'n' => $seq,
            'cid' => $validated['cid'],
            'user_id' => $user->id,
            'name' => $user->name,
            'text' => $text,
            'at' => now()->format('H:i'),
        ];
        $chat[] = $message;
        Cache::put('wt-world:chat', array_slice($chat, -self::WORLD_CHAT_KEEP), 1800);

        return response()->json(['status' => 200, 'message' => $message]);
    }

    // Warna dan divisi pemain diambil dari data anggota; bukan anggota tetap bisa masuk sebagai tamu
    protected function worldProfile($userId)
    {
        return Cache::remember('wt-world:profile:' . $userId, 60, function () use ($userId) {
            $member = DB::table('work_programmers')->where('user_id', $userId)->where('is_active', true)->first(['id', 'color', 'division']);

            return [
                'member_id' => $member->id ?? null,
                'color' => $member->color ?? null,
                'division' => $member->division ?? null,
            ];
        });
    }

    public function dashboardData(Request $request, WorkTrackerDashboardService $dashboard)
    {
        $filters = $request->validate([
            'division' => 'nullable|string|max:50',
            'period' => ['nullable', Rule::in(WorkTrackerDashboardService::PERIODS)],
        ]);
        $today = Carbon::today();

        if ($request->boolean('demo')) {
            return response()->json($dashboard->demo($today, $filters));
        }

        $items = DB::table('work_items')->whereNull('deleted_at')->get();
        $logs = DB::table('work_item_logs')
            ->whereIn('work_item_id', $items->pluck('id'))
            ->get(['id', 'work_item_id', 'progress_before', 'progress_after', 'created_at']);
        $divisions = DB::table('work_programmers')->pluck('division', 'id');

        return response()->json($dashboard->build($items, $logs, $this->activeProgrammers(), $divisions, $today, $filters));
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

        $items = $query->get();
        $ids = $items->pluck('id');
        $logs = DB::table('work_item_logs')
            ->whereIn('work_item_id', $ids)
            ->get(['id', 'work_item_id', 'progress_before', 'progress_after', 'status', 'created_at']);

        // Jumlah poin dan lampiran untuk penanda di kartu
        $points = DB::table('work_item_points')->whereIn('work_item_id', $ids)->whereNull('deleted_at')
            ->groupBy('work_item_id')->selectRaw('work_item_id, COUNT(*) AS total, SUM(is_done) AS done')->get()->keyBy('work_item_id');
        $files = DB::table('work_item_attachments')->whereIn('work_item_id', $ids)->whereNull('deleted_at')
            ->groupBy('work_item_id')->selectRaw('work_item_id, COUNT(*) AS total')->pluck('total', 'work_item_id');

        foreach ($items as $item) {
            $item->points_total = isset($points[$item->id]) ? (int) $points[$item->id]->total : 0;
            $item->points_done = isset($points[$item->id]) ? (int) $points[$item->id]->done : 0;
            $item->attachments_count = (int) ($files[$item->id] ?? 0);
        }

        return response()->json($this->tracker->board($items, $this->activeProgrammers(), $today, $logs));
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
            // Beberapa divisi dikirim dipisah koma, misalnya "Programmer,IE"
            'division' => 'nullable|string|max:255',
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
        $divisions = DB::table('work_programmers')->pluck('division', 'id');

        return $this->tracker->report(DB::table('work_items')->whereNull('deleted_at')->get(), $names, $divisions, $today, $filters);
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
            'division' => 'required|string|max:50',
        ]);

        $existing = DB::table('work_programmers')->distinct()->pluck('division');
        $values = [
            'color' => $validated['color'],
            'division' => $this->tracker->normalizeDivision($validated['division'], $existing),
            'is_active' => true,
            'updated_at' => now(),
        ];

        if (DB::table('work_programmers')->where('user_id', $validated['user_id'])->exists()) {
            DB::table('work_programmers')->where('user_id', $validated['user_id'])->update($values);
        } else {
            DB::table('work_programmers')->insert(array_merge($values, ['user_id' => $validated['user_id'], 'created_at' => now()]));
        }

        return response()->json(['status' => 200, 'message' => 'Anggota disimpan']);
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

        return response()->json(['status' => 200, 'message' => 'Anggota dinonaktifkan']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
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
            'points' => 'nullable|json',
        ], $this->fileRules()), array_merge([
            'reject_reason.required_if' => 'Alasan wajib diisi kalau request tidak dikerjakan',
            'target_date.after_or_equal' => 'Target tidak boleh sebelum tanggal mulai',
        ], $this->fileMessages()));

        $id = $validated['id'] ?? null;
        $points = $this->formPoints($validated['points'] ?? null);
        $files = $request->file('files', []);
        unset($validated['id'], $validated['points'], $validated['files']);

        if ($validated['type'] === 'plan') {
            $validated['level'] = null;
        }

        $user = auth()->user()->username;
        $rejected = ($validated['level'] ?? null) === 'tidak';
        $validated['reject_reason'] = $rejected ? $validated['reject_reason'] : null;
        $validated['updated_by'] = $user;
        $validated['updated_at'] = now();

        DB::transaction(function () use (&$id, $validated, $rejected, $user, $points, $files) {
            if (!$id) {
                $validated['status'] = $rejected ? 'ditolak' : 'baru';
                $validated['created_by'] = $user;
                $validated['created_at'] = now();
                $id = DB::table('work_items')->insertGetId($validated);
                $this->log($id, null, 0, $validated['status'], $rejected ? $validated['reject_reason'] : 'Kerjaan dicatat');
                $this->syncPoints($id, $points, true);
                $this->storeFiles($files, $id);
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
            $this->syncPoints($id, $points);
            $this->storeFiles($files, $id);
        });

        return response()->json(['status' => 200, 'message' => 'Kerjaan tersimpan', 'id' => $id]);
    }

    public function updateProgress(Request $request)
    {
        $validated = $request->validate(array_merge([
            'id' => 'required|integer',
            'progress' => 'required|integer|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ], $this->fileRules()), $this->fileMessages());

        $files = $request->file('files', []);

        $result = DB::transaction(function () use ($validated, $files) {
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
            $logId = $this->log($item->id, $item->progress, $progress, $changes['status'], $validated['note'] ?? null);
            $this->storeFiles($files, $item->id, null, $logId);

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

    // Dipakai Timeline saat batang digeser: mengubah tanggal, dan anggota kalau dipindah ke baris lain
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'start_date' => 'nullable|date',
            'target_date' => 'nullable|date|after_or_equal:start_date',
            'programmer_id' => ['nullable', 'integer', Rule::exists('work_programmers', 'id')->where('is_active', true)],
        ], ['target_date.after_or_equal' => 'Target tidak boleh sebelum tanggal mulai']);

        $start = $validated['start_date'] ?? null;
        $target = $validated['target_date'] ?? null;
        $movesPerson = $request->exists('programmer_id');
        $programmerId = $validated['programmer_id'] ?? null;

        DB::transaction(function () use ($validated, $start, $target, $movesPerson, $programmerId) {
            $item = DB::table('work_items')->where('id', $validated['id'])->whereNull('deleted_at')->lockForUpdate()->first();
            abort_if(!$item, 404, 'Kerjaan tidak ditemukan');
            abort_if(!in_array($item->status, WorkTrackerService::ACTIVE), 422, 'Hanya kerjaan yang belum selesai yang bisa digeser');

            $changes = $this->tracker->rescheduleChanges($item, $start, $target);
            $notes = [];

            if ($item->start_date !== $start || $item->target_date !== $target) {
                $notes[] = 'Jadwal diubah: ' . ($item->start_date ?: '-') . ' s/d ' . ($item->target_date ?: '-') . ' menjadi ' . ($start ?: '-') . ' s/d ' . ($target ?: '-');
            }

            if ($movesPerson && (int) $item->programmer_id !== (int) $programmerId) {
                $changes['programmer_id'] = $programmerId;
                $name = $programmerId
                    ? DB::table('work_programmers')->join('users', 'users.id', '=', 'work_programmers.user_id')->where('work_programmers.id', $programmerId)->value('users.name')
                    : null;
                $notes[] = $name ? "Ditugaskan ke {$name}" : 'Penugasan dilepas';
            }

            if (!$notes) {
                return;
            }

            DB::table('work_items')->where('id', $item->id)->update(array_merge($changes, [
                'updated_by' => auth()->user()->username,
                'updated_at' => now(),
            ]));
            $this->log($item->id, $item->progress, $item->progress, $item->status, implode('. ', $notes));
        });

        return response()->json(['status' => 200, 'message' => 'Jadwal disimpan']);
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

    // Isi modal detail: riwayat, poin, dan semua lampiran (kerjaan, poin, update progress)
    public function detail($id)
    {
        $attachments = DB::table('work_item_attachments')->where('work_item_id', $id)->whereNull('deleted_at')->orderBy('id')
            ->get(['id', 'point_id', 'log_id', 'path', 'original_name', 'mime', 'size', 'created_by', 'created_at'])
            ->map(function ($file) {
                $private = $this->isPrivatePath($file->path);
                $file->url = $private ? route('download-attachment-work-tracker', $file->id) : url('storage/' . $file->path);
                $file->download_only = $private;
                $file->is_image = !$private && strpos((string) $file->mime, 'image/') === 0;
                unset($file->path);
                return $file;
            });

        return response()->json([
            'logs' => DB::table('work_item_logs')->where('work_item_id', $id)->orderByDesc('id')->limit(100)->get(),
            'points' => DB::table('work_item_points')->where('work_item_id', $id)->whereNull('deleted_at')->orderBy('sort_order')->orderBy('id')
                ->get(['id', 'content', 'is_done', 'done_at', 'done_by']),
            'attachments' => $attachments,
        ]);
    }

    // Tambah poin baru, atau ubah isi poin kalau id dikirim
    public function storePoint(Request $request)
    {
        $validated = $request->validate(array_merge([
            'id' => 'nullable|integer',
            'work_item_id' => 'required_without:id|nullable|integer',
            'content' => 'required|string|max:500',
        ], $this->fileRules()), $this->fileMessages());

        $content = trim($validated['content']);
        $files = $request->file('files', []);

        DB::transaction(function () use ($validated, $content, $files) {
            if (!empty($validated['id'])) {
                $point = $this->findPoint($validated['id']);
                DB::table('work_item_points')->where('id', $point->id)->update([
                    'content' => $content,
                    'updated_by' => auth()->user()->username,
                    'updated_at' => now(),
                ]);
                $this->storeFiles($files, $point->work_item_id, $point->id);
                return;
            }

            $item = $this->findItem($validated['work_item_id']);
            $pointId = $this->insertPoints($item->id, [$content]);
            $this->storeFiles($files, $item->id, $pointId);
            $this->log($item->id, $item->progress, $item->progress, $item->status, "Poin ditambah: {$content}");
        });

        return response()->json(['status' => 200, 'message' => empty($validated['id']) ? 'Poin ditambah' : 'Poin diubah']);
    }

    public function togglePoint(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer', 'done' => 'required|boolean']);
        $done = (bool) $validated['done'];

        DB::transaction(function () use ($validated, $done) {
            $point = $this->findPoint($validated['id']);

            if ((bool) $point->is_done === $done) {
                return;
            }

            $user = auth()->user()->username;
            DB::table('work_item_points')->where('id', $point->id)->update([
                'is_done' => $done,
                'done_at' => $done ? now() : null,
                'done_by' => $done ? $user : null,
                'updated_by' => $user,
                'updated_at' => now(),
            ]);

            $item = $this->findItem($point->work_item_id);
            $this->log($item->id, $item->progress, $item->progress, $item->status, ($done ? 'Poin selesai: ' : 'Poin dibuka lagi: ') . $point->content);
        });

        return response()->json(['status' => 200, 'message' => $done ? 'Poin selesai' : 'Poin dibuka lagi']);
    }

    public function deletePoint(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer']);

        DB::transaction(function () use ($validated) {
            $point = $this->findPoint($validated['id']);
            DB::table('work_item_points')->where('id', $point->id)->update(['deleted_at' => now(), 'updated_by' => auth()->user()->username]);
            DB::table('work_item_attachments')->where('point_id', $point->id)->whereNull('deleted_at')->update(['deleted_at' => now()]);

            $item = $this->findItem($point->work_item_id);
            $this->log($item->id, $item->progress, $item->progress, $item->status, "Poin dihapus: {$point->content}");
        });

        return response()->json(['status' => 200, 'message' => 'Poin dihapus']);
    }

    // Lampiran langsung ke kerjaan, atau ke salah satu poinnya
    public function storeAttachment(Request $request)
    {
        $rules = $this->fileRules();
        $rules['files'] = 'required|array|min:1|max:' . self::MAX_FILES;

        $validated = $request->validate(array_merge([
            'work_item_id' => 'required|integer',
            'point_id' => 'nullable|integer',
        ], $rules), array_merge(['files.required' => 'Pilih file dulu'], $this->fileMessages()));

        $item = $this->findItem($validated['work_item_id']);
        $pointId = null;

        if (!empty($validated['point_id'])) {
            $point = $this->findPoint($validated['point_id']);
            abort_if((int) $point->work_item_id !== (int) $item->id, 422, 'Poin bukan milik kerjaan ini');
            $pointId = $point->id;
        }

        $count = $this->storeFiles($request->file('files', []), $item->id, $pointId);

        return response()->json(['status' => 200, 'message' => "{$count} lampiran diunggah"]);
    }

    // Lampiran HTML selalu diunduh sebagai file, tidak pernah ditampilkan di browser
    public function downloadAttachment($id)
    {
        $file = DB::table('work_item_attachments')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$file || !$this->isPrivatePath($file->path), 404, 'Lampiran tidak ditemukan');

        $fullPath = storage_path('app/' . $file->path);
        abort_if(!is_file($fullPath), 404, 'File lampiran tidak ditemukan');

        return response()->download($fullPath, $file->original_name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }

    protected function isPrivatePath($path)
    {
        return strpos((string) $path, self::PRIVATE_FOLDER . '/') === 0 && strpos((string) $path, '..') === false;
    }

    // Lampiran hanya ditandai terhapus, filenya tetap di storage
    public function deleteAttachment(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer']);

        DB::table('work_item_attachments')->where('id', $validated['id'])->whereNull('deleted_at')->update(['deleted_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Lampiran dihapus']);
    }

    protected function fileRules()
    {
        return [
            'files' => 'nullable|array|max:' . self::MAX_FILES,
            'files.*' => 'file|max:' . self::MAX_FILE_KB . '|mimes:' . self::FILE_TYPES,
        ];
    }

    protected function fileMessages()
    {
        return [
            'files.max' => 'Maksimal ' . self::MAX_FILES . ' file sekali unggah',
            'files.*.max' => 'Ukuran file maksimal ' . (self::MAX_FILE_KB / 1024) . ' MB',
            'files.*.mimes' => 'Jenis file tidak didukung. Gunakan foto, PDF, Excel, Word, TXT, CSV, ZIP, atau HTML',
            'files.*.uploaded' => 'File gagal diunggah, kemungkinan ukurannya terlalu besar',
        ];
    }

    // Ekstensi diambil dari isi file, bukan dari nama aslinya, supaya file .php yang menyamar tidak tersimpan apa adanya
    protected function storeFiles($files, $itemId, $pointId = null, $logId = null)
    {
        $folder = 'work_tracker/' . date('Y/m');
        $user = auth()->user()->username;
        $rows = [];

        foreach (array_filter((array) $files) as $file) {
            $ext = strtolower($file->extension() ?: 'bin');
            $name = date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;

            // path "private/..." ada di storage/app (tidak bisa diakses langsung), selain itu di storage/app/public
            if (in_array($ext, self::PRIVATE_TYPES, true)) {
                $path = self::PRIVATE_FOLDER . '/' . date('Y/m') . '/' . $name;
                $file->storeAs(dirname($path), $name);
            } else {
                $path = $folder . '/' . $name;
                $file->storeAs('public/' . $folder, $name);
            }

            $rows[] = [
                'work_item_id' => $itemId,
                'point_id' => $pointId,
                'log_id' => $logId,
                'path' => $path,
                'original_name' => mb_substr($file->getClientOriginalName() ?: $name, 0, 255),
                'mime' => mb_substr((string) $file->getMimeType(), 0, 100),
                'size' => (int) $file->getSize(),
                'created_by' => $user,
                'created_at' => now(),
            ];
        }

        if ($rows) {
            DB::table('work_item_attachments')->insert($rows);
        }

        return count($rows);
    }

    // Daftar poin dari form: [{id, content, done}]. null berarti form tidak mengirim poin, jadi poin tidak disentuh.
    protected function formPoints($json)
    {
        if ($json === null) {
            return null;
        }

        $list = json_decode($json, true);
        abort_if(!is_array($list) || count($list) > 100, 422, 'Daftar poin tidak valid');

        return collect($list)->map(function ($point) {
            $content = is_array($point) ? mb_substr(trim((string) ($point['content'] ?? '')), 0, 500) : '';

            return [
                'id' => is_array($point) && !empty($point['id']) ? (int) $point['id'] : null,
                'content' => $content,
                'done' => is_array($point) && !empty($point['done']),
            ];
        })->filter(function ($point) {
            return $point['content'] !== '';
        })->values()->all();
    }

    // Menyamakan poin di database dengan isi form: ubah yang ada, tambah yang baru, hapus yang dibuang. Urutan ikut form.
    protected function syncPoints($itemId, $points, $isNew = false)
    {
        if ($points === null) {
            return;
        }

        $user = auth()->user()->username;
        $existing = DB::table('work_item_points')->where('work_item_id', $itemId)->whereNull('deleted_at')->get()->keyBy('id');
        $kept = [];
        $notes = [];

        foreach ($points as $index => $point) {
            $old = $point['id'] ? $existing->get($point['id']) : null;
            $values = ['content' => $point['content'], 'sort_order' => $index + 1, 'updated_by' => $user, 'updated_at' => now()];

            if (!$old || (bool) $old->is_done !== $point['done']) {
                $values['is_done'] = $point['done'];
                $values['done_at'] = $point['done'] ? now() : null;
                $values['done_by'] = $point['done'] ? $user : null;
            }

            if ($old) {
                $kept[] = $old->id;
                DB::table('work_item_points')->where('id', $old->id)->update($values);
                if (array_key_exists('is_done', $values)) {
                    $notes[] = ($point['done'] ? 'Poin selesai: ' : 'Poin dibuka lagi: ') . $point['content'];
                }
                continue;
            }

            DB::table('work_item_points')->insert(array_merge($values, ['work_item_id' => $itemId, 'created_by' => $user, 'created_at' => now()]));
            $notes[] = 'Poin ditambah: ' . $point['content'];
        }

        $removed = $existing->except($kept);
        if ($removed->isNotEmpty()) {
            DB::table('work_item_points')->whereIn('id', $removed->keys())->update(['deleted_at' => now(), 'updated_by' => $user]);
            DB::table('work_item_attachments')->whereIn('point_id', $removed->keys())->whereNull('deleted_at')->update(['deleted_at' => now()]);
            foreach ($removed as $point) {
                $notes[] = 'Poin dihapus: ' . $point->content;
            }
        }

        // Kerjaan baru cukup satu catatan jumlah poin, bukan satu baris per poin
        if ($isNew) {
            $notes = $points ? [count($points) . ' poin dicatat'] : [];
        }

        if ($notes) {
            $item = $this->findItem($itemId);
            $this->log($itemId, $item->progress, $item->progress, $item->status, implode("\n", $notes));
        }
    }

    // Mengembalikan id poin terakhir yang dimasukkan
    protected function insertPoints($itemId, array $points)
    {
        $order = (int) DB::table('work_item_points')->where('work_item_id', $itemId)->max('sort_order');
        $user = auth()->user()->username;
        $lastId = null;

        foreach ($points as $content) {
            $lastId = DB::table('work_item_points')->insertGetId([
                'work_item_id' => $itemId,
                'content' => $content,
                'is_done' => false,
                'sort_order' => ++$order,
                'created_by' => $user,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $lastId;
    }

    protected function findItem($id)
    {
        $item = DB::table('work_items')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$item, 404, 'Kerjaan tidak ditemukan');

        return $item;
    }

    protected function findPoint($id)
    {
        $point = DB::table('work_item_points')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$point, 404, 'Poin tidak ditemukan');

        return $point;
    }

    protected function activeProgrammers()
    {
        return DB::table('work_programmers')
            ->join('users', 'users.id', '=', 'work_programmers.user_id')
            ->where('work_programmers.is_active', true)
            ->orderBy('users.name')
            ->get(['work_programmers.id', 'work_programmers.user_id', 'work_programmers.division', 'work_programmers.color', 'users.name']);
    }

    protected function log($itemId, $before, $after, $status, $note)
    {
        return DB::table('work_item_logs')->insertGetId([
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
