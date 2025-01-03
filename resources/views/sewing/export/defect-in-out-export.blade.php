<table>
    <tr>
        <td style="text-align: center;">Dari : {{ $dateFrom }}</td>
        <td style="text-align: center;">Sampai : {{ $dateTo }}</td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center; font-weight: 800;">{{ strtoupper(str_replace("_", "", $type)) }}</td>
    </tr>
    <tr>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">DATE IN</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">LINE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">DEPARTMENT</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">WS</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">STYLE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">COLOR</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">SIZE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">TYPE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">RATE</th>
    </tr>
    @php
        $summaryDefectQty = $defectInOutList->sum("defect_qty");
    @endphp
    @if ($defectInOutList->count() < 1)
        <tr>
            <td style="text-align: center;" colspan="15">
                Data not found
            </td>
        </tr>
    @else
        @foreach ($defectInOutList as $defect)
            <tr>
                <td>{{ $defect->updated_at }}</td>
                <td>{{ $defect->FullName }}</td>
                <td>{{ strtoupper($defect->output_type) }}</td>
                <td>{{ $defect->kpno }}</td>
                <td>{{ $defect->styleno }}</td>
                <td>{{ $defect->color }}</td>
                <td>{{ $defect->size }}</td>
                <td>{{ $defect->defect_type }}</td>
                <td>{{ $defect->defect_qty }}</td>
                <td>{{ round($defect->defect_qty/($summaryDefectQty > 0 ? $summaryDefectQty : 1) * 100, 2) }} %</td>
            </tr>
        @endforeach
    @endif
    <tr>
        <td colspan="8" class="fs-5 fw-bold text-center">Summary</td>
        <td class="fs-5 fw-bold text-center">{{ num($summaryDefectQty) }}</td>
        <td class="fs-5 fw-bold text-center"></td>
    </tr>
</table>
