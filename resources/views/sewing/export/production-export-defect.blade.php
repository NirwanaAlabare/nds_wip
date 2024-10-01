<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center;">{{ $date }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;font-weight: 800;">{{ ucfirst(str_replace("_"," ",$selectedLine)) }} Defects</th>
        </tr>
        <tr>
            <th style="text-align: center; font-weight: 800;">No.</th>
            <th style="font-weight: 800;">Waktu</th>
            <th style="font-weight: 800;">Kode Numbering</th>
            <th style="font-weight: 800;">Tipe Defect</th>
            <th style="font-weight: 800;">Area Defect</th>
            <th style="font-weight: 800;">Status</th>
        </tr>
    </thead>
    <tbody>
        @if ($defects->count() < 1)
            <tr>
                <td colspan="5" style="text-align: center;">Data not found</td>
            </tr>
        @else
            @foreach ($defects as $defect)
                <tr>
                    <td style="vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td style="vertical-align: middle;">
                        {{ $defect->waktu }}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ $defect->kode_numbering ? $defect->kode_numbering : "MANUAL" }}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ $defect->defect_type }}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ $defect->defect_area}}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ $defect->defect_status }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center;">{{ $date }}</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;font-weight: 800;">{{ ucfirst(str_replace("_"," ",$selectedLine)) }} Defects</th>
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
