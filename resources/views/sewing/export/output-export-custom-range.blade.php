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
        <td colspan="15" style="text-align: center; font-weight: 800;">OUTPUT {{ $subtitle }} {{ $search }}</td>
    </tr>
    <tr>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Line</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">NIK</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Leader</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">WS Number</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Style</th>
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
        $currentLine = '';
        $currentRowSpan = 0;

        $currentActual = 0;
        $currentMinsProd = 0;
        $currentTarget = 0;
        $currentMinsAvail = 0;
        $currentLastInput = 0;

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
                $currentRowSpan = $lines->where("username", $line->username)->count();

                $rateRft = $line->total_output > 0 ? round(($line->rft/$line->total_output * 100), 2) : '0';
                $rateDefect = $line->total_output > 0 ? round((($line->defect+$line->rework)/$line->total_output * 100), 2) : '0';
                $rateReject = $line->total_output > 0 ? round((($line->reject)/$line->total_output * 100), 2) : '0';
            @endphp
            <tr wire:key="{{ $loop->index }}">
                @if ($currentLine != $line->username)
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
                        {{ ucfirst(str_replace("_", " ", $line->username)) }}
                    </td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;" >{{ $line->leader_nik }}</td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;" >{{ $line->leader_name }}</td>
                @endif
                <td>{{ $line->kpno }}</td>
                <td>{{ $line->styleno }}</td>
                <td style="text-align: center; font-weight: 800;" data-format="0">
                    {{ $line->rft < 1 ? '0' : $line->rft }}
                </td>
                <td style="text-align: center; font-weight: 800;" data-format="0">
                    {{ $line->defect < 1 ? '0' : $line->defect }}
                </td>
                <td style="text-align: center; font-weight: 800;" data-format="0">
                    {{ $line->rework < 1 ? '0' : $line->rework }}
                </td>
                <td style="text-align: center; font-weight: 800;" data-format="0">
                    {{ $line->reject < 1 ? '0' : $line->reject }}
                </td>
                <td style="text-align: center; font-weight: 800;" data-format="0">
                    {{ $line->total_actual < 1 ? '0' : $line->total_actual }}
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
                @if ($currentLine != $line->username)
                    @php
                        if ((date('Y-m-d H:i:s') >= $dateFrom.' 16:00:00')) {
                            $cumulativeTarget = $lines->where("username", $line->username)->sum("total_target") ?? 0;
                            $cumulativeMinsAvail = $lines->where("username", $line->username)->sum("mins_avail") ?? 0;
                        } else {
                            $cumulativeTarget = $lines->where("username", $line->username)->max('cumulative_target') ?? 0;
                            $cumulativeMinsAvail = $lines->where("username", $line->username)->max('cumulative_mins_avail') ?? 0;
                        }

                        $currentActual = $lines->where("username", $line->username)->sum("total_actual") ?? 0;
                        $currentMinsProd = $lines->where("username", $line->username)->sum("mins_prod") ?? 0;
                        $currentTarget = $cumulativeTarget;
                        $currentMinsAvail = $cumulativeMinsAvail;
                        $currentLastInput = $lines->where("username", $line->username)->max("latest_output") ?? date("Y-m-d")." 00:00:00";

                        $currentEfficiency = ($currentMinsAvail  > 0 ? round(($currentMinsProd/$currentMinsAvail)*100, 2) : 0);

                        $summaryActual += $currentActual;
                        $summaryMinsProd += $currentMinsProd;
                        $summaryTarget += $currentTarget;
                        $summaryMinsAvail += $currentMinsAvail;
                        $lastInput < $currentLastInput && $lastInput = $currentLastInput;
                    @endphp
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0">
                        {{ $currentActual }}
                    </td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0">
                        {{ $currentTarget }}
                    </td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; font-weight: 800; vertical-align: middle;" data-format="0%">
                        {{ $currentEfficiency }} %
                    </td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; font-weight: 800; vertical-align: middle;">
                        {{ $currentLastInput }}
                    </td>
                @endif
            </tr>
            @php
                if ($currentLine != $line->username) {
                    $currentLine = $line->username;
                }
            @endphp
        @endforeach
    @endif
    <tfoot>
        @php
            $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
            $summaryEfficiencyNumber = $summaryMinsAvail > 0 ? ($summaryMinsProd/$summaryMinsAvail) : 0;
            $targetFromEfficiency = floor($summaryEfficiencyNumber > 0 ? $summaryActual / ($summaryEfficiencyNumber) : 0);
        @endphp
        <tr>
            <th colspan="13" style="text-align: center; font-weight:800;">Summary</th>
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
