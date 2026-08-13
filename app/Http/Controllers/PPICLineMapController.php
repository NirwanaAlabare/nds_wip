<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;


class PPICLineMapController extends Controller
{
    private const EDIT_ALLOWED_USERNAMES = ['eka', 'admin_01', 'nirwana_it', 'reza'];

    // Rows parked in the temporary holding area (line/tgl_start = null) are capped
    // at this many so it stays a quick "staging" spot, not a second backlog list.
    private const TEMP_HOLDING_CAPACITY = 3;

    // Undo stack depth for move_ppic_line_map / move_to_temp_ppic_line_map. Only
    // these two actions are snapshotted (see snapshotBeforeMutation) — not
    // create/edit/cancel, which are out of scope for undo for now.
    private const HISTORY_LIMIT = 3;

    // dim_date.status_prod alone misses ad-hoc holidays managed separately in
    // signalbit_erp.mgt_rep_hari_libur, so every KERJA/LIBUR check in this
    // controller joins it in and treats a matching hari-libur row as authoritative
    // (LIBUR) regardless of what dim_date itself says.
    private const DIM_DATE_JOIN = "dim_date a
        left join signalbit_erp.mgt_rep_hari_libur b on a.tanggal = b.tanggal_libur";
    private const DIM_DATE_STATUS_FINAL = "case when b.id is not null then 'LIBUR' else a.status_prod end";

    private function canEditLineMap(): bool
    {
        return in_array(auth()->user()->username ?? null, self::EDIT_ALLOWED_USERNAMES);
    }

    public function ppic_line_map(Request $request)
    {
        return view('ppic.line_map', array_merge(
            $this->buildLineMapData($request),
            [
                'page' => 'dashboard-ppic',
                'subPageGroup' => 'asset-mesin',
                'subPage' => 'ppic_line_map',
                'containerFluid' => true,
            ]
        ));
    }

    public function ppic_line_map_refresh(Request $request)
    {
        $data = $this->buildLineMapData($request);

        return response()->json([
            'success' => true,
            'calendar' => view('ppic.partials.line_map_calendar', $data)->render(),
            'listRows' => view('ppic.partials.line_map_list_rows', $data)->render(),
            'tempHolding' => view('ppic.partials.line_map_temp_holding', $data)->render(),
            'productGroupByLine' => $data['productGroupByLine'],
            'colorGroups' => $data['colorGroups'],
            'lineNextAvailableDate' => $data['lineNextAvailableDate'],
            'occupiedDatesByLine' => $data['occupiedDatesByLine'],
            'lastUpdated' => $data['lastUpdated'] ? date('d-m-Y H:i:s', strtotime($data['lastUpdated'])) : '-',
            'undoCount' => $data['undoCount'],
        ]);
    }

    public function set_ppic_line_map_color(Request $request)
    {
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'row_id' => 'required|integer|exists:ppic_line_map,id',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $row = DB::table('ppic_line_map')->where('id', $validated['row_id'])->first();

        $query = DB::table('ppic_line_map');
        if ($row->id_line_map) {
            $query->where('id_line_map', $row->id_line_map);
        } else {
            $query->where('id', $row->id);
        }
        $query->update([
            'color' => $validated['color'],
            'font_color' => $validated['font_color'],
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Warna berhasil disimpan',
        ]);
    }

    public function ppic_line_map_live(Request $request)
    {
        return view('ppic.line_map_live', array_merge(
            $this->buildLineMapData($request),
            [
                'containerFluid' => true,
                'navbar' => false,
                'footer' => false,
            ]
        ));
    }

    private function buildLineMapData(Request $request): array
    {
        $line = DB::connection('mysql_sb')->select("
select * from userpassword where username like '%line%' order by username asc");

        $filterStart = $request->input('tgl_dari') ?: date('Y-m-01');
        $filterEnd = $request->input('tgl_sampai') ?: date('Y-m-t');

        $lineMap = DB::table('ppic_line_map')
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->latest('tgl_start')
            ->get();

        $lastUpdated = DB::table('ppic_line_map')->max('updated_at');

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

            // A row parked in the temporary holding area has no line/date yet
            // (see move_to_temp_ppic_line_map), so it has no working dates to compute.
            $workingDates = $row->tgl_start !== null ? $this->workingDatesFrom($row->tgl_start, $totalDays) : [];
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
                    $dailyEfficiency[$dateKey] = $row->efficiency !== null ? round($row->efficiency, 1) : null;
                }
            }
            $row->daily_plan = $dailyPlan;
            $row->daily_efficiency = $dailyEfficiency;
            $row->ramp_up_dates = array_slice(array_keys($dailyPlan), 0, count($row->ramp_up_efficiency));
            $row->style_color = $row->color ?: $this->styleColorFromName($row->style);
            $row->font_color = $row->font_color ?: '#ffffff';

            return $row;
        });

        // Rows created together share id_line_map so they open/edit as one group;
        // legacy rows without a group id are treated as a solo group of themselves.
        $groupKey = fn($row) => $row->id_line_map ?: ('solo-' . $row->id);
        $groupedForEdit = $lineMap->groupBy($groupKey);

        $lineMap = $lineMap->map(function ($row) use ($groupedForEdit, $groupKey) {
            $siblings = $groupedForEdit->get($groupKey($row), collect())->sortBy('tgl_start')->values();

            // Style/buyer/product group/SMV/color are shared across a group's rows
            // by construction, so any sibling's values (here, $row's own) represent
            // the whole group. Man power/working minutes/efficiency/start date/ramp-up
            // are per line, so they stay per entry.
            $row->edit_payload = [
                'group_id' => $row->id_line_map,
                'style' => $row->style,
                'buyer' => $row->buyer,
                'product_group' => $row->product_group,
                'smv' => $row->smv,
                'color' => $row->style_color,
                'font_color' => $row->font_color,
                'has_custom_color' => (bool) $row->color,
                'qty_order_total' => $siblings->sum('qty_order'),
                'lines' => $siblings->map(fn($s) => [
                    'id' => $s->id,
                    'line' => $s->line,
                    'qty_order' => $s->qty_order,
                    'man_power' => $s->man_power,
                    'working_min' => $s->working_min,
                    'efficiency' => $s->efficiency,
                    'tgl_start' => $s->tgl_start,
                    'tgl_finish' => $s->tgl_end,
                    'ramp_up_efficiency' => $s->ramp_up_efficiency,
                ])->values()->all(),
            ];

            return $row;
        });

        // Rows with no line yet are sitting in the temporary holding area (see
        // move_to_temp_ppic_line_map) and never belong on the calendar grid, its
        // date-filtered list, or the per-line "next available date" calc below.
        $tempHolding = $lineMap->filter(fn($row) => $row->line === null)->sortBy('id')->values();
        $scheduledLineMap = $lineMap->filter(fn($row) => $row->line !== null)->values();

        $lineMapByLine = $scheduledLineMap->groupBy('line');

        // Default "Start Day Calendar" suggestion per line: the working day right
        // after that line's latest active plan ends, so a new order slots in right
        // where the line actually goes free instead of defaulting to today (which
        // would usually collide with whatever is already scheduled).
        $lineNextAvailableDate = collect($line)->mapWithKeys(function ($ln) use ($lineMapByLine) {
            $lastEnd = $lineMapByLine->get($ln->username, collect())->max('tgl_end');

            return [$ln->username => $lastEnd ? $this->nextWorkingDay($lastEnd) : date('Y-m-d')];
        });

        $lineMapList = $scheduledLineMap
            ->filter(fn($row) => $row->tgl_start <= $filterEnd && $row->tgl_end >= $filterStart)
            ->values();

        // Powers the "Start Day Calendar" picker: for each line, which dates already
        // have a plan sitting on them, so the picker can mark them instead of the
        // user having to cross-check the calendar grid separately. Built from
        // $scheduledLineMap (not the date-filtered $lineMapList) so it's not limited
        // to the current tgl_dari/tgl_sampai filter.
        $occupiedDatesByLine = $scheduledLineMap
            ->groupBy('line')
            ->map(function ($rows) {
                $byDate = [];
                foreach ($rows as $row) {
                    foreach ($row->daily_plan as $date => $qty) {
                        $byDate[$date][] = [
                            'style' => $row->style,
                            'buyer' => $row->buyer,
                            'qty' => $qty,
                        ];
                    }
                }
                return $byDate;
            });

        // One color swatch per order group (same id_line_map), not per line, since
        // all lines/dates belonging to one order share a single color on the calendar.
        $colorGroups = $lineMapList
            ->groupBy($groupKey)
            ->map(function ($rows) use ($lineNameByUsername) {
                $first = $rows->sortBy('tgl_start')->first();

                return (object) [
                    'row_id' => $first->id,
                    'style' => $first->style,
                    'buyer' => $first->buyer,
                    'color' => $first->style_color,
                    'font_color' => $first->font_color,
                    'lines' => $rows->pluck('line')->unique()->map(fn($u) => $lineNameByUsername[$u] ?? $u)->values()->all(),
                    'tgl_start' => $rows->min('tgl_start'),
                    'tgl_end' => $rows->max('tgl_end'),
                ];
            })
            ->sortBy('tgl_start')
            ->values();

        $calendarStart = $filterStart . ' 00:00:00';
        $calendarEnd = $filterEnd . ' 23:59:59';

        $calendarDates = DB::select("
            select a.tanggal, a.nama_hari, " . self::DIM_DATE_STATUS_FINAL . " as status_prod
            from " . self::DIM_DATE_JOIN . "
            where a.tanggal between ? and ?
            order by a.tanggal asc", [$calendarStart, $calendarEnd]);

        // Non-working dates from the same table workingDatesFrom()/workingDatesInRange()
        // use server-side, sent to the client so the "Tgl Finish" preview in the New/Edit
        // Line Map modal can skip holidays too instead of just adding calendar days.
        $holidayDates = collect(DB::select("
            select a.tanggal
            from " . self::DIM_DATE_JOIN . "
            where " . self::DIM_DATE_STATUS_FINAL . " != 'KERJA'"))
            ->map(fn($d) => date('Y-m-d', strtotime($d->tanggal)))
            ->values();

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

        return [
            'line' => $line,
            'lineMap' => $lineMapList,
            'lineMapByLine' => $lineMapByLine,
            'tempHolding' => $tempHolding,
            'tempHoldingCapacity' => self::TEMP_HOLDING_CAPACITY,
            'colorGroups' => $colorGroups,
            'lineNextAvailableDate' => $lineNextAvailableDate,
            'lineNameByUsername' => $lineNameByUsername,
            'productGroupByLine' => $productGroupByLine,
            'productGroupList' => $productGroupList,
            'calendarDates' => $calendarDates,
            'holidayDates' => $holidayDates,
            'actualByLineDate' => $actualByLineDate,
            'occupiedDatesByLine' => $occupiedDatesByLine,
            'filterStart' => $filterStart,
            'filterEnd' => $filterEnd,
            'lastUpdated' => $lastUpdated,
            'canEditLineMap' => $this->canEditLineMap(),
            'undoCount' => DB::table('ppic_line_map_history')->count(),
        ];
    }

    /**
     * One order (style/buyer/product group/SMV/color shared) can be split across
     * several lines in a single submission. Man power/working minutes/efficiency/
     * start date/qty/ramp-up all vary per line (each line has its own capacity and
     * schedule), and the qty portions must sum exactly to txtorderqtytotal. All rows
     * in the submission share id_line_map so they render/edit together as one group.
     */
    public function store_ppic_line_map(Request $request)
    {
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'group_id' => 'nullable|integer',
            'txtstyle' => 'nullable|string',
            'txtbuyer' => 'nullable|string',
            'cboproductgroup' => 'nullable|string',
            'txtsmv' => 'nullable|numeric',
            'txtorderqtytotal' => 'required|numeric|min:1',
            'txtcolor' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'txtfontcolor' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'ramp_efficiency' => 'nullable|array',
            'ramp_efficiency.*' => 'nullable|array',
            'ramp_efficiency.*.*' => 'nullable|numeric|min:0|max:100',
            'line_row_id' => 'nullable|array',
            'line_row_id.*' => 'nullable|integer|exists:ppic_line_map,id',
            'cboline' => 'required|array|min:1',
            'cboline.*' => 'required|string',
            'txtorderqtyline' => 'required|array',
            'txtorderqtyline.*' => 'required|numeric|min:1',
            'txtmanpower' => 'required|array',
            'txtmanpower.*' => 'required|numeric|min:0',
            'txtworkingminutes' => 'required|array',
            'txtworkingminutes.*' => 'required|numeric|min:0',
            'txtefficiency' => 'required|array',
            'txtefficiency.*' => 'required|numeric|min:0',
            'cbodate' => 'required|array',
            'cbodate.*' => 'required|date',
        ]);

        $lineCount = count($validated['cboline']);
        foreach (['txtorderqtyline', 'txtmanpower', 'txtworkingminutes', 'txtefficiency', 'cbodate'] as $field) {
            if (count($validated[$field]) !== $lineCount) {
                return response()->json(['success' => false, 'message' => 'Data line tidak lengkap.'], 422);
            }
        }

        if (count(array_unique($validated['cboline'])) !== $lineCount) {
            return response()->json([
                'success' => false,
                'message' => 'Satu line tidak boleh dipilih lebih dari sekali dalam satu order.',
            ], 422);
        }

        $qtyTotal = (int) round($validated['txtorderqtytotal']);
        $qtySum = (int) round(collect($validated['txtorderqtyline'])->sum());
        if ($qtySum !== $qtyTotal) {
            return response()->json([
                'success' => false,
                'message' => "Total qty per line ({$qtySum}) harus sama dengan Order Qty Total ({$qtyTotal}).",
            ], 422);
        }

        $style = isset($validated['txtstyle']) ? strtoupper($validated['txtstyle']) : null;
        $buyer = isset($validated['txtbuyer']) ? strtoupper($validated['txtbuyer']) : null;
        $productGroup = $validated['cboproductgroup'] ?? null;
        $smv = $validated['txtsmv'] ?? null;

        $lineRowIds = array_values(array_filter($validated['line_row_id'] ?? []));

        $groupId = $validated['group_id'] ?? null;
        if (!$groupId && !empty($lineRowIds)) {
            $groupId = (int) $lineRowIds[0];
        }

        $rows = [];
        foreach ($validated['cboline'] as $i => $line) {
            $qtyOrder = $validated['txtorderqtyline'][$i];
            $rowId = $validated['line_row_id'][$i] ?? null;

            $manPower = $validated['txtmanpower'][$i];
            $workingMinutes = $validated['txtworkingminutes'][$i];
            $efficiencyPercent = (int) round($validated['txtefficiency'][$i]);
            $efficiencyFraction = $efficiencyPercent / 100;
            $tglStart = $validated['cbodate'][$i];

            $minsAvailable = $manPower * $workingMinutes;
            $outputPerDay100 = $smv > 0 ? $minsAvailable / $smv : null;
            $outputPerDayEfficiency = $outputPerDay100 !== null ? $outputPerDay100 * $efficiencyFraction : null;

            $rampUpEfficiency = collect($validated['ramp_efficiency'][$i] ?? [])
                ->filter(fn($val) => $val !== null && $val !== '')
                ->map(fn($val) => round($val / 100, 4))
                ->values()
                ->all();

            $totalDays = $this->simulateTotalDays($outputPerDay100, $qtyOrder, $efficiencyFraction, $rampUpEfficiency);

            $daysForFinish = $totalDays !== null ? max((int) round($totalDays), 1) : 1;
            $workingDates = $this->workingDatesFrom($tglStart, $daysForFinish);
            $tglFinish = !empty($workingDates) ? end($workingDates) : $tglStart;

            $overlap = $this->findLineMapOverlap($line, $tglStart, $totalDays, $lineRowIds);
            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal tersebut sudah terisi style ' . ($overlap->style ?? '-') . " di line {$line}.",
                ], 422);
            }

            $rows[] = [
                'row_id' => $rowId,
                'data' => [
                    'line' => $line,
                    'tgl_start' => $tglStart,
                    'tgl_finish' => $tglFinish,
                    'style' => $style,
                    'product_group' => $productGroup,
                    'smv' => $smv,
                    'efficiency' => $efficiencyPercent,
                    'qty_order' => $qtyOrder,
                    'buyer' => $buyer,
                    'color' => $validated['txtcolor'] ?? null,
                    'font_color' => $validated['txtfontcolor'] ?? null,
                    'man_power' => $manPower,
                    'working_min' => $workingMinutes,
                    'mins_avail' => $minsAvailable,
                    'output_day_100' => $outputPerDay100,
                    'output_based_eff' => $outputPerDayEfficiency,
                    'tot_days' => $totalDays,
                    'ramp_up_days' => count($rampUpEfficiency) ?: null,
                    'ramp_up_efficiency' => count($rampUpEfficiency) ? json_encode($rampUpEfficiency) : null,
                    'updated_at' => now(),
                ],
            ];
        }

        DB::transaction(function () use (&$groupId, $rows, $validated) {
            $keptIds = [];

            foreach ($rows as $row) {
                if ($row['row_id']) {
                    DB::table('ppic_line_map')->where('id', $row['row_id'])->update(
                        $row['data'] + ['id_line_map' => $groupId]
                    );
                    $keptIds[] = $row['row_id'];
                } else {
                    $insertId = DB::table('ppic_line_map')->insertGetId($row['data'] + [
                        'cancel' => 'N',
                        'created_at' => now(),
                        'created_by' => auth()->user()->username ?? null,
                        'id_line_map' => $groupId,
                    ]);

                    if (!$groupId) {
                        $groupId = $insertId;
                        DB::table('ppic_line_map')->where('id', $insertId)->update(['id_line_map' => $groupId]);
                    }

                    $keptIds[] = $insertId;
                }
            }

            if (!empty($validated['group_id'])) {
                $existingGroupIds = DB::table('ppic_line_map')->where('id_line_map', $validated['group_id'])->pluck('id')->all();
                $toCancel = array_diff($existingGroupIds, $keptIds);
                if (!empty($toCancel)) {
                    DB::table('ppic_line_map')->whereIn('id', $toCancel)->update([
                        'cancel' => 'Y',
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data Line Map berhasil disimpan',
        ]);
    }

    public function cancel_ppic_line_map($id)
    {
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

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
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

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
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

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

        $this->snapshotBeforeMutation(collect($moves)->pluck('id')->all(), 'move');

        DB::transaction(function () use ($moves, $validated) {
            foreach ($moves as $move) {
                $update = [
                    'tgl_start' => $move['new_start'],
                    'tgl_finish' => $move['new_end'],
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
     * Parks an already-scheduled plan in the temporary holding area by clearing
     * its line/date, freeing up that slot on the calendar. Capped at
     * TEMP_HOLDING_CAPACITY so the area stays a quick staging spot. The plan
     * keeps its qty/man power/tot_days etc, so dropping it back onto a real
     * line+date via move_ppic_line_map recomputes its schedule from there.
     */
    public function move_to_temp_ppic_line_map(Request $request)
    {
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|integer|exists:ppic_line_map,id',
        ]);

        $row = DB::table('ppic_line_map')
            ->where('id', $validated['id'])
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Data Line Map tidak ditemukan'], 404);
        }

        if ($row->line === null) {
            return response()->json(['success' => false, 'message' => 'Plan ini sudah berada di area temporary.'], 422);
        }

        $tempCount = DB::table('ppic_line_map')
            ->whereNull('line')
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->count();

        if ($tempCount >= self::TEMP_HOLDING_CAPACITY) {
            return response()->json([
                'success' => false,
                'message' => 'Area temporary sudah penuh (maksimal ' . self::TEMP_HOLDING_CAPACITY . ' plan).',
            ], 422);
        }

        $this->snapshotBeforeMutation([$row->id], 'move_to_temp');

        DB::table('ppic_line_map')->where('id', $row->id)->update([
            'line' => null,
            'tgl_start' => null,
            'tgl_finish' => null,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan dipindahkan ke area temporary',
        ]);
    }

    /**
     * Records a pre-mutation snapshot (line/tgl_start/tgl_finish) for every given
     * row id, so undo_ppic_line_map can restore them exactly as they were. Trims
     * the table down to the last HISTORY_LIMIT entries after inserting, so this
     * behaves as a fixed-depth undo stack rather than an ever-growing log.
     */
    private function snapshotBeforeMutation(array $ids, string $action): void
    {
        $ids = collect($ids)->filter()->unique()->values()->all();
        if (empty($ids)) {
            return;
        }

        $snapshot = DB::table('ppic_line_map')
            ->whereIn('id', $ids)
            ->get(['id', 'line', 'tgl_start', 'tgl_finish'])
            ->map(fn($row) => (array) $row)
            ->values()
            ->all();

        DB::table('ppic_line_map_history')->insert([
            'action' => $action,
            'snapshot' => json_encode($snapshot),
            'created_by' => auth()->user()->username ?? null,
            'created_at' => now(),
        ]);

        $staleIds = DB::table('ppic_line_map_history')
            ->orderByDesc('id')
            ->skip(self::HISTORY_LIMIT)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($staleIds->isNotEmpty()) {
            DB::table('ppic_line_map_history')->whereIn('id', $staleIds)->delete();
        }
    }

    /**
     * Pops the most recent entry off the undo stack (see snapshotBeforeMutation)
     * and restores every row in its snapshot to the line/tgl_start/tgl_finish it
     * had right before that move — reversing a whole cascade in one step, not
     * just the dragged row.
     */
    public function undo_ppic_line_map(Request $request)
    {
        if (!$this->canEditLineMap()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $entry = DB::table('ppic_line_map_history')->orderByDesc('id')->first();

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada histori perpindahan untuk di-undo.',
            ], 404);
        }

        $snapshot = json_decode($entry->snapshot, true) ?: [];

        DB::transaction(function () use ($snapshot, $entry) {
            foreach ($snapshot as $row) {
                DB::table('ppic_line_map')->where('id', $row['id'])->update([
                    'line' => $row['line'],
                    'tgl_start' => $row['tgl_start'],
                    'tgl_finish' => $row['tgl_finish'],
                    'updated_at' => now(),
                ]);
            }

            DB::table('ppic_line_map_history')->where('id', $entry->id)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Perpindahan terakhir berhasil di-undo',
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
            "select min(a.tanggal) tanggal
            from " . self::DIM_DATE_JOIN . "
            where a.tanggal > ? and " . self::DIM_DATE_STATUS_FINAL . " = 'KERJA'",
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

        return $this->hslToHex($hue, 68, 45);
    }

    /**
     * Hex output (not "hsl(...)") so this can double as the prefill value for a
     * native <input type="color">, and so it's format-compatible with manually
     * chosen colors stored in ppic_line_map.color.
     */
    private function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        $toHex = fn($v) => str_pad(dechex((int) round(($v + $m) * 255)), 2, '0', STR_PAD_LEFT);

        return '#' . $toHex($r) . $toHex($g) . $toHex($b);
    }

    /**
     * $ignoreIds accepts a single id or an array of ids. When editing a multi-line
     * group, every sibling row in the submission is passed here (not just the row
     * being checked) because all of them get replaced in the same transaction —
     * a row still sitting on its old line/date in the DB shouldn't false-positive
     * against a sibling block that is about to move off that same line.
     */
    private function findLineMapOverlap(?string $line, ?string $startDate, $totalDays, $ignoreIds = null)
    {
        if (!$line || !$startDate) {
            return null;
        }

        $ignoreIds = collect(is_array($ignoreIds) ? $ignoreIds : [$ignoreIds])->filter()->values()->all();

        $totalDays = $totalDays !== null ? (int) round($totalDays) : 1;
        $totalDays = max($totalDays, 1);
        $workingDates = $this->workingDatesFrom($startDate, $totalDays);
        $endDate = !empty($workingDates) ? end($workingDates) : $startDate;

        $query = DB::table('ppic_line_map')
            ->where('line', $line)
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            });

        if (!empty($ignoreIds)) {
            $query->whereNotIn('id', $ignoreIds);
        }

        return $query->get()->first(function ($row) use ($startDate, $endDate) {
            if (!$row->tgl_start) {
                return false;
            }

            // tgl_finish is already computed and stored on every row (see
            // store_ppic_line_map/move_ppic_line_map), so reuse it here instead of
            // recomputing via workingDatesFrom() — that would fire one extra
            // dim_date query per existing row on the line, which made this check
            // slow enough on busy lines to risk the request timing out client-side
            // even though the save itself would still complete on the server.
            $rowEndDate = $row->tgl_finish ?: $row->tgl_start;

            return $row->tgl_start <= $endDate && $rowEndDate >= $startDate;
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
            "select a.tanggal
            from " . self::DIM_DATE_JOIN . "
            where a.tanggal >= ? and a.tanggal <= ? and " . self::DIM_DATE_STATUS_FINAL . " = 'KERJA'
            order by a.tanggal asc",
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
