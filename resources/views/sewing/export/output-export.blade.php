<table>
    @php
        $subtitle = "";
        switch ($subtype) {
            case "" :
                $subtitle = "END-LINE";
                break;
            case "_finish" :
                $subtitle = "FINISH-LINE";
                break;
            case "_packing" :
                $subtitle = "PACKING-LINE";
                break;
            default :
                $subtitle = "END-LINE";
                break;
        }
    @endphp
    <tr>
        <td colspan="15" style="text-align: center;">{{ $date }}</td>
    </tr>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: 800;">OUTPUT {{ $subtitle }}</td>
    </tr>
    <tr>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Line</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">WS Number</th>
        <th colspan="5" style="vertical-align: middle; text-align: center; font-weight: 800;">Output</th>
        <th colspan="3" style="vertical-align: middle; text-align: center; font-weight: 800;">Rate</th>
        <th colspan="3" style="vertical-align: middle; text-align: center; font-weight: 800;">Total</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Last Input</th>
    </tr>
    <tr>
        <th style="text-align: center; font-weight: 800;">RFT</th>
        <th style="text-align: center; font-weight: 800;">Defect</th>
        <th style="text-align: center; font-weight: 800;">Rework</th>
        <th style="text-align: center; font-weight: 800;">Reject</th>
        <th style="text-align: center; font-weight: 800;">Actual</th>
        <th style="text-align: center; font-weight: 800;">RFT</th>
        <th style="text-align: center; font-weight: 800;">Defect</th>
        <th style="text-align: center; font-weight: 800;">Reject</th>
        <th style="text-align: center; font-weight: 800;">Actual</th>
        <th style="text-align: center; font-weight: 800;">Target</th>
        <th style="text-align: center; font-weight: 800;">Efficiency</th>
    </tr>
    @php
        $summaryActual = 0;
        $summaryMinsProd = 0;
        $summaryTarget = 0;
        $summaryMinsAvail = 0;
        $lastInput = 0;
    @endphp
    @if ($lines->count() < 1)
        <tr>
            <td style="text-align: center;" colspan="14">
                Data not found
            </td>
        </tr>
    @else
        {{-- Total Line Loop --}}
        @foreach ($lines as $line)
            @php
                $lastInput < $line->latest_output && $lastInput = $line->latest_output;

                if (date('Y-m-d H:i:s') >= $line->tgl_plan.' 16:00:00') {
                    $cumulativeTarget = $line->total_target;
                    $cumulativeMinsAvail = $line->mins_avail;
                } else {
                    $cumulativeTarget = $line->cumulative_target;
                    $cumulativeMinsAvail = $line->cumulative_mins_avail;
                }

                $cumulativeEfficiency = $cumulativeMinsAvail > 0 ? round(($line->mins_prod/$cumulativeMinsAvail)*100, 2) : 0;

                $summaryActual += $line->total_actual;
                $summaryMinsProd += $line->mins_prod;
                $summaryTarget += $cumulativeTarget;
                $summaryMinsAvail += $cumulativeMinsAvail;

                $totalWS = $line->total_ws;
                $lineMasterPlans = $line->masterPlans->where('tgl_plan', $line->tgl_plan)->where('cancel', 'N')->groupBy('id_ws');
            @endphp
            <tr>
                <td rowspan="{{ $totalWS }}" style="text-align: center; vertical-align: middle;">{{ ucfirst(str_replace("_", " ", $line->username)) }}</td>
                {{-- Line Per Master Plan Loop --}}
                @foreach ($lineMasterPlans as $masterPlan)
                    @php
                        $rft = 0;
                        $defect = 0;
                        $rework = 0;
                        $reject = 0;
                        $actual = 0;
                        $output = 0;
                        $rateRft = 0;
                        $rateDefect = 0;
                        $rateReject = 0;

                        foreach ($masterPlan as $subMasterPlan) {
                            switch ($subtype) {
                                case "" :
                                    $rft += $subMasterPlan->rfts->where('status', 'NORMAL')->count() < 1 ? '0' : $subMasterPlan->rfts->where('status', 'NORMAL')->count();
                                    $defect += $subMasterPlan->defects->where('defect_status', 'defect')->count() < 1 ? '0' : $subMasterPlan->defects->where('defect_status', 'defect')->count();
                                    $rework += $subMasterPlan->defects->where('defect_status', 'reworked')->count() < 1 ? '0' : $subMasterPlan->defects->where('defect_status', 'reworked')->count();
                                    $reject += $subMasterPlan->rejects->count() < 1 ? '0' : $subMasterPlan->rejects->count();
                                    break;
                                case "_finish" :
                                    $rft += $subMasterPlan->rftsFinish->where('status', 'NORMAL')->count() < 1 ? '0' : $subMasterPlan->rftsFinish->where('status', 'NORMAL')->count();
                                    $defect += $subMasterPlan->defectsFinish->where('defect_status', 'defect')->count() < 1 ? '0' : $subMasterPlan->defectsFinish->where('defect_status', 'defect')->count();
                                    $rework += $subMasterPlan->defectsFinish->where('defect_status', 'reworked')->count() < 1 ? '0' : $subMasterPlan->defectsFinish->where('defect_status', 'reworked')->count();
                                    $reject += $subMasterPlan->rejectsFinish->count() < 1 ? '0' : $subMasterPlan->rejectsFinish->count();
                                    break;
                                case "_packing" :
                                    $rft += $subMasterPlan->rftsPacking->where('status', 'NORMAL')->count() < 1 ? '0' : $subMasterPlan->rftsPacking->where('status', 'NORMAL')->count();
                                    $defect += $subMasterPlan->defectsPacking->where('defect_status', 'defect')->count() < 1 ? '0' : $subMasterPlan->defectsPacking->where('defect_status', 'defect')->count();
                                    $rework += $subMasterPlan->defectsPacking->where('defect_status', 'reworked')->count() < 1 ? '0' : $subMasterPlan->defectsPacking->where('defect_status', 'reworked')->count();
                                    $reject += $subMasterPlan->rejectsPacking->count() < 1 ? '0' : $subMasterPlan->rejectsPacking->count();
                                    break;
                                default :
                                    $rft += $subMasterPlan->rfts->where('status', 'NORMAL')->count() < 1 ? '0' : $subMasterPlan->rfts->where('status', 'NORMAL')->count();
                                    $defect += $subMasterPlan->defects->where('defect_status', 'defect')->count() < 1 ? '0' : $subMasterPlan->defects->where('defect_status', 'defect')->count();
                                    $rework += $subMasterPlan->defects->where('defect_status', 'reworked')->count() < 1 ? '0' : $subMasterPlan->defects->where('defect_status', 'reworked')->count();
                                    $reject += $subMasterPlan->rejects->count() < 1 ? '0' : $subMasterPlan->rejects->count();
                                    break;
                            }
                        }
                        $actual = $rft + $rework;
                        $output = $rft + $defect + $rework + $reject;

                        $rateRft = $output > 0 ? round(($rft/$output * 100), 2) : '0';
                        $rateDefect = $output > 0 ? round((($defect+$rework)/$output * 100), 2) : '0';
                        $rateReject = $output > 0 ? round((($reject)/$output * 100), 2) : '0';
                    @endphp
                        {{-- Per Master Plan Data --}}
                        @if (!$loop->first)
                            <tr>
                        @endif

                        <td>{{ $masterPlan->first()->actCosting->kpno }}</td>
                        <td style="text-align: center; font-weight: 800;" data-format="0">
                            {{ $rft < 1 ? '0' : $rft }}
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0">
                            {{ $defect < 1 ? '0' : $defect }}
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0">
                            {{ $rework < 1 ? '0' : $rework }}
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0">
                            {{ $reject < 1 ? '0' : $reject }}
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0">
                            {{ $actual }}
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0%">
                            {{ $rateRft }} %
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0%">
                            {{ $rateDefect }} %
                        </td>
                        <td style="text-align: center; font-weight: 800;" data-format="0%">
                            {{ $rateReject }} %
                        </td>

                        @if (!$loop->first)
                            </tr>
                        @endif

                        {{-- Total Line Data --}}
                        @if ($loop->first)
                                <td rowspan="{{ $totalWS }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0">
                                    {{ $line->total_actual }}
                                </td>
                                <td rowspan="{{ $totalWS }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0">
                                    {{ $cumulativeTarget }}
                                </td>
                                <td rowspan="{{ $totalWS }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0%">
                                    {{ $cumulativeEfficiency }} %
                                </td>
                                <td rowspan="{{ $totalWS }}" style="text-align: center; vertical-align: middle;">{{ $line->latest_output }}</td>
                            </tr>
                        @endif
                @endforeach
        @endforeach
    @endif
    <tfoot>
        @php
            $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
            $summaryEfficiencyNumber = $summaryMinsAvail > 0 ? ($summaryMinsProd/$summaryMinsAvail) : 0;
            $targetFromEfficiency = floor($summaryEfficiencyNumber > 0 ? $summaryActual / ($summaryEfficiencyNumber) : 0);
        @endphp
        <tr>
            <th colspan="10" style="text-align: center; font-weight:800;">Summary</th>
            <th data-format="0" style="text-align: center; font-weight:800;">{{ $summaryActual }}</th>
            <th data-format="0" style="text-align: center; font-weight:800;">{{ $targetFromEfficiency }}</th>
            <th data-format="0%" style="text-align: center; font-weight:800;">{{ $summaryEfficiency }} %</th>
            <td style="text-align: center;">{{ $lastInput }}</td>
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
            <th style="text-align: center;" colspan="2">Defect Types</th>
            <th style="text-align: center;" colspan="2">Defect Areas</th>
            <th style="text-align: center;" colspan="2">Line</th>
        </tr>
    </thead>
    <tbody>
        @if ($defectTypes->count() < 1)
            <tr>
                <td colspan="6" style="text-align: center;">Data not found</td>
            </tr>
        @else
            @foreach ($defectTypes as $type)
                @php
                    $defectAreasFiltered = $defectAreas->where("defect_type_id", $type->defect_type_id)->take(5);
                    $lineDefectsFilteredType = $lineDefects->where("defect_type_id", $type->defect_type_id);
                    $firstDefectAreasFiltered = $defectAreasFiltered->first();
                    $lineDefectsFilteredAreaFirstCol = $lineDefectsFilteredType->where('defect_area_id', $firstDefectAreasFiltered->defect_area_id)->sortByDesc('total')->take(5);
                    $firstLineDefectsFilteredArea = $lineDefectsFilteredAreaFirstCol->first();
                    $typeRowspan = 0;

                    foreach ($defectAreasFiltered as $area) {
                        $typeRowspan += $lineDefectsFilteredType->where('defect_area_id', $area->defect_area_id)->take(5)->count();
                    }
                @endphp
                <tr>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="vertical-align: middle;">
                        {{ $type->defect_type }}
                    </td>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                        <b>{{$type->defect_type_count}}</b>
                    </td>
                    <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} style="vertical-align: middle;">
                        <div>
                            {{ $defectAreasFiltered->first()->defect_area }}
                        </div>
                    </td>
                    <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                        <b>{{  $defectAreasFiltered->first()->defect_area_count }}</b>
                    </td>
                    <td>
                        {{ $firstLineDefectsFilteredArea->sewing_line }}
                    </td>
                    <td style="text-align: center;">
                        <b>{{ $firstLineDefectsFilteredArea->total }}</b>
                    </td>
                </tr>
                @if ($lineDefectsFilteredAreaFirstCol->count() > 1)
                    @foreach ($lineDefectsFilteredAreaFirstCol as $line)
                        @if ($loop->index > 0)
                            <tr>
                                <td>
                                    {{ $line->sewing_line }}
                                </td>
                                <td style="text-align: center;" data-format="0">
                                    <b>{{ $line->total }}</b>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                @if ($defectAreasFiltered->count() > 1)
                    @foreach ($defectAreasFiltered as $area)
                        @if ($loop->index > 0)
                            @php
                                $lineDefectAreasFilteredNextCol = $lineDefectsFilteredType->where("defect_area_id", $area->defect_area_id)->sortByDesc('total')->take(5);
                            @endphp
                            <tr>
                                <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} style="vertical-align: middle;">
                                    {{ $area->defect_area }}
                                </td>
                                <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                                    <b>{{  $area->defect_area_count }}</b>
                                </td>
                                <td>
                                    {{ $lineDefectAreasFilteredNextCol->first()->sewing_line }}
                                </td>
                                <td style="text-align: center;" data-format="0">
                                    <b>{{  $lineDefectAreasFilteredNextCol->first()->total }}</b>
                                </td>
                            </tr>

                            @if ($lineDefectAreasFilteredNextCol->count() > 1)
                                @foreach ($lineDefectAreasFilteredNextCol as $line)
                                    @if ($loop->index > 0)
                                        <tr>
                                            <td>
                                                {{ $line->sewing_line }}
                                            </td>
                                            <td style="text-align: center;" data-format="0">
                                                <b>{{  $line->total }}</b>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @endif

            @endforeach
        @endif
    </tbody>
</table>
