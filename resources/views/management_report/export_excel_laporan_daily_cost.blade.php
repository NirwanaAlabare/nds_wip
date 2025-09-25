<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN DAILY COST</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>

            <tr>
                <th class="text-center align-middle" style="color: black;">No. COA</th>
                <th class="text-center align-middle" style="color: black;">Klasifikasi</th>
                <th class="text-center align-middle" style="color: black;">Projection</th>
                <th class="text-center align-middle" style="color: black;">Daily Cost</th>
                @foreach ($tanggalList as $tanggal)
                    <th class="text-center align-middle" style="white-space: nowrap;">
                        {{ $tanggal->translatedFormat('j M Y') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedData as $coa)
                <tr>
                    <td>{{ $coa['no_coa'] }}</td>
                    <td>{{ $coa['nama_coa'] }}</td>
                    <td class="text-end">{{ number_format($coa['projection'], 2) }}</td>
                    <td class="text-end">{{ number_format($coa['daily_cost'], 2) }}</td>
                    @foreach ($tanggalList as $tgl)
                        @php
                            $key = $tgl->format('Y-m-d');
                            $value = $coa['totals_by_date'][$key] ?? 0;
                        @endphp
                        <td class="text-end">{{ number_format($value, 2) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
