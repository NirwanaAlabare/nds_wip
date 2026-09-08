<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center">{{ $date }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: 800;">{{ ucfirst(str_replace("_", " ", $selectedLine)) }}</th>
        </tr>
        <tr>
            <th style="text-align: center; font-weight: 800;">Hour</th>
            <th style="text-align: center; font-weight: 800;">RFT</th>
            <th style="text-align: center; font-weight: 800;">Defect</th>
            <th style="text-align: center; font-weight: 800;">Rework</th>
            <th style="text-align: center; font-weight: 800;">Reject</th>
            <th style="text-align: center; font-weight: 800;">Actual</th>
            <th style="text-align: center; font-weight: 800;">Target</th>
            <th style="text-align: center; font-weight: 800;">Efficiency</th>
        </tr>
    </thead>
    <tbody>
        @php
            // Pakai $lines langsung (sudah di-eager load & difilter tanggal di ProductionExport).
            // Sebelumnya $lines->firstWhere(...) justru melempar query baru ke DB sehingga
            // relasi yang sudah difilter hilang dan seluruh master plan/output ikut termuat.
            $lineModel = $selectedLine != '' ? $lines : null;

            // Data untuk ACTUAL (tetap filter cancel = 'N')
            // Tidak dibatasi rentang tgl_plan: semua master plan yang punya output di tanggal ini
            // ikut dihitung, supaya total actual sama dengan Report Output.
            $lineData = $lineModel ? $lineModel->masterPlans->where("cancel", 'N') : collect([]);
            $lineDataCurrent = $lineData->where('tgl_plan', $date);

            // Data untuk TARGET (tanpa filter cancel, ambil semua)
            $lineDataTarget = $lineModel ? $lineModel->masterPlans : collect([]);
            $lineDataTargetCurrent = $lineDataTarget->where('tgl_plan', $date);

            // jam_kerja di-sum, sesuai SQL sistem asli: COALESCE(sum(jam_kerja),0)
            $jamKerja = count($lineDataTargetCurrent) > 0 ? round($lineDataTargetCurrent->sum('jam_kerja')) : 0;
            $planTarget = count($lineDataTargetCurrent) > 0 ? $lineDataTargetCurrent->sum('plan_target') : 0;
            $planTargetCurrent = count($lineDataCurrent) > 0 ? $lineDataCurrent->sum('plan_target') : 0;

            $summaryActual = 0;
            $summaryTarget = 0;
            $summaryMinsProd = 0;
            $summaryMinsAvail = 0;

            // Hitung elapsedJam: jam kerja yang sudah berjalan real-time (skip jam istirahat 12-13)
            // Mengikuti rumus SQL: CASE WHEN HOUR(NOW())=7 THEN 0 WHEN HOUR(NOW())>=13 THEN HOUR(NOW())-8 ELSE HOUR(NOW())-7 END
            if ($date == date('Y-m-d')) {
                $jamNow = (int) date('H');
                if ($jamNow <= 7) {
                    $elapsedJam = 0;
                } elseif ($jamNow >= 13) {
                    $elapsedJam = $jamNow - 8;
                } else {
                    $elapsedJam = $jamNow - 7;
                }
            } elseif ($date < date('Y-m-d')) {
                // Tanggal sudah lewat -> seluruh jam kerja dianggap selesai
                $elapsedJam = $jamKerja;
            } else {
                // Tanggal di masa depan -> belum mulai
                $elapsedJam = 0;
            }

            // Realtime mins avail (tidak berubah)
            if(strtotime(date('Y-m-d H:i:s')) <= strtotime($date.' 13:00:00')) {
                $minsAvailNow = count($lineData) > 0 ? $lineDataCurrent->max('man_power') * floor(strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60 : 0;
                $targetNow = count($lineDataTarget)  > 0 && $lineDataTargetCurrent->avg('smv') > 0 ? floor($lineDataTargetCurrent->max('man_power') * (floor(strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60) / $lineDataTargetCurrent->avg('smv')) : 0;
            } else {
                $minsAvailNow = count($lineData) > 0 ? $lineDataCurrent->max('man_power') * floor(((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60)-60) : 0;
                $targetNow = count($lineDataTarget)  > 0 && $lineDataTargetCurrent->avg('smv') > 0 ? floor($lineDataTargetCurrent->max('man_power') * floor(((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60)-60) / $lineDataTargetCurrent->avg('smv')) : 0;
            }
        @endphp
        @for ($i = 0; $i < count($hours); $i++)
            @php
                // Batas bawah eksklusif (mulai detik ke-1) supaya rentang jam tidak tumpang tindih.
                // Kalau batas bawah & atas sama-sama inklusif, output yang jatuh tepat di jam bulat
                // (mis. 09:00:00) terhitung 2x dan summary actual jadi lebih besar dari Report Output.
                if ($i == 0) {
                    $timeFrom = $date . ' 00:00:00';
                    $timeTo   = $date . ' ' . $hours[$i] . ':00';
                } elseif ($i == count($hours) - 1) {
                    $timeFrom = $date . ' ' . $hours[$i - 1] . ':00';
                    $timeTo   = $date . ' 23:59:59';
                } else {
                    $timeFrom = $date . ' ' . $hours[$i - 1] . ':00';
                    $timeTo   = $date . ' ' . $hours[$i] . ':00';
                }

                $jamKe = $i;
                $totalActual = 0;
                $totalRft = 0;
                $totalDefect = 0;
                $totalRework = 0;
                $totalReject = 0;
                $minsProd = 0;
                $minsAvail = 0;

                foreach ($lineData as $line) {
                    // $rft = $line->rfts->whereBetween('updated_at', [$timeFrom, $timeTo])->where('status', 'NORMAL');
                    $rft = $line->rfts->where('updated_at', '>=', $timeFrom)->where('updated_at', '<', $timeTo)->where('status', 'NORMAL');
                    $totalRft += $rft->count();
                    // $defect = $line->defects->whereBetween('updated_at', [$timeFrom, $timeTo])->where('defect_status', 'defect');
                    $defect = $line->defects->where('updated_at', '>=', $timeFrom)->where('updated_at', '<', $timeTo)->where('defect_status', 'defect');
                    $totalDefect += $defect->count();
                    // $rework = $line->defects->whereBetween('updated_at', [$timeFrom, $timeTo])->where('defect_status', 'reworked');
                    $rework = $line->defects->where('updated_at', '>=', $timeFrom)->where('updated_at', '<', $timeTo)->where('defect_status', 'reworked');
                    $totalRework += $rework->count();
                    // $reject = $line->rejects->whereBetween('updated_at', [$timeFrom, $timeTo]);
                    $reject = $line->rejects->where('updated_at', '>=', $timeFrom)->where('updated_at', '<', $timeTo);
                    $totalReject += $reject->count();
                    $totalActualThis = $rft->count() + $rework->count();
                    $totalActual += $totalActualThis;
                    $minsProd += $totalActualThis * $line->smv;
                    $minsAvail += ($line->tgl_plan == $date ? ($line->man_power) : 0) * ($line->tgl_plan == $date ? ($line->jam_kerja) : 0) * 60;
                }

                $summaryActual += $totalActual;
                $summaryMinsProd += $minsProd;
                $summaryTarget = $planTargetCurrent;

                if (strtotime(date('Y-m-d H:i:s')) >= strtotime($date.' 16:00:00')) {
                    $summaryMinsAvail = $minsAvail;
                    $summaryTarget = $planTargetCurrent;
                } else {
                    $summaryMinsAvail = $minsAvailNow;
                    $summaryTarget = $targetNow;
                }

                $cumulativeEfficiency = $summaryMinsAvail > 0 ? round((($summaryMinsProd/$summaryMinsAvail) * 100), 2) : 0 ;

                // ===== HOUR TARGET — mengikuti rumus SQL cari_datajam =====
                // target = FLOOR((plan_target - actual_sebelum) / (jam_kerja - MIN(jamKe, elapsedJam)))
                $actualSebelum = $summaryActual - $totalActual;
                $offset = min($jamKe, $elapsedJam);
                $sisaJam = $jamKerja - $offset;

                $hourTarget = $sisaJam > 0
                    ? floor(($planTarget - $actualSebelum) / $sisaJam)
                    : 0;
                    
            @endphp
            <tr wire:key="{{ $i }}" class="{{ $date == date('Y-m-d') ? (date('H', strtotime($hours[$jamKe]." -1 hour")) == date('H') ? 'active' : '') : ''}}">
                <td class="text-center">
                    {{ sprintf("%02d", (intval(substr($hours[$i],0,2))-1)).":00 - ".$hours[$i] }}
                </td>
                <td class="text-center fw-bold text-success">{{ num($totalRft) }}</td>
                <td class="text-center fw-bold text-defect">{{ num($totalDefect) }}</td>
                <td class="text-center fw-bold text-rework">{{ num($totalRework) }}</td>
                <td class="text-center fw-bold text-reject">{{ num($totalReject) }}</td>
                <td class="text-center fs-5 fw-bold text-sb">{{ num($totalActual) }}</td>
                <td class="text-center fs-5 fw-bold text-sb">
                    @if($i == count($hours)-1)
                        {{ 0 }}
                    @else
                        {{ $hourTarget > 0 ? num($hourTarget) : 0 }}
                    @endif
                </td>
                <td class="text-center fs-5 fw-bold text-sb">
                    {{ $hourTarget > 0 ? round(($totalActual/$hourTarget) * 100, 2) : 0 }} %
                </td>
            </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="fs-5 fw-bold text-center">Summary</td>
            <td class="fs-5 fw-bold text-center">{{ num($summaryActual) }}</td>
            <td class="fs-5 fw-bold text-center">{{ num($summaryTarget) }}</td>
            <td class="fs-5 fw-bold text-center">{{ num($cumulativeEfficiency) }} %</td>
        </tr>
    </tfoot>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center;">{{ $date }}</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;font-weight: 800;">{{ ucfirst(str_replace("_"," ",$selectedLine)) }} Top 5 Defects</th>
        </tr>
        <tr>
            <th style="text-align: center; font-weight: 800;">No.</th>
            <th colspan="2" style="font-weight: 800;">Defect Types</th>
            <th colspan="2" style="font-weight: 800;">Defect Areas</th>
        </tr>
    </thead>
    <tbody>
        @if ($defectTypes->count() < 1)
            <tr>
                <td colspan="5" style="text-align: center;">Data not found</td>
            </tr>
        @else
            @foreach ($defectTypes as $type)
                @php
                    $defectAreasFiltered = $defectAreas->where("defect_type_id", $type->defect_type_id)->take(5);
                    $firstDefectAreasFiltered = $defectAreasFiltered->first();
                    $typeRowspan = $defectAreasFiltered->count();
                @endphp
                <tr>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="vertical-align: middle;">
                        {{ $type->defectType->defect_type }}
                    </td>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} data-format="0" style="vertical-align: middle;font-weight: 800;">
                        {{$type->defect_type_count}}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ $defectAreasFiltered->first()->defectArea->defect_area }}
                    </td>
                    <td style="font-weight: 800;" data-format="0">
                        {{  $defectAreasFiltered->first()->defect_area_count }}
                    </td>
                </tr>
                @if ($defectAreasFiltered->count() > 1)
                    @foreach ($defectAreasFiltered as $area)
                        @if ($loop->index > 0)
                            <tr>
                                <td style="vertical-align: middle;">
                                    {{ $area->defectArea->defect_area }}
                                </td>
                                <td style="font-weight: 800;" data-format="0">
                                    {{  $area->defect_area_count }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

            @endforeach
        @endif
    </tbody>
</table>
