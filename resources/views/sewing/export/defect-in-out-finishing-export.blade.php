<table>
    <tr>
        <td colspan="15" style="text-align: center;">{{ $dateFrom }} s/d {{ $dateTo }}</td>
    </tr>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: 800;">OUTPUT DEFECT FINISHING</td>
    </tr>
    <tr>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Line</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">NIK</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Leader</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">WS Number</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Style</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Output Line</th>
        <th colspan="3" style="vertical-align: middle; text-align: center; font-weight: 800;">Output</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Persentase</th>
        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: 800;">Last Input</th>
    </tr>
    <tr>
        <th style="text-align: center; font-weight: 800;">Defect</th>
        <th style="text-align: center; font-weight: 800;">Reject</th>
        <th style="text-align: center; font-weight: 800;">Rework</th>
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
        @php
            $currentLine = '';
            $currentRowSpan = 0;
            $currentLastInput = 0;

            $lastInput = 0;
            $summaryActual = 0;
            $summaryDefectIn = 0;
            $summaryReworkOut = 0;
            $summaryRejected = 0;
        @endphp
        @if ($lines->count() < 1)
            <tr>
                <td style="text-align: center;" colspan="14">
                    Data not found
                </td>
            </tr>
        @else
            @foreach ($lines as $line)
                @php
                    $currentRowSpan = $lines->where("username", $line->username)->count();
                    $currentLastInput = $lines->where("username", $line->username)->max("latest_output");

                    $defectInOutRate = $line->total_actual > 0 ? round((($line->defect_in+$line->rework_out+$line->rejected)/$line->total_actual * 100), 2) : '0';

                    $lastInput < $currentLastInput && $lastInput = $currentLastInput;
                    $summaryActual += $line->total_actual;
                    $summaryDefectIn += $line->defect_in;
                    $summaryReworkOut += $line->rework_out;
                    $summaryRejected += $line->rejected;
                @endphp
                <tr wire:key="{{ $loop->index }}">
                    @if ($currentLine != $line->username)
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
                            <a style="font-weight: bold;" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $line->username }}" target="_blank">
                                {{ ucfirst(str_replace("_", " ", $line->username)) }}
                            </a>
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;" >{{ $line->leader_nik }}</td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;" >{{ $line->leader_name }}</td>
                    @endif
                    <td>{{ $line->kpno }}</td>
                    <td>{{ $line->styleno }}</td>
                    <td style="text-align: center;">
                        {{ $line->total_actual }}
                    </td>
                    <td style="text-align: center;">
                        {{ $line->defect_in }}
                    </td>
                    <td style="text-align: center;">
                        {{ $line->rejected }}
                    </td>
                    <td style="text-align: center;">
                        {{ $line->rework_out }}
                    </td>
                    <td style="text-align: center; font-weight: bold; {{ $defectInOutRate < 97 ? 'color: #f51818;' : 'color: #018003;' }}">
                        {{ $defectInOutRate }} %
                    </td>
                    <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
                        {{ $currentLastInput }}
                    </td>
                </tr>
                @php
                    if ($currentLine != $line->username) {
                        $currentLine = $line->username;
                    }
                @endphp
            @endforeach
        @endif
        @php
            $summaryDefectInOutRate = $summaryActual > 0 ? round(($summaryDefectIn+$summaryReworkOut+$summaryRejected)/$summaryActual*100, 2) : 0;
        @endphp
        <tr>
            <th colspan="5" style="font-weight: bold;text-align: center;">Summary</th>
            <th style="font-weight: bold;text-align: center;">{{ $summaryActual }}</th>
            <th style="font-weight: bold;text-align: center;">{{ $summaryDefectIn }}</th>
            <th style="font-weight: bold;text-align: center;">{{ $summaryRejected }}</th>
            <th style="font-weight: bold;text-align: center;">{{ $summaryReworkOut }}</th>
            <th style="font-weight: bold;text-align: center; {{ $summaryDefectInOutRate < 85 ? 'color: #f51818;' : 'color: #018003;' }}">{{ $summaryDefectInOutRate }} %</th>
            <td style="font-weight: bold;text-align: center;">{{ $lastInput }}</td>
        </tr>
    @endif
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
