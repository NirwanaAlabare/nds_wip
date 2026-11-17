<?php

namespace App\Http\Controllers\General;

use App\Exports\ActionPointExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

// Action Points: poin hasil meeting dan temuan lapangan beserta tindak lanjutnya. Riwayat tindak lanjut ada di action_point_logs.
class ActionPointController extends Controller
{
    const JENIS = ['meeting' => 'Hasil Meeting', 'temuan' => 'Temuan'];
    const STATUS = ['open' => 'Open', 'progress' => 'Progress', 'done' => 'Selesai', 'batal' => 'Batal'];
    const PRIORITAS = ['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'];
    const ACTIVE = ['open', 'progress'];
    const MAX_PHOTOS = 6;
    const PHOTO_KB = 8192;

    public function index()
    {
        return view('general.tools.action-points', ['containerFluid' => true]);
    }

    public function data(Request $request)
    {
        $filters = $this->validateFilters($request);
        $today = Carbon::today();
        $items = $this->query($filters)->get();

        $logs = DB::table('action_point_logs')
            ->whereIn('action_point_id', $items->pluck('id'))
            ->groupBy('action_point_id')
            ->selectRaw('action_point_id, COUNT(*) as total, MAX(created_at) as last_at')
            ->get()
            ->keyBy('action_point_id');

        $rows = $items->map(function ($item) use ($logs, $today) {
            return $this->present($item, $today, $logs->get($item->id));
        })->values();

        return response()->json([
            'today' => $today->toDateString(),
            'items' => $rows,
            'suggestions' => $this->suggestions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(self::JENIS))],
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:150',
            'peserta' => 'nullable|string|max:2000',
            'bagian' => 'nullable|string|max:50',
            'points' => 'required|array|min:1|max:50',
            'points.*.poin' => 'required|string|max:5000',
            'points.*.kategori' => 'nullable|string|max:50',
            'points.*.prioritas' => ['required', Rule::in(array_keys(self::PRIORITAS))],
            'points.*.pic' => 'nullable|string|max:100',
            'points.*.target_date' => 'nullable|date',
            'points.*.foto' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'points.*.foto.*' => 'image|max:' . self::PHOTO_KB,
        ], $this->messages());

        $user = auth()->user()->username;
        $stored = [];

        try {
            $ids = DB::transaction(function () use ($request, $validated, $user, &$stored) {
                $ids = [];

                foreach ($validated['points'] as $index => $point) {
                    $photos = $this->storePhotos($request->file("points.$index.foto", []));
                    $stored = array_merge($stored, $photos);

                    $id = DB::table('action_points')->insertGetId([
                        'jenis' => $validated['jenis'],
                        'tanggal' => $validated['tanggal'],
                        'judul' => trim($validated['judul']),
                        'peserta' => $validated['jenis'] === 'meeting' ? $this->clean($validated['peserta'] ?? null) : null,
                        'bagian' => $this->clean($validated['bagian'] ?? null),
                        'poin' => trim($point['poin']),
                        'kategori' => $this->clean($point['kategori'] ?? null),
                        'prioritas' => $point['prioritas'],
                        'foto' => $photos ? json_encode($photos) : null,
                        'pic' => $this->clean($point['pic'] ?? null),
                        'target_date' => $point['target_date'] ?? null,
                        'status' => 'open',
                        'created_by' => $user,
                        'updated_by' => $user,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->log($id, null, 'open', $validated['jenis'] === 'meeting' ? 'Poin meeting dicatat' : 'Temuan dicatat');
                    $ids[] = $id;
                }

                return $ids;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($stored);
            throw $e;
        }

        return response()->json(['status' => 200, 'message' => count($ids) . ' poin tersimpan', 'ids' => $ids]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'jenis' => ['required', Rule::in(array_keys(self::JENIS))],
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:150',
            'peserta' => 'nullable|string|max:2000',
            'bagian' => 'nullable|string|max:50',
            'poin' => 'required|string|max:5000',
            'kategori' => 'nullable|string|max:50',
            'prioritas' => ['required', Rule::in(array_keys(self::PRIORITAS))],
            'pic' => 'nullable|string|max:100',
            'target_date' => 'nullable|date',
            'foto_keep' => 'nullable|array',
            'foto_keep.*' => 'string',
            'foto' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'foto.*' => 'image|max:' . self::PHOTO_KB,
        ], $this->messages());

        $item = DB::table('action_points')->where('id', $validated['id'])->whereNull('deleted_at')->first();
        abort_if(!$item, 404, 'Poin tidak ditemukan');

        $current = $this->photos($item->foto);
        $keep = array_values(array_intersect($current, $validated['foto_keep'] ?? []));
        $newFiles = $request->file('foto', []);

        if (count($keep) + count($newFiles) > self::MAX_PHOTOS) {
            return response()->json(['message' => 'Maksimal ' . self::MAX_PHOTOS . ' foto per poin'], 422);
        }

        $added = $this->storePhotos($newFiles);
        $photos = array_merge($keep, $added);
        $values = [
            'jenis' => $validated['jenis'],
            'tanggal' => $validated['tanggal'],
            'judul' => trim($validated['judul']),
            'peserta' => $validated['jenis'] === 'meeting' ? $this->clean($validated['peserta'] ?? null) : null,
            'bagian' => $this->clean($validated['bagian'] ?? null),
            'poin' => trim($validated['poin']),
            'kategori' => $this->clean($validated['kategori'] ?? null),
            'prioritas' => $validated['prioritas'],
            'pic' => $this->clean($validated['pic'] ?? null),
            'target_date' => $validated['target_date'] ?? null,
            'foto' => $photos ? json_encode($photos) : null,
            'updated_by' => auth()->user()->username,
            'updated_at' => now(),
        ];

        $notes = [];
        if ((string) $item->pic !== (string) $values['pic']) {
            $notes[] = 'PIC: ' . ($item->pic ?: '-') . ' → ' . ($values['pic'] ?: '-');
        }
        if ((string) $item->target_date !== (string) $values['target_date']) {
            $notes[] = 'Target: ' . $this->dateText($item->target_date) . ' → ' . $this->dateText($values['target_date']);
        }
        if ($item->prioritas !== $values['prioritas']) {
            $notes[] = 'Prioritas: ' . self::PRIORITAS[$item->prioritas] . ' → ' . self::PRIORITAS[$values['prioritas']];
        }
        if ($added || count($keep) !== count($current)) {
            $notes[] = 'Foto diperbarui';
        }

        try {
            DB::transaction(function () use ($item, $values, $notes) {
                DB::table('action_points')->where('id', $item->id)->update($values);
                $this->log($item->id, $item->status, $item->status, $notes ? implode('. ', $notes) : 'Data poin diubah');
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($added);
            throw $e;
        }

        Storage::disk('public')->delete(array_values(array_diff($current, $keep)));

        return response()->json(['status' => 200, 'message' => 'Poin diperbarui']);
    }

    public function followUp(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'status' => ['required', Rule::in(array_keys(self::STATUS))],
            'catatan' => 'nullable|required_if:status,batal|string|max:3000',
            'foto' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'foto.*' => 'image|max:' . self::PHOTO_KB,
        ], array_merge($this->messages(), ['catatan.required_if' => 'Alasan batal wajib diisi']));

        $files = $request->file('foto', []);
        $note = $this->clean($validated['catatan'] ?? null);
        $photos = [];

        try {
            $result = DB::transaction(function () use ($validated, $files, $note, &$photos) {
                $item = DB::table('action_points')->where('id', $validated['id'])->whereNull('deleted_at')->lockForUpdate()->first();
                abort_if(!$item, 404, 'Poin tidak ditemukan');

                $status = $validated['status'];
                abort_if($status === $item->status && !$note && !$files, 422, 'Isi catatan, lampirkan foto, atau ubah statusnya');

                $photos = $this->storePhotos($files);
                $changes = [
                    'status' => $status,
                    'done_date' => $status === 'done' ? ($item->status === 'done' ? $item->done_date : Carbon::today()->toDateString()) : null,
                    'updated_by' => auth()->user()->username,
                    'updated_at' => now(),
                ];

                if ($note) {
                    $changes['tindak_lanjut'] = $note;
                }

                DB::table('action_points')->where('id', $item->id)->update($changes);
                $this->log($item->id, $item->status, $status, $note, $photos);

                return ['just_done' => $status === 'done' && $item->status !== 'done'];
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($photos);
            throw $e;
        }

        return response()->json(['status' => 200, 'message' => 'Tindak lanjut tersimpan', 'just_done' => $result['just_done']]);
    }

    public function logs($id)
    {
        $logs = DB::table('action_point_logs')
            ->leftJoin('users', 'users.username', '=', 'action_point_logs.created_by')
            ->where('action_point_id', $id)
            ->orderByDesc('action_point_logs.id')
            ->limit(200)
            ->get(['action_point_logs.*', 'users.name as created_name']);

        return response()->json($logs->map(function ($log) {
            $log->foto_urls = $this->urls($this->photos($log->foto));
            return $log;
        }));
    }

    public function delete(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer']);

        DB::table('action_points')->where('id', $validated['id'])->whereNull('deleted_at')->update([
            'deleted_at' => now(),
            'updated_by' => auth()->user()->username,
        ]);

        return response()->json(['status' => 200, 'message' => 'Poin dihapus']);
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);
        $items = $this->query($filters)->get();
        $logs = DB::table('action_point_logs')
            ->whereIn('action_point_id', $items->pluck('id'))
            ->orderBy('id')
            ->get()
            ->groupBy('action_point_id');
        $today = Carbon::today();

        $rows = $items->map(function ($item) use ($logs, $today) {
            $row = $this->present($item, $today, null);
            $row->history = collect($logs->get($item->id, []))->map(function ($log) {
                $status = $log->status_from !== $log->status_to && $log->status_to
                    ? (self::STATUS[$log->status_from] ?? 'Baru') . ' → ' . (self::STATUS[$log->status_to] ?? $log->status_to)
                    : null;
                $text = trim(implode(': ', array_filter([$status, $log->catatan])));

                return Carbon::parse($log->created_at)->format('d/m/Y H:i') . ' ' . ($text ?: '-') . ' (' . $log->created_by . ')';
            })->implode("\n");
            return $row;
        });

        $suffix = ($filters['jenis'] ?? null) ? '_' . $filters['jenis'] : '';

        return Excel::download(new ActionPointExport($rows), "action-points{$suffix}_{$filters['from']}_{$filters['to']}.xlsx");
    }

    protected function validateFilters(Request $request)
    {
        return $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'carry' => 'nullable|boolean',
            'jenis' => ['nullable', Rule::in(array_keys(self::JENIS))],
            'status' => ['nullable', Rule::in(array_merge(['aktif', 'overdue'], array_keys(self::STATUS)))],
            'pic' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:100',
        ], ['to.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal dari']);
    }

    // Poin dalam rentang tanggal; poin yang belum selesai dari sebelum rentang ikut tampil kalau "carry" aktif supaya tindak lanjut tidak terlupa
    protected function query(array $filters)
    {
        $today = Carbon::today()->toDateString();
        $query = DB::table('action_points')->whereNull('deleted_at')
            ->where(function ($q) use ($filters) {
                $q->whereBetween('tanggal', [$filters['from'], $filters['to']]);
                if (!empty($filters['carry'])) {
                    $q->orWhere(function ($q) use ($filters) {
                        $q->whereIn('status', self::ACTIVE)->where('tanggal', '<', $filters['from']);
                    });
                }
            });

        if (!empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        $status = $filters['status'] ?? null;
        if ($status === 'aktif') {
            $query->whereIn('status', self::ACTIVE);
        } elseif ($status === 'overdue') {
            $query->whereIn('status', self::ACTIVE)->whereNotNull('target_date')->where('target_date', '<', $today);
        } elseif ($status) {
            $query->where('status', $status);
        }

        if (!empty($filters['pic'])) {
            $query->where('pic', $filters['pic']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . addcslashes($filters['search'], '%_\\') . '%';
            $query->where(function ($q) use ($term) {
                foreach (['poin', 'judul', 'peserta', 'pic', 'kategori', 'bagian', 'tindak_lanjut'] as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        return $query->orderByDesc('tanggal')->orderBy('judul')->orderBy('id');
    }

    protected function present($item, Carbon $today, $log)
    {
        $active = in_array($item->status, self::ACTIVE);
        $daysLeft = $item->target_date ? $today->diffInDays(Carbon::parse($item->target_date), false) : null;

        $item->foto_list = $this->photos($item->foto);
        $item->foto_urls = $this->urls($item->foto_list);
        $item->days_left = $daysLeft;
        $item->overdue = $active && $daysLeft !== null && $daysLeft < 0;
        $item->log_count = $log ? (int) $log->total : 0;
        $item->last_log_at = $log ? $log->last_at : null;
        unset($item->foto);

        return $item;
    }

    protected function suggestions()
    {
        $distinct = function ($column) {
            return DB::table('action_points')->whereNull('deleted_at')->whereNotNull($column)->where($column, '<>', '')
                ->groupBy($column)->orderByRaw('MAX(id) DESC')->limit(200)->pluck($column);
        };

        return [
            'pic' => $distinct('pic'),
            'kategori' => $distinct('kategori'),
            'bagian' => $distinct('bagian'),
            'judul' => $distinct('judul'),
        ];
    }

    protected function storePhotos($files)
    {
        $paths = [];
        $folder = 'action_points/' . date('Y/m');

        foreach (array_filter((array) $files) as $file) {
            $name = date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($file->extension() ?: 'jpg');
            $file->storeAs('public/' . $folder, $name);
            $paths[] = $folder . '/' . $name;
        }

        return $paths;
    }

    protected function photos($json)
    {
        $list = $json ? json_decode($json, true) : [];

        return is_array($list) ? array_values(array_filter($list, 'is_string')) : [];
    }

    protected function urls(array $paths)
    {
        return array_map(function ($path) {
            return url('storage/' . $path);
        }, $paths);
    }

    protected function log($pointId, $from, $to, $note, array $photos = [])
    {
        DB::table('action_point_logs')->insert([
            'action_point_id' => $pointId,
            'status_from' => $from,
            'status_to' => $to,
            'catatan' => $note,
            'foto' => $photos ? json_encode($photos) : null,
            'created_by' => auth()->user()->username,
            'created_at' => now(),
        ]);
    }

    protected function clean($value)
    {
        $value = $value === null ? null : trim(preg_replace('/[ \t]+/', ' ', $value));

        return $value === '' ? null : $value;
    }

    protected function dateText($date)
    {
        return $date ? Carbon::parse($date)->format('d/m/Y') : '-';
    }

    protected function messages()
    {
        return [
            'judul.required' => 'Judul meeting / lokasi temuan wajib diisi',
            'points.required' => 'Isi minimal satu poin',
            'points.*.poin.required' => 'Isi poin tidak boleh kosong',
            'poin.required' => 'Isi poin tidak boleh kosong',
            'points.*.foto.max' => 'Maksimal ' . self::MAX_PHOTOS . ' foto per poin',
            'foto.max' => 'Maksimal ' . self::MAX_PHOTOS . ' foto',
            'points.*.foto.*.image' => 'File foto harus berupa gambar',
            'foto.*.image' => 'File foto harus berupa gambar',
            'points.*.foto.*.max' => 'Ukuran foto maksimal 8 MB',
            'foto.*.max' => 'Ukuran foto maksimal 8 MB',
        ];
    }
}
