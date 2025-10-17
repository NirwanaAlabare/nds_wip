<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Profit Line</title>
</head>

<body>
    <table>
        <thead>
            @php
                $colCount = count($fixHeaders) + 1; // +1 for the "Line" column
            @endphp

            <tr>
                <th colspan="{{ $colCount }}">LAPORAN PROFIT LINE</th>
            </tr>
            <tr>
                <th class="text-center align-middle" style="color: black;">Line</th>
                @foreach ($fixHeaders as $tanggal => $fix)
                    <th>{{ $fix }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($pivotData as $row)
                <tr>
                    <td>{{ $row['sewing_line'] }}</td>
                    @foreach (array_keys($fixHeaders) as $tanggal)
                        <td class="text-end" style="{{ ($row[$tanggal] ?? 0) < 0 ? 'color: red;' : '' }}">
                            {{ $row[$tanggal] ?? 0 }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
