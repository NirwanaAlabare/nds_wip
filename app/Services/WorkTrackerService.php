<?php

namespace App\Services;

use Carbon\Carbon;

// Work Tracker: menghitung pace, prediksi selesai, dan beban per programmer. Semua hari kalender dihitung hari kerja.
class WorkTrackerService
{
    const ACTIVE = ['baru', 'dikerjakan'];
    const PREFIX = ['plan' => 'PLN', 'bug' => 'BUG', 'request' => 'REQ'];
    const TYPE_LABEL = ['plan' => 'Plan', 'bug' => 'Bug', 'request' => 'Request'];
    const LEVEL_LABEL = ['major' => 'Major', 'minor' => 'Minor', 'tidak' => 'Tidak dikerjakan'];
    const STATUS_LABEL = ['baru' => 'Baru', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
    const HEALTH_LABEL = ['aman' => 'Aman', 'mepet' => 'Mepet', 'telat' => 'Telat', 'selesai' => 'Selesai', 'selesai_telat' => 'Selesai telat', 'ditolak' => 'Ditolak', 'belum_dinilai' => 'Belum dinilai'];
    const TIGHT_SLACK_DAYS = 1;
    const BEHIND_POINTS = 10; // selisih persen dari posisi seharusnya yang membuat pelari tidak lagi dianggap ngebut

    public function code($item)
    {
        return (self::PREFIX[$item->type] ?? 'WRK') . '-' . str_pad($item->id, 4, '0', STR_PAD_LEFT);
    }

    // Beban kerja item dalam hari: estimasi, atau durasi mulai-target bila estimasi kosong
    public function effort($item)
    {
        if ($item->estimate_days !== null) {
            return (float) $item->estimate_days;
        }

        if ($item->start_date && $item->target_date) {
            return (float) max(1, Carbon::parse($item->start_date)->diffInDays(Carbon::parse($item->target_date), false) + 1);
        }

        return null;
    }

    // Masalah mendesak: Bug Major yang belum selesai dan targetnya hari ini atau sudah lewat
    public function isUrgent($item, Carbon $today)
    {
        return $item->type === 'bug'
            && $item->level === 'major'
            && in_array($item->status, self::ACTIVE)
            && $item->target_date
            && Carbon::parse($item->target_date)->startOfDay()->lte($today);
    }

    public function analyzeItem($item, Carbon $today)
    {
        $today = $today->copy()->startOfDay();
        $progress = (int) $item->progress;
        $target = $item->target_date ? Carbon::parse($item->target_date)->startOfDay() : null;
        $effort = $this->effort($item);

        $result = [
            'code' => $this->code($item),
            'urgent' => $this->isUrgent($item, $today),
            'effort' => $effort,
            'effort_left' => null,
            'pace' => null,
            'basis' => null,
            'predicted_date' => null,
            'slack_days' => null,
            'health' => 'belum_dinilai',
            'message' => 'Isi estimasi atau target supaya bisa diprediksi',
        ];

        if ($item->status === 'ditolak') {
            $result['health'] = 'ditolak';
            $result['message'] = 'Tidak dikerjakan';
            return $result;
        }

        if ($item->status === 'selesai') {
            $finished = $item->finished_at ? Carbon::parse($item->finished_at)->startOfDay() : $today;
            $late = $target ? $target->diffInDays($finished, false) : 0;
            $result['effort_left'] = 0;
            $result['predicted_date'] = $finished->toDateString();
            $result['health'] = $late > 0 ? 'selesai_telat' : 'selesai';
            $result['message'] = $late > 0 ? "Selesai, telat {$late} hari" : 'Selesai tepat waktu';
            return $result;
        }

        $predicted = null;

        if ($progress > 0) {
            // Hari ini ikut dihitung sebagai hari yang sudah terpakai, sisa kerja dimulai besok
            $start = $item->start_date ? Carbon::parse($item->start_date)->startOfDay() : $today;
            $elapsed = max(1, $start->diffInDays($today, false) + 1);
            $pace = $progress / $elapsed;
            $remaining = (int) ceil((100 - $progress) / $pace);
            $predicted = $today->copy()->addDays($remaining);

            $result['pace'] = round($pace, 1);
            $result['basis'] = 'pace';
            $result['effort_left'] = $effort !== null ? round($effort * (100 - $progress) / 100, 1) : (float) $remaining;
        } elseif ($effort !== null) {
            $begin = $item->start_date ? Carbon::parse($item->start_date)->startOfDay() : $today->copy();
            if ($begin->lt($today)) {
                $begin = $today->copy();
            }
            $predicted = $begin->copy()->addDays(max(1, (int) ceil($effort)) - 1);

            $result['basis'] = 'estimasi';
            $result['effort_left'] = round($effort, 1);
        }

        if ($predicted) {
            $result['predicted_date'] = $predicted->toDateString();
        }

        if (!$target) {
            $result['health'] = 'belum_dinilai';
            $result['message'] = $predicted ? 'Belum ada target tanggal' : $result['message'];
            return $result;
        }

        if (!$predicted) {
            $result['health'] = $target->lt($today) ? 'telat' : 'belum_dinilai';
            $result['message'] = $target->lt($today) ? 'Target sudah lewat, belum ada progress' : $result['message'];
            return $result;
        }

        $slack = $predicted->diffInDays($target, false);
        $result['slack_days'] = $slack;

        if ($slack < 0) {
            $result['health'] = 'telat';
            $result['message'] = 'Dengan pace ini telat ' . abs($slack) . ' hari';
        } elseif ($slack <= self::TIGHT_SLACK_DAYS) {
            $result['health'] = 'mepet';
            $result['message'] = $slack === 0 ? 'Pas di target, tidak ada sisa waktu' : 'Sisa waktu 1 hari';
        } else {
            $result['health'] = 'aman';
            $result['message'] = "Aman, selesai {$slack} hari sebelum target";
        }

        return $result;
    }

    // Menyusun seluruh data halaman: item teranalisis, ringkasan per programmer, dan ringkasan tim
    public function board($items, $programmers, Carbon $today)
    {
        $today = $today->copy()->startOfDay();

        $analyzed = collect($items)->map(function ($item) use ($today) {
            return array_merge((array) $item, $this->analyzeItem($item, $today));
        })->values();

        $programmerRows = collect($programmers)->map(function ($programmer) use ($analyzed, $today) {
            $own = $analyzed->where('programmer_id', $programmer->id);
            return $this->summarizeProgrammer($programmer, $own, $today);
        })->values();

        return [
            'today' => $today->toDateString(),
            'items' => $analyzed,
            'programmers' => $programmerRows,
            'summary' => $this->summarizeTeam($analyzed, $programmerRows),
        ];
    }

    protected function summarizeProgrammer($programmer, $own, Carbon $today)
    {
        $active = $own->whereIn('status', self::ACTIVE);
        $doneThisMonth = $own->where('status', 'selesai')->filter(function ($item) use ($today) {
            return $item['finished_at'] && Carbon::parse($item['finished_at'])->isSameMonth($today);
        });

        $counted = $active->merge($doneThisMonth);
        $weight = 0;
        $weighted = 0;
        $expected = 0;

        foreach ($counted as $item) {
            $w = $item['effort'] ?: 1;
            $weight += $w;
            $weighted += $w * (int) $item['progress'];
            $expected += $w * $this->expectedProgress($item, $today);
        }

        $effortLeft = round($active->sum('effort_left'), 1);
        $latestTarget = $active->pluck('target_date')->filter()->max();
        $available = $latestTarget ? max(0, $today->diffInDays(Carbon::parse($latestTarget), false) + 1) : null;
        $gap = $available !== null ? round($available - $effortLeft, 1) : null;
        $late = $active->where('health', 'telat')->count();
        $tight = $active->where('health', 'mepet')->count();
        $progress = $weight > 0 ? (int) round($weighted / $weight) : 0;
        $expectedProgress = $weight > 0 ? (int) round($expected / $weight) : 0;

        return [
            'id' => $programmer->id,
            'user_id' => $programmer->user_id,
            'name' => $programmer->name,
            'color' => $programmer->color,
            'active' => $active->count(),
            'in_progress' => $active->where('status', 'dikerjakan')->count(),
            'late' => $late,
            'tight' => $tight,
            'done_month' => $doneThisMonth->count(),
            'progress' => $progress,
            'expected_progress' => $expectedProgress,
            'effort_left' => $effortLeft,
            'available_days' => $available,
            'gap_days' => $gap,
            'tier' => $this->tier($active->count(), $doneThisMonth->count(), $late, $tight, $gap, $available, $expectedProgress - $progress),
            'checkpoints' => $this->checkpoints($counted, $weight),
            'urgent' => $this->urgentRows($active),
            'incident' => $this->incident($active),
        ];
    }

    protected function urgentRows($items)
    {
        return $items->where('urgent', true)->map(function ($item) {
            return ['id' => $item['id'], 'code' => $item['code'], 'title' => $item['title'], 'status' => $item['status'], 'programmer_id' => $item['programmer_id']];
        })->values();
    }

    // panic: masih ada masalah yang belum disentuh, fixing: semua masalah sedang dikerjakan
    protected function incident($active)
    {
        $urgent = $active->where('urgent', true);

        if ($urgent->isEmpty()) {
            return null;
        }

        return $urgent->where('status', 'baru')->isNotEmpty() ? 'panic' : 'fixing';
    }

    // Pos di lintasan: yang selesai di depan, lalu target terdekat. Jarak antarpos mengikuti beban kerja.
    protected function checkpoints($items, $totalWeight)
    {
        $sorted = $items->sortBy(function ($item) {
            $done = $item['status'] === 'selesai';
            return sprintf('%d|%s|%010d', $done ? 0 : 1, ($done ? $item['finished_at'] : $item['target_date']) ?: '9999-12-31', $item['id']);
        });

        $cumulative = 0;

        return $sorted->map(function ($item) use (&$cumulative, $totalWeight) {
            $start = $cumulative;
            $cumulative += $item['effort'] ?: 1;

            return [
                'id' => $item['id'],
                'code' => $item['code'],
                'title' => $item['title'],
                'status' => $item['status'],
                'health' => $item['health'],
                'progress' => (int) $item['progress'],
                'target_date' => $item['target_date'],
                'start_position' => round($start / $totalWeight * 100, 2),
                'position' => round($cumulative / $totalWeight * 100, 2),
            ];
        })->values();
    }

    // Posisi yang seharusnya sudah dicapai hari ini bila kerja merata dari mulai sampai target
    protected function expectedProgress($item, Carbon $today)
    {
        if ($item['status'] === 'selesai') {
            return 100;
        }

        if (!$item['start_date'] || !$item['target_date']) {
            return (int) $item['progress'];
        }

        $start = Carbon::parse($item['start_date'])->startOfDay();
        $target = Carbon::parse($item['target_date'])->startOfDay();
        $duration = max(1, $start->diffInDays($target, false) + 1);
        $elapsed = $start->diffInDays($today, false) + 1;

        return min(100, max(0, $elapsed / $duration * 100));
    }

    // Menentukan gaya animasi pelari: sprint, jog, walk, rest, atau finish
    protected function tier($active, $done, $late, $tight, $gap, $available, $behind)
    {
        if ($active === 0) {
            return $done > 0 ? 'finish' : 'rest';
        }

        if (($gap !== null && $gap < 0) || $late * 2 >= $active && $late > 0) {
            return 'walk';
        }

        $roomy = $gap !== null && $available > 0 && $gap / $available >= 0.2;

        if ($late > 0 || $tight > 0 || !$roomy || $behind >= self::BEHIND_POINTS) {
            return 'jog';
        }

        return 'sprint';
    }

    protected function summarizeTeam($items, $programmers)
    {
        $active = $items->whereIn('status', self::ACTIVE);
        $shortfall = round($programmers->sum(function ($p) {
            return $p['gap_days'] !== null && $p['gap_days'] < 0 ? abs($p['gap_days']) : 0;
        }), 1);

        return [
            'active' => $active->count(),
            'in_progress' => $active->where('status', 'dikerjakan')->count(),
            'late' => $active->where('health', 'telat')->count(),
            'tight' => $active->where('health', 'mepet')->count(),
            'bugs_open' => $active->where('type', 'bug')->count(),
            'requests_new' => $items->where('type', 'request')->where('status', 'baru')->count(),
            'unassigned' => $active->whereNull('programmer_id')->count(),
            'unestimated' => $active->whereNull('effort_left')->count(),
            'effort_left' => round($active->sum('effort_left'), 1),
            'shortfall_days' => $shortfall,
            'latest_predicted' => $active->pluck('predicted_date')->filter()->max(),
            'urgent' => $this->urgentRows($active),
        ];
    }

    // Data contoh untuk melihat tampilan tanpa menulis ke database
    protected function demoData(Carbon $today)
    {
        $d = function ($offset) use ($today) {
            return $today->copy()->addDays($offset)->toDateString();
        };

        $programmers = [
            (object) ['id' => 1, 'user_id' => 0, 'name' => 'Programmer A', 'color' => '#2f80ed'],
            (object) ['id' => 2, 'user_id' => 0, 'name' => 'Programmer B', 'color' => '#9b51e0'],
            (object) ['id' => 3, 'user_id' => 0, 'name' => 'Programmer C', 'color' => '#f2994a'],
            (object) ['id' => 4, 'user_id' => 0, 'name' => 'Programmer D', 'color' => '#27ae60'],
        ];

        // id, tipe, judul, modul, peminta, dept, level, status, programmer, mulai, target, estimasi, progress, selesai
        $rows = [
            [1, 'plan', 'Refactor laporan export order', 'Laporan', null, null, null, 'dikerjakan', 1, -4, 8, 8, 60, null],
            [2, 'request', 'Tambah kolom buyer di export', 'Marketing', 'Contoh Peminta', 'Marketing', 'minor', 'dikerjakan', 1, -1, 5, 2, 50, null],
            [3, 'plan', 'Dashboard WIP tahap 2', 'Sewing', null, null, null, 'selesai', 1, -9, -2, 6, 100, -3],
            [4, 'plan', 'Integrasi penerimaan cutting', 'Cutting', null, null, null, 'dikerjakan', 2, -5, 2, 10, 25, null],
            [5, 'bug', 'Error simpan scan stocker', 'Stocker', 'Contoh Operator', 'Produksi', 'major', 'dikerjakan', 2, -1, 0, 2, 20, null],
            [6, 'request', 'Filter tanggal laporan packing', 'Packing', 'Contoh Peminta', 'PPIC', 'minor', 'baru', 2, 1, 3, 2, 0, null],
            [7, 'plan', 'Halaman master mesin', 'Asset', null, null, null, 'dikerjakan', 3, -2, 3, 4, 50, null],
            [8, 'bug', 'Total qty beda di rekap', 'Finish Good', 'Contoh Peminta', 'FG', 'major', 'baru', 3, 0, 0, 1, 0, null],
            [9, 'plan', 'Perbaikan alur lock transaksi', 'General', null, null, null, 'selesai', 4, -7, -1, 5, 100, -2],
            [10, 'request', 'Ganti warna tombol login', 'General', 'Contoh Peminta', 'HR', 'tidak', 'ditolak', null, null, null, null, 0, null],
            [11, 'request', 'Export BPB ke PDF', 'Warehouse', 'Contoh Peminta', 'Gudang', 'minor', 'baru', null, null, 7, 3, 0, null],
            [12, 'bug', 'Gagal cetak label karton', 'Packing', 'Contoh Operator', 'Packing', 'major', 'baru', null, null, 0, 1, 0, null],
        ];

        $items = array_map(function ($r) use ($d, $today) {
            return (object) [
                'id' => $r[0], 'type' => $r[1], 'title' => $r[2], 'description' => null, 'module' => $r[3],
                'requester' => $r[4], 'requester_dept' => $r[5], 'level' => $r[6], 'status' => $r[7],
                'programmer_id' => $r[8],
                'start_date' => $r[9] !== null ? $d($r[9]) : null,
                'target_date' => $r[10] !== null ? $d($r[10]) : null,
                'estimate_days' => $r[11], 'progress' => $r[12],
                'finished_at' => $r[13] !== null ? $d($r[13]) : null,
                'reject_reason' => $r[7] === 'ditolak' ? 'Contoh alasan ditolak' : null,
                'created_at' => $today->toDateTimeString(),
                'updated_at' => $today->toDateTimeString(),
            ];
        }, $rows);

        return [$items, $programmers];
    }

    public function demo(Carbon $today)
    {
        [$items, $programmers] = $this->demoData($today);

        return $this->board($items, $programmers, $today);
    }

    public function demoReport(Carbon $today, array $filters)
    {
        [$items, $programmers] = $this->demoData($today);
        $names = collect($programmers)->pluck('name', 'id');

        return $this->report($items, $names, $today, $filters);
    }

    // Daftar kerjaan untuk tab Laporan dan export Excel: kerjaan yang berjalan pada periode dan cocok dengan filter
    public function report($items, $names, Carbon $today, array $filters)
    {
        $today = $today->copy()->startOfDay();
        $from = $filters['from'];
        $to = $filters['to'];
        $search = mb_strtolower(trim($filters['search'] ?? ''));

        return collect($items)->filter(function ($item) use ($from, $to) {
            $begin = $item->start_date ?: substr($item->created_at, 0, 10);
            $end = $item->status === 'selesai' ? $item->finished_at : ($item->status === 'ditolak' ? substr($item->updated_at, 0, 10) : null);

            return $begin <= $to && (!$end || $end >= $from);
        })->map(function ($item) use ($today, $names) {
            return array_merge((array) $item, $this->analyzeItem($item, $today), [
                'programmer_name' => $item->programmer_id ? ($names[$item->programmer_id] ?? null) : null,
            ]);
        })->filter(function ($row) use ($filters, $search) {
            $programmer = $filters['programmer'] ?? null;
            $status = $filters['status'] ?? null;
            $level = $filters['level'] ?? null;

            if ($programmer === 'none' ? $row['programmer_id'] !== null : ($programmer && (int) $row['programmer_id'] !== (int) $programmer)) return false;
            if (!empty($filters['type']) && $row['type'] !== $filters['type']) return false;
            if ($status === 'open' ? !in_array($row['status'], self::ACTIVE) : ($status && $row['status'] !== $status)) return false;
            if ($level === 'none' ? $row['level'] !== null : ($level && $row['level'] !== $level)) return false;
            if (!empty($filters['health']) && $row['health'] !== $filters['health']) return false;

            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', [$row['code'], $row['title'], $row['module'], $row['requester'], $row['requester_dept'], $row['programmer_name']]));
                if (mb_strpos($haystack, $search) === false) return false;
            }

            return true;
        })->sortBy(function ($row) {
            return sprintf('%s|%s|%010d', mb_strtolower($row['programmer_name'] ?: "\u{FFFF}"), $row['start_date'] ?: '9999-12-31', $row['id']);
        })->values();
    }
}
