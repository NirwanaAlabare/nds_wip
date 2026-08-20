<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Labor Staff</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th rowspan="2">No Dept</th>
                <th rowspan="2">Dept Name</th>
                <th rowspan="2">No Sub Dept</th>
                <th rowspan="2">Sub Dept Name</th>
                <th rowspan="2">Group</th>
                @foreach ($dates as $date)
                    <th colspan="7">{{ $date['hari'] }} &nbsp; {{ $date['tanggal'] }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($dates as $date)
                    <th>No of MP</th>
                    <th>Working M</th>
                    <th>Wage</th>
                    <th>BPJS TK</th>
                    <th>BPJS KS</th>
                    <th>Accrual THR</th>
                    <th>Total</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['department_id'] }}</td>
                    <td>{{ $row['department_name'] }}</td>
                    <td>{{ $row['sub_dept_id'] }}</td>
                    <td>{{ $row['sub_dept_name'] }}</td>
                    <td>{{ $row['group_department'] }}</td>
                    @foreach (array_keys($dates) as $tanggal)
                        @php
                            $value = $row['values'][$tanggal] ?? null;
                        @endphp
                        <td>{{ $value ? $value['man_power'] : 0 }}</td>
                        <td>{{ $value ? $value['absen_menit'] : 0 }}</td>
                        <td>{{ $value ? $value['wage'] : 0 }}</td>
                        <td>{{ $value ? $value['bpjs_tk'] : 0 }}</td>
                        <td>{{ $value ? $value['bpjs_ks'] : 0 }}</td>
                        <td>{{ $value ? $value['thr'] : 0 }}</td>
                        <td>{{ $value ? $value['total'] : 0 }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + 7 * count($dates) }}">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
