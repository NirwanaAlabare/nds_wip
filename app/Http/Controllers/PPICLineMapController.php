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

        $lineMap = $lineMap->map(function ($row) {
            $totalDays = $row->tot_days !== null ? (int) round($row->tot_days) : 1;
            $totalDays = max($totalDays, 1);
            $row->tot_days_rounded = $totalDays;

            $workingDates = $this->workingDatesFrom($row->tgl_start, $totalDays);
            $row->plan_start = $workingDates[0] ?? $row->tgl_start;
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

        $filterStart = $request->input('tgl_dari');
        $filterEnd = $request->input('tgl_sampai');

        $calendarStart = $filterStart ?: date('Y-m-01 00:00:00');
        $calendarEnd = $filterEnd ?: date('Y-m-t 23:59:59');

        $calendarDates = DB::select("select tanggal, nama_hari, status_prod from dim_date where tanggal between ? and ? order by tanggal asc", [$calendarStart, $calendarEnd]);

        $actualRows = DB::connection('mysql_sb')->select("WITH a as (
select created_by,date(updated_at) tgl_trans, count(*) tot_rfts, so_det_id from output_rfts
left join master_plan mp on output_rfts.master_plan_id = mp.id
where created_at >= ? and created_at <= ? and mp.cancel = 'N'
group by so_det_id, created_by, date(created_at)
)

SELECT tgl_trans, up.username as line, sum(tot_rfts) tot_rfts, supplier as buyer, ac.kpno, ac.styleno, sd.reff_no, ac.styleno
FROM a
left join user_sb_wip u on a.created_by = u.id
left join userpassword up on up.line_id = u.line_id
LEFT JOIN so_det sd on a.so_det_id = sd.id
left join so on sd.id_so = so.id
left join act_costing	 ac on so.id_cost = ac.id
left join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by styleno, up.username, tgl_trans", [$calendarStart, $calendarEnd]);

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
            'calendarDates' => $calendarDates,
            'actualByLineDate' => $actualByLineDate,
            'filterStart' => $filterStart ?? $calendarStart,
            'filterEnd' => $filterEnd ?? $calendarEnd,
        ]);
    }

    public function store_ppic_line_map(Request $request)
    {
        $validated = $request->validate([
            'editid' => 'nullable|integer|exists:ppic_line_map,id',
            'cboline' => 'required|string',
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

    public function move_ppic_line_map(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:ppic_line_map,id',
            'target_line' => 'required|string',
            'target_date' => 'required|date',
            'source_date' => 'nullable|date',
        ]);

        $lineMap = DB::table('ppic_line_map')
            ->where('id', $validated['id'])
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->first();

        if (!$lineMap) {
            return response()->json([
                'success' => false,
                'message' => 'Data Line Map tidak ditemukan',
            ], 404);
        }

        $targetStartDate = $validated['target_date'];
        if (!empty($validated['source_date']) && !empty($lineMap->tgl_start)) {
            $totalDaysForRow = $lineMap->tot_days !== null ? max((int) round($lineMap->tot_days), 1) : 1;
            $rowWorkingDates = $this->workingDatesFrom($lineMap->tgl_start, $totalDaysForRow);
            $dayOffset = array_search($validated['source_date'], $rowWorkingDates, true);

            if ($dayOffset !== false) {
                $lookbackStart = date('Y-m-d', strtotime($validated['target_date'] . ' -' . (($dayOffset + 10) * 2 + 30) . ' days'));
                $windowDates = $this->workingDatesInRange($lookbackStart, $validated['target_date']);
                $targetIdx = array_search($validated['target_date'], $windowDates, true);

                if ($targetIdx !== false && ($targetIdx - $dayOffset) >= 0) {
                    $targetStartDate = $windowDates[$targetIdx - $dayOffset];
                }
            }
        }

        $overlap = $this->findLineMapOverlap($validated['target_line'], $targetStartDate, $lineMap->tot_days, $lineMap->id);
        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa dipindahkan. Tanggal tersebut sudah terisi style ' . ($overlap->style ?? '-') . ' di line tujuan.',
            ], 422);
        }

        DB::table('ppic_line_map')
            ->where('id', $validated['id'])
            ->update([
                'line' => $validated['target_line'],
                'tgl_start' => $targetStartDate,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Line Map berhasil dipindahkan',
        ]);
    }

    private function styleColorFromName(?string $style): string
    {
        $hash = abs(crc32(strtoupper(trim($style ?? ''))));
        $hue = $hash % 360;

        return "hsl({$hue}, 62%, 42%)";
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
     * Working days (status_prod = KERJA) starting at $startDate, skipping holidays,
     * limited to $count days.
     */
    private function workingDatesFrom(string $startDate, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $bufferDays = (int) ceil($count * 0.6) + 30;
        $rangeEnd = date('Y-m-d', strtotime($startDate . ' +' . ($count + $bufferDays) . ' days'));

        return array_slice($this->workingDatesInRange($startDate, $rangeEnd), 0, $count);
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
