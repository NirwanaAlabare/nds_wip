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
        <th>Tanggal</th>
        <th>Department</th>
        <th>Line</th>
        <th>Worksheet</th>
        <th>Buyer</th>
        <th>Style</th>
        <th>Color</th>
        <th>Size</th>
        <th>Defect Name</th>
        <th>Qty</th>
    </tr>
    @foreach ($rejectData as $reject)
        <tr>
            <td>{{ $reject->tanggal }}</td>
            <td>{{ $reject->output_type }}</td>
            <td>{{ $reject->sewing_line }}</td>
            <td>{{ $reject->no_ws }}</td>
            <td>{{ $reject->buyer }}</td>
            <td>{{ $reject->style }}</td>
            <td>{{ $reject->color }}</td>
            <td>{{ $reject->size }}</td>
            <td>{{ $reject->defect_types_check }}</td>
            <td>{{ $reject->qty }}</td>
        </tr>
    @endforeach
</table>
