<table>
    <tr>
        <td>Dari : {{ $dateFrom }}</td>
        <td>Sampai : {{ $dateTo }}</td>
    </tr>
    <tr>
        <td>Filter Defect Type : {{ $defect_types }}</td>
        <td>Filter WS : {{ ($base_ws ? $base_ws : '-') }}</td>
        <td>Sub Filter WS : {{ ($ws ? $ws : '-') }}</td>
        <td>Filter Dept : {{ ($department ? $department : '-') }}</td>
        <td>Filter Quality : {{ ($defect_status ? $defect_status : '-') }}</td>
        <td>Filter Line : {{ ($sewing_line ? $sewing_line : '-') }}</td>
        <td>Filter Buyer : {{ ($buyer ? $buyer : '-') }}</td>
        <td>Filter Style : {{ ($style ? $style : '-') }}</td>
        <td>Filter Color : {{ ($color ? $color : '-') }}</td>
        <td>Filter Size : {{ ($size ? $size : '-') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="border: 1px solid black; font-weight: 800">Tanggal</th>
        <th style="border: 1px solid black; font-weight: 800">Department</th>
        <th style="border: 1px solid black; font-weight: 800">Line</th>
        <th style="border: 1px solid black; font-weight: 800">Worksheet</th>
        <th style="border: 1px solid black; font-weight: 800">Buyer</th>
        <th style="border: 1px solid black; font-weight: 800">Style</th>
        <th style="border: 1px solid black; font-weight: 800">Color</th>
        <th style="border: 1px solid black; font-weight: 800">Size</th>
        <th style="border: 1px solid black; font-weight: 800">Defect Name</th>
        <th style="border: 1px solid black; font-weight: 800">Qty</th>
    </tr>
    @php
        $totalReject = 0;
    @endphp
    @foreach ($rejectData as $reject)
        <tr>
            <td style="border: 1px solid black;">{{ $reject->tanggal }}</td>
            <td style="border: 1px solid black;">{{ $reject->output_type }}</td>
            <td style="border: 1px solid black;">{{ $reject->sewing_line }}</td>
            <td style="border: 1px solid black;">{{ $reject->no_ws }}</td>
            <td style="border: 1px solid black;">{{ $reject->buyer }}</td>
            <td style="border: 1px solid black;">{{ $reject->style }}</td>
            <td style="border: 1px solid black;">{{ $reject->color }}</td>
            <td style="border: 1px solid black;">{{ $reject->size }}</td>
            <td style="border: 1px solid black;">{{ $reject->defect_types_check }}</td>
            <td style="border: 1px solid black;">{{ $reject->total_reject }}</td>
        </tr>
        @php
            $totalReject += $reject->total_reject;
        @endphp
    @endforeach
    <tr>
        <td style="border: 1px solid black; font-weight: 800" colspan="9">Total</td>
        <td style="border: 1px solid black; font-weight: 800">{{ $totalReject }}</td>
    </tr>
</table>
