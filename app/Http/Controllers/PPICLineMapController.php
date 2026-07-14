<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;


class PPICLineMapController extends Controller
{
    public function ppic_line_map(Request $request)
    {
        $line = DB::connection('mysql_sb')->select("
select * from userpassword where username like '%line%' order by username asc");

        $lineMap = DB::table('ppic_line_map')
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->latest('tgl_start')
            ->get();

        $lineNameByUsername = collect($line)->pluck('FullName', 'username');

        $productGroupRows = DB::connection('mysql_sb')->select("
select line, product_group, sum(tot_qty) tot_qty from hist_product_per_line
where line is not null and product_group is not null
group by line, product_group order by line, sum(tot_qty) desc");

        $productGroupByLine = collect($productGroupRows)
            ->groupBy('line')
            ->map(fn($rows) => $rows->values());

        $productGroupList = collect(DB::connection('mysql_sb')->select("
select product_group from masterproduct
where product_group is not null and product_group <> ''
group by product_group order by product_group"))->pluck('product_group');

        $lineMap = $lineMap->map(function ($row) {
            $totalDays = $row->tot_days !== null ? (int) round($row->tot_days) : 1;
            $totalDays = max($totalDays, 1);
            $row->tot_days_rounded = $totalDays;

            $workingDates = $this->workingDatesFrom($row->tgl_start, $totalDays);
            $row->tgl_end = !empty($workingDates) ? end($workingDates) : $row->tgl_start;
            $row->output_per_day = $row->output_based_eff !== null ? (int) round($row->output_based_eff) : null;
            $row->ramp_up_efficiency = $row->ramp_up_efficiency ? json_decode($row->ramp_up_efficiency, true) : [];

            $dailyPlan = [];
            $dailyEfficiency = [];
            foreach ($workingDates as $i => $dateKey) {
                if ($i < count($row->ramp_up_efficiency) && $row->output_day_100 !== null) {
                    $dailyPlan[$dateKey] = (int) round($row->output_day_100 * $row->ramp_up_efficiency[$i]);
                    $dailyEfficiency[$dateKey] = round($row->ramp_up_efficiency[$i] * 100, 1);
                } else {
                    $dailyPlan[$dateKey] = $row->output_per_day;
                    $dailyEfficiency[$dateKey] = $row->efficiency !== null ? round($row->efficiency * 100, 1) : null;
                }
            }
            $row->daily_plan = $dailyPlan;
            $row->daily_efficiency = $dailyEfficiency;
            $row->ramp_up_dates = array_slice(array_keys($dailyPlan), 0, count($row->ramp_up_efficiency));
            $row->style_color = $this->styleColorFromName($row->style);

            $row->edit_payload = [
                'id' => $row->id,
                'line' => $row->line,
                'style' => $row->style,
                'product_group' => $row->product_group,
                'smv' => $row->smv,
                'efficiency' => $row->efficiency,
                'qty_order' => $row->qty_order,
                'buyer' => $row->buyer,
                'man_power' => $row->man_power,
                'working_min' => $row->working_min,
                'tgl_start' => $row->tgl_start,
                'ramp_up_efficiency' => $row->ramp_up_efficiency,
            ];

            return $row;
        });

        $lineMapByLine = $lineMap->groupBy('line');

        $filterStart = $request->input('tgl_dari') ?: date('Y-m-01');
        $filterEnd = $request->input('tgl_sampai') ?: date('Y-m-t');

        $calendarStart = $filterStart . ' 00:00:00';
        $calendarEnd = $filterEnd . ' 23:59:59';

        $calendarDates = DB::select("select tanggal, nama_hari, status_prod from dim_date where tanggal between ? and ? order by tanggal asc", [$calendarStart, $calendarEnd]);

        $actualRows = DB::connection('mysql_sb')->select("WITH a as (
select created_by,date(updated_at) tgl_trans, count(*) tot_rfts, so_det_id from output_rfts
left join master_plan mp on output_rfts.master_plan_id = mp.id
where created_at >= ? and created_at <= ? and mp.cancel = 'N'
group by so_det_id, created_by, date(created_at)
)

SELECT tgl_trans, up.username as line, sum(tot_rfts) tot_rfts, supplier as buyer, ac.styleno, ac.kpno as ws
FROM a
left join user_sb_wip u on a.created_by = u.id
left join userpassword up on up.line_id = u.line_id
LEFT JOIN so_det sd on a.so_det_id = sd.id
left join so on sd.id_so = so.id
left join act_costing	 ac on so.id_cost = ac.id
left join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by styleno, kpno, up.username, tgl_trans", [$calendarStart, $calendarEnd]);

        $actualByLineDate = collect($actualRows)
            ->groupBy('line')
            ->map(function ($lineGroup) {
                return $lineGroup->groupBy('tgl_trans')->map(function ($dateGroup) {
                    return $dateGroup
                        ->groupBy(fn($r) => ($r->buyer ?? '') . '|' . ($r->styleno ?? ''))
                        ->map(function ($group) {
                            $first = $group->first();
                            return (object) [
                                'buyer' => $first->buyer,
                                'styleno' => $first->styleno,
                                'tot_rfts' => $group->sum('tot_rfts'),
                                'ws_breakdown' => $group
                                    ->groupBy(fn($r) => $r->ws ?? '-')
                                    ->map(fn($wsGroup) => (object) [
                                        'ws' => $wsGroup->first()->ws,
                                        'tot_rfts' => $wsGroup->sum('tot_rfts'),
                                    ])
                                    ->values(),
                            ];
                        })
                        ->values();
                });
            });

        // For non-AJAX (initial page load)
        return view('ppic.line_map', [
            'page' => 'dashboard-ppic',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'ppic_line_map',
            'containerFluid' => true,
            'line' => $line,
            'lineMap' => $lineMap,
            'lineMapByLine' => $lineMapByLine,
            'lineNameByUsername' => $lineNameByUsername,
            'productGroupByLine' => $productGroupByLine,
            'productGroupList' => $productGroupList,
            'calendarDates' => $calendarDates,
            'actualByLineDate' => $actualByLineDate,
            'filterStart' => $filterStart,
            'filterEnd' => $filterEnd,
        ]);
    }

    public function store_ppic_line_map(Request $request)
    {
        $validated = $request->validate([
            'editid' => 'nullable|integer|exists:ppic_line_map,id',
            'cboline' => 'required|string',
            'cboproductgroup' => 'nullable|string',
            'txtstyle' => 'nullable|string',
            'txtsmv' => 'nullable|numeric',
            'txtefficiency' => 'nullable|numeric',
            'txtorderqty' => 'nullable|numeric',
            'txtbuyer' => 'nullable|string',
            'txtmanpower' => 'nullable|numeric',
            'txtworkingminutes' => 'nullable|numeric',
            'cbodate' => 'nullable|date',
            'ramp_efficiency' => 'nullable|array',
            'ramp_efficiency.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $efficiency = $validated['txtefficiency'] ?? null;
        if ($efficiency !== null) {
            $efficiency = $efficiency / 100;
        }

        $smv = $validated['txtsmv'] ?? null;
        $manPower = $validated['txtmanpower'] ?? null;
        $workingMinutes = $validated['txtworkingminutes'] ?? null;
        $qtyOrder = $validated['txtorderqty'] ?? null;

        $rampUpEfficiency = collect($validated['ramp_efficiency'] ?? [])
            ->filter(fn($val) => $val !== null && $val !== '')
            ->map(fn($val) => round($val / 100, 4))
            ->values()
            ->all();

        $minsAvailable = ($manPower !== null && $workingMinutes !== null) ? $manPower * $workingMinutes : null;
        $outputPerDay100 = ($minsAvailable !== null && $smv > 0) ? $minsAvailable / $smv : null;
        $outputPerDayEfficiency = ($outputPerDay100 !== null && $efficiency !== null) ? $outputPerDay100 * $efficiency : null;

        $totalDays = $this->simulateTotalDays($outputPerDay100, $qtyOrder, $efficiency, $rampUpEfficiency);

        $data = [
            'line' => $validated['cboline'],
            'tgl_start' => $validated['cbodate'] ?? null,
            'style' => isset($validated['txtstyle']) ? strtoupper($validated['txtstyle']) : null,
            'product_group' => $validated['cboproductgroup'] ?? null,
            'smv' => $smv,
            'efficiency' => $efficiency,
            'qty_order' => $qtyOrder,
            'buyer' => isset($validated['txtbuyer']) ? strtoupper($validated['txtbuyer']) : null,
            'man_power' => $manPower,
            'working_min' => $workingMinutes,
            'mins_avail' => $minsAvailable,
            'output_day_100' => $outputPerDay100,
            'output_based_eff' => $outputPerDayEfficiency,
            'tot_days' => $totalDays,
            'ramp_up_days' => count($rampUpEfficiency) ?: null,
            'ramp_up_efficiency' => count($rampUpEfficiency) ? json_encode($rampUpEfficiency) : null,
            'updated_at' => now(),
        ];

        $overlap = $this->findLineMapOverlap($data['line'], $data['tgl_start'], $totalDays, $validated['editid'] ?? null);
        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tersebut sudah terisi style ' . ($overlap->style ?? '-') . ' di line yang sama.',
            ], 422);
        }

        if (!empty($validated['editid'])) {
            DB::table('ppic_line_map')->where('id', $validated['editid'])->update($data);
            $message = 'Data Line Map berhasil diupdate';
        } else {
            $data['cancel'] = 'N';
            $data['created_at'] = now();
            $data['created_by'] = auth()->user()->username ?? null;
            DB::table('ppic_line_map')->insert($data);
            $message = 'Data Line Map berhasil disimpan';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function cancel_ppic_line_map($id)
    {
        DB::table('ppic_line_map')->where('id', $id)->update([
            'cancel' => 'Y',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Line Map berhasil dihapus',
        ]);
    }

    public function preview_move_ppic_line_map(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:ppic_line_map,id',
            'target_line' => 'required|string',
            'target_date' => 'required|date',
        ]);

        $moves = $this->computeCascade($validated['target_line'], $validated['id'], $validated['target_date']);

        if ($moves === null) {
            return response()->json([
                'success' => false,
                'message' => 'Data Line Map tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'moves' => $moves,
        ]);
    }

    public function move_ppic_line_map(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:ppic_line_map,id',
            'target_line' => 'required|string',
            'target_date' => 'required|date',
        ]);

        $moves = $this->computeCascade($validated['target_line'], $validated['id'], $validated['target_date']);

        if ($moves === null) {
            return response()->json([
                'success' => false,
                'message' => 'Data Line Map tidak ditemukan',
            ], 404);
        }

        DB::transaction(function () use ($moves, $validated) {
            foreach ($moves as $move) {
                $update = [
                    'tgl_start' => $move['new_start'],
                    'updated_at' => now(),
                ];

                if ($move['is_dragged']) {
                    $update['line'] = $validated['target_line'];
                }

                DB::table('ppic_line_map')->where('id', $move['id'])->update($update);
            }
        });

        $shiftedCount = collect($moves)->filter(fn($m) => !$m['is_dragged'] && $m['shifted'])->count();

        return response()->json([
            'success' => true,
            'message' => $shiftedCount > 0
                ? "Jadwal berhasil dipindahkan, {$shiftedCount} jadwal lain ikut digeser mundur"
                : 'Jadwal Line Map berhasil dipindahkan',
        ]);
    }

    /**
     * Places the dragged entry at $draggedNewStart on $targetLine, then walks the
     * target line's timeline chronologically, pushing every entry whose start
     * would now overlap the previous one forward to the next free working day
     * (duration/tot_days untouched). Returns null if the dragged entry no longer exists.
     */
    private function computeCascade(string $targetLine, $draggedId, string $draggedNewStart): ?array
    {
        $draggedRow = DB::table('ppic_line_map')->where('id', $draggedId)->first();
        if (!$draggedRow) {
            return null;
        }

        $others = DB::table('ppic_line_map')
            ->where('line', $targetLine)
            ->where('id', '!=', $draggedId)
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->orderBy('tgl_start')
            ->get();

        $items = collect([
            (object) [
                'id' => $draggedRow->id,
                'style' => $draggedRow->style,
                'buyer' => $draggedRow->buyer,
                'product_group' => $draggedRow->product_group,
                'tot_days' => max((int) round($draggedRow->tot_days ?? 1), 1),
                'start' => $draggedNewStart,
                'is_dragged' => true,
            ],
        ])->concat($others->map(fn($e) => (object) [
            'id' => $e->id,
            'style' => $e->style,
            'buyer' => $e->buyer,
            'product_group' => $e->product_group,
            'tot_days' => max((int) round($e->tot_days ?? 1), 1),
            'start' => $e->tgl_start,
            'is_dragged' => false,
        ]))->sortBy('start')->values();

        $moves = [];
        $cursor = null;

        foreach ($items as $item) {
            $newStart = $item->start;
            if ($cursor !== null && $newStart < $cursor) {
                $newStart = $cursor;
            }

            $workingDates = $this->workingDatesFrom($newStart, $item->tot_days);
            $newEnd = !empty($workingDates) ? end($workingDates) : $newStart;
            $cursor = $this->nextWorkingDay($newEnd);

            $moves[] = [
                'id' => $item->id,
                'style' => $item->style,
                'buyer' => $item->buyer,
                'product_group' => $item->product_group,
                'is_dragged' => $item->is_dragged,
                'new_start' => $newStart,
                'new_end' => $newEnd,
                'shifted' => $newStart !== $item->start,
                'dates' => $workingDates,
            ];
        }

        return $moves;
    }

    private function nextWorkingDay(string $date): string
    {
        $next = DB::selectOne(
            "select min(tanggal) tanggal from dim_date where tanggal > ? and status_prod = 'KERJA'",
            [$date]
        );

        return $next->tanggal ? date('Y-m-d', strtotime($next->tanggal)) : date('Y-m-d', strtotime($date . ' +1 day'));
    }

    /**
     * Deterministic per-style color, independent of row order/date so a style's
     * color never changes just because its schedule position changed (e.g. when
     * dragged to a different date, which reorders the list sorted by tgl_start).
     * md5 gives a much better hue spread than crc32 for short strings, which
     * previously produced frequent near-hue collisions (looked "all greenish").
     */
    private function styleColorFromName(?string $style): string
    {
        $key = strtoupper(trim($style ?? ''));
        $hash = hexdec(substr(md5($key), 0, 8));
        $hue = $hash % 360;

        return "hsl({$hue}, 68%, 45%)";
    }

    private function findLineMapOverlap(?string $line, ?string $startDate, $totalDays, ?int $ignoreId = null)
    {
        if (!$line || !$startDate) {
            return null;
        }

        $totalDays = $totalDays !== null ? (int) round($totalDays) : 1;
        $totalDays = max($totalDays, 1);
        $workingDates = $this->workingDatesFrom($startDate, $totalDays);
        $endDate = !empty($workingDates) ? end($workingDates) : $startDate;

        $query = DB::table('ppic_line_map')
            ->where('line', $line)
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->get()->first(function ($row) use ($startDate, $endDate) {
            if (!$row->tgl_start) {
                return false;
            }

            $rowTotalDays = $row->tot_days !== null ? (int) round($row->tot_days) : 1;
            $rowTotalDays = max($rowTotalDays, 1);
            $rowStartDate = $row->tgl_start;
            $rowWorkingDates = $this->workingDatesFrom($rowStartDate, $rowTotalDays);
            $rowEndDate = !empty($rowWorkingDates) ? end($rowWorkingDates) : $rowStartDate;

            return $rowStartDate <= $endDate && $rowEndDate >= $startDate;
        });
    }

    /**
     * $startDate is always kept as day 1 even if it lands on a holiday (an
     * intentional start date, e.g. planned overtime). Every day after that
     * skips status_prod = LIBUR and continues on the next working day.
     */
    private function workingDatesFrom(string $startDate, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $dates = [$startDate];
        $remaining = $count - 1;

        if ($remaining > 0) {
            $bufferDays = (int) ceil($remaining * 0.6) + 30;
            $rangeStart = date('Y-m-d', strtotime($startDate . ' +1 day'));
            $rangeEnd = date('Y-m-d', strtotime($startDate . ' +' . ($remaining + $bufferDays) . ' days'));

            $dates = array_merge(
                $dates,
                array_slice($this->workingDatesInRange($rangeStart, $rangeEnd), 0, $remaining)
            );
        }

        return $dates;
    }

    private function workingDatesInRange(string $from, string $to): array
    {
        $dates = DB::select(
            "select tanggal from dim_date where tanggal >= ? and tanggal <= ? and status_prod = 'KERJA' order by tanggal asc",
            [$from, $to]
        );

        return collect($dates)->map(fn($d) => date('Y-m-d', strtotime($d->tanggal)))->values()->all();
    }

    private function simulateTotalDays(?float $outputPerDay100, ?float $qtyOrder, ?float $steadyEfficiency, array $rampUpEfficiency)
    {
        if (!$outputPerDay100 || !$qtyOrder || $steadyEfficiency === null) {
            return null;
        }

        $produced = 0;
        $day = 0;
        $maxDays = 3650;

        while ($produced < $qtyOrder && $day < $maxDays) {
            $eff = $day < count($rampUpEfficiency) ? $rampUpEfficiency[$day] : $steadyEfficiency;
            $dailyOutput = $outputPerDay100 * $eff;

            if ($dailyOutput <= 0) {
                return null;
            }

            $produced += $dailyOutput;
            $day++;
        }

        return $day;
    }
}
