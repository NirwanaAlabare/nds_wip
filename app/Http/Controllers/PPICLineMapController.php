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
            ->orderBy('line')->orderBy('tgl_start')->get();

        $lineNameByUsername = collect($line)->pluck('FullName', 'username');

        $lineMap = $lineMap->map(function ($row) {
            $totalDays = $row->tot_days !== null ? (int) round($row->tot_days) : 1;
            $totalDays = max($totalDays, 1);
            $row->tot_days_rounded = $totalDays;
            $row->tgl_end = date('Y-m-d', strtotime($row->tgl_start . ' +' . ($totalDays - 1) . ' days'));
            $row->output_per_day = $row->output_based_eff !== null ? (int) round($row->output_based_eff) : null;
            $row->ramp_up_efficiency = $row->ramp_up_efficiency ? json_decode($row->ramp_up_efficiency, true) : [];

            $dailyPlan = [];
            $dailyEfficiency = [];
            for ($i = 0; $i < $totalDays; $i++) {
                $dateKey = date('Y-m-d', strtotime($row->tgl_start . ' +' . $i . ' days'));
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

        $calendarStart = $filterStart ?: date('Y-m-01');
        $calendarEnd = $filterEnd ?: date('Y-m-t');

        $calendarDates = DB::select("select tanggal, nama_hari from dim_date where tanggal between ? and ? order by tanggal asc", [$calendarStart, $calendarEnd]);

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

    private function styleColorFromName(?string $style): string
    {
        $hash = abs(crc32(strtoupper(trim($style ?? ''))));
        $hue = $hash % 360;

        return "hsl({$hue}, 62%, 42%)";
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
