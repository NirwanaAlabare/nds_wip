<?php

namespace App\Services;

use Carbon\Carbon;

// Dashboard Work Tracker: proyeksi selesai dihitung dari riwayat progress di work_item_logs. Hanya membaca data.
class WorkTrackerDashboardService
{
    const PACE_WINDOW_DAYS = 14;
    const MIN_ACTIVE_DAYS = 3; // kurang dari ini, pace riwayat belum bisa dipercaya
    const HORIZON_DAYS = 365;
    const WEEKS = 8;
    const RISK_LIMIT = 8;
    const PERIODS = ['month', '30', '90'];

    protected $tracker;

    public function __construct(WorkTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    public function build($items, $logs, $programmers, $divisionsById, Carbon $today, array $filters)
    {
        $today = $today->copy()->startOfDay();
        $division = $filters['division'] ?? null;
        $from = $this->periodStart($filters['period'] ?? 'month', $today);
        $allProgrammers = collect($programmers);

        $scopedItems = collect($items)->filter(function ($item) use ($division, $divisionsById) {
            return !$division || ($item->programmer_id && ($divisionsById[$item->programmer_id] ?? null) === $division);
        })->values();
        $scopedProgrammers = $allProgrammers->filter(function ($programmer) use ($division) {
            return !$division || $programmer->division === $division;
        })->values();

        $board = $this->tracker->board($scopedItems, $scopedProgrammers, $today);
        $rows = collect($board['items'])->map(function ($row) use ($divisionsById) {
            $row['weight'] = $row['effort'] ?: 1;
            $row['begin'] = $this->beginDate($row);
            $row['division'] = $row['programmer_id'] ? ($divisionsById[$row['programmer_id']] ?? null) : null;
            return $row;
        });
        $counted = $rows->where('status', '!=', 'ditolak');
        $active = $rows->whereIn('status', WorkTrackerService::ACTIVE);
        $logsByItem = collect($logs)->filter(function ($log) {
            return $log->progress_after !== null;
        })->sortBy(function ($log) {
            return sprintf('%s|%010d', $log->created_at, $log->id ?? 0);
        })->groupBy('work_item_id');

        $done = $rows->where('status', 'selesai')->filter(function ($row) use ($from, $today) {
            return $row['finished_at'] && $row['finished_at'] >= $from->toDateString() && $row['finished_at'] <= $today->toDateString();
        });
        $doneWithTarget = $done->filter(function ($row) {
            return $row['target_date'];
        });
        $onTime = $doneWithTarget->filter(function ($row) {
            return $row['finished_at'] <= $row['target_date'];
        })->count();

        $programmerRows = collect($board['programmers']);

        return [
            'today' => $today->toDateString(),
            'from' => $from->toDateString(),
            'has_items' => $rows->isNotEmpty(),
            'divisions' => $this->divisions($allProgrammers),
            'kpi' => [
                'active' => $active->count(),
                'in_progress' => $active->where('status', 'dikerjakan')->count(),
                'done' => $done->count(),
                'on_time_pct' => $doneWithTarget->count() ? (int) round($onTime / $doneWithTarget->count() * 100) : null,
                'on_time_base' => $doneWithTarget->count(),
                'late' => $active->where('health', 'telat')->count(),
                'urgent' => $active->where('urgent', true)->count(),
            ],
            'projection' => $this->projection($counted, $active, $logsByItem, $today),
            'history' => $this->history($counted, $logsByItem, $from, $today),
            'members' => $this->members($programmerRows),
            'risks' => $this->risks($active, $programmerRows, $today),
            'weekly' => $this->weekly($counted, $today),
            'composition' => $this->composition($active),
        ];
    }

    protected function periodStart($period, Carbon $today)
    {
        if ($period === 'month') {
            return $today->copy()->startOfMonth();
        }

        return $today->copy()->subDays(((int) $period) - 1);
    }

    protected function beginDate($row)
    {
        $created = substr($row['created_at'], 0, 10);

        return $row['start_date'] && $row['start_date'] < $created ? $row['start_date'] : $created;
    }

    protected function divisions($programmers)
    {
        $used = $programmers->pluck('division')->unique();

        return collect(WorkTrackerService::DEFAULT_DIVISIONS)->filter(function ($division) use ($used) {
            return $used->contains($division);
        })->merge($used)->unique()->values();
    }

    protected function projection($counted, $active, $logsByItem, Carbon $today)
    {
        $remaining = round($active->sum(function ($row) {
            return $row['weight'] * (100 - (int) $row['progress']) / 100;
        }), 1);

        $observed = $this->observedPace($counted, $logsByItem, $today);
        $people = $active->pluck('programmer_id')->filter()->unique()->count();
        $usesHistory = $observed['active_days'] >= self::MIN_ACTIVE_DAYS && $observed['pace'] > 0;
        // Tanpa riwayat yang cukup, tiap orang dianggap menyelesaikan satu hari beban per hari
        $pace = $usesHistory ? $observed['pace'] : (float) max(1, $people);

        $latestTarget = $active->pluck('target_date')->filter()->max();
        $available = $latestTarget ? max(0, $today->diffInDays(Carbon::parse($latestTarget), false) + 1) : null;

        $daysToFinish = $remaining > 0 ? (int) ceil($remaining / $pace) : 0;
        $overHorizon = $daysToFinish > self::HORIZON_DAYS;
        $predicted = $remaining > 0 && !$overHorizon ? $today->copy()->addDays($daysToFinish)->toDateString() : null;
        $slack = $predicted && $latestTarget ? Carbon::parse($predicted)->diffInDays(Carbon::parse($latestTarget), false) : null;

        return [
            'remaining' => $remaining,
            'pace' => round($pace, 2),
            'basis' => $usesHistory ? 'riwayat' : 'estimasi',
            'active_days' => $observed['active_days'],
            'people' => $people,
            'unestimated' => $active->filter(function ($row) {
                return !$row['effort'];
            })->count(),
            'latest_target' => $latestTarget,
            'available_days' => $available,
            'needed_pace' => $available ? round($remaining / $available, 2) : null,
            'days_to_finish' => $daysToFinish,
            'predicted_date' => $predicted,
            'over_horizon' => $overHorizon,
            'slack_days' => $slack,
            'tight_slack_days' => WorkTrackerService::TIGHT_SLACK_DAYS,
            'horizon_days' => self::HORIZON_DAYS,
            'window_days' => self::PACE_WINDOW_DAYS,
        ];
    }

    // Beban yang diselesaikan per hari dalam jendela terakhir, dihitung sejak hari pertama ada progress
    protected function observedPace($counted, $logsByItem, Carbon $today)
    {
        $start = $today->copy()->subDays(self::PACE_WINDOW_DAYS - 1)->toDateString();
        $todayString = $today->toDateString();
        $total = 0;
        $days = [];

        foreach ($counted as $row) {
            foreach ($logsByItem->get($row['id'], []) as $log) {
                $date = substr($log->created_at, 0, 10);
                if ($log->progress_before === null || $date < $start || $date > $todayString) {
                    continue;
                }

                $delta = ($log->progress_after - $log->progress_before) / 100 * $row['weight'];
                $total += $delta;
                if ($delta > 0) {
                    $days[$date] = true;
                }
            }
        }

        if (!$days) {
            return ['pace' => 0, 'active_days' => 0];
        }

        ksort($days);
        $first = Carbon::parse(array_key_first($days));

        return [
            'pace' => $total / ($first->diffInDays($today) + 1),
            'active_days' => count($days),
        ];
    }

    protected function history($counted, $logsByItem, Carbon $from, Carbon $today)
    {
        $series = [];

        for ($date = $from->copy(); $date->lte($today); $date->addDay()) {
            $dateString = $date->toDateString();
            $remaining = 0;

            foreach ($counted as $row) {
                if ($row['begin'] <= $dateString) {
                    $remaining += $row['weight'] * (100 - $this->progressAt($row, $logsByItem->get($row['id'], []), $dateString, $today)) / 100;
                }
            }

            $series[] = ['date' => $dateString, 'remaining' => round($remaining, 1)];
        }

        return $series;
    }

    protected function progressAt($row, $logs, $date, Carbon $today)
    {
        if ($date === $today->toDateString()) {
            return (int) $row['progress'];
        }

        if ($row['status'] === 'selesai' && $row['finished_at'] && $date >= $row['finished_at']) {
            return 100;
        }

        $progress = 0;

        foreach ($logs as $log) {
            if (substr($log->created_at, 0, 10) <= $date) {
                $progress = (int) $log->progress_after;
            }
        }

        return $progress;
    }

    protected function members($programmerRows)
    {
        return $programmerRows->map(function ($p) {
            return collect($p)->only(['id', 'name', 'division', 'color', 'active', 'late', 'tight', 'progress', 'effort_left', 'available_days', 'gap_days', 'tier', 'incident'])->all();
        })->sortBy(function ($p) {
            return sprintf('%s|%s', mb_strtolower($p['division']), mb_strtolower($p['name']));
        })->values();
    }

    protected function risks($active, $programmerRows, Carbon $today)
    {
        $names = $programmerRows->pluck('name', 'id');

        return $active->filter(function ($row) {
            return $row['urgent'] || in_array($row['health'], ['telat', 'mepet']);
        })->sortBy(function ($row) {
            return sprintf('%d|%012d|%010d', $row['urgent'] ? 0 : 1, 500000 + ($row['slack_days'] ?? 400000), $row['id']);
        })->take(self::RISK_LIMIT)->map(function ($row) use ($names, $today) {
            $daysLeft = $row['target_date'] ? $today->diffInDays(Carbon::parse($row['target_date']), false) + 1 : null;

            return [
                'id' => $row['id'],
                'code' => $row['code'],
                'title' => $row['title'],
                'type' => $row['type'],
                'level' => $row['level'],
                'urgent' => $row['urgent'],
                'programmer' => $row['programmer_id'] ? ($names[$row['programmer_id']] ?? null) : null,
                'progress' => (int) $row['progress'],
                'pace' => $row['pace'],
                'needed_pace' => $daysLeft && $daysLeft > 0 ? round((100 - (int) $row['progress']) / $daysLeft, 1) : null,
                'target_date' => $row['target_date'],
                'predicted_date' => $row['predicted_date'],
                'health' => $row['health'],
                'message' => $row['message'],
            ];
        })->values();
    }

    protected function weekly($counted, Carbon $today)
    {
        $monday = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weeks = [];

        for ($i = self::WEEKS - 1; $i >= 0; $i--) {
            $start = $monday->copy()->subWeeks($i)->toDateString();
            $end = $monday->copy()->subWeeks($i)->addDays(6)->toDateString();

            $weeks[] = [
                'start' => $start,
                'in' => $counted->filter(function ($row) use ($start, $end) {
                    $created = substr($row['created_at'], 0, 10);
                    return $created >= $start && $created <= $end;
                })->count(),
                'done' => $counted->filter(function ($row) use ($start, $end) {
                    return $row['status'] === 'selesai' && $row['finished_at'] >= $start && $row['finished_at'] <= $end;
                })->count(),
            ];
        }

        return $weeks;
    }

    protected function composition($active)
    {
        $count = function ($key, $labels, $emptyLabel) use ($active) {
            return $active->groupBy(function ($row) use ($key, $labels, $emptyLabel) {
                $value = $row[$key];
                return $value ? ($labels[$value] ?? $value) : $emptyLabel;
            })->map(function ($rows, $label) {
                return ['label' => $label, 'count' => $rows->count()];
            })->sortByDesc('count')->values();
        };

        return [
            'type' => $count('type', WorkTrackerService::TYPE_LABEL, '-'),
            'level' => $count('level', WorkTrackerService::LEVEL_LABEL, 'Belum ada level'),
            'division' => $count('division', [], 'Belum ditugaskan'),
        ];
    }

    // Data contoh: riwayat progress dibuat merata supaya grafik bisa dilihat tanpa menyentuh database
    public function demo(Carbon $today, array $filters)
    {
        [$items, $programmers] = $this->tracker->demoData($today);
        $divisions = collect($programmers)->pluck('division', 'id');

        return $this->build($items, $this->tracker->demoLogs($items, $today), $programmers, $divisions, $today, $filters);
    }
}
