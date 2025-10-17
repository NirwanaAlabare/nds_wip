<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN DAILY EARN PER BUYER</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>

            <tr>
                <th class="text-center align-middle" style="color: black;">Date</th>
                <th class="text-center align-middle" style="color: black;">Total Balance</th>
                <th class="text-center align-middle" style="color: black;">Day Off (Without Output)</th>
                @php
                    // Get all possible buyer keys from pivotData rows (exclude known fields)
                    $allBuyers = collect($pivotData)
                        ->flatMap(function ($row) {
                            return collect($row)
                                ->except(['tanggal', 'tanggal_fix', 'blc_full_earning', 'day_without_output'])
                                ->keys();
                        })
                        ->unique();

                    // Filter buyers that have at least one non-zero/non-null value in any row
                    $buyers = $allBuyers
                        ->filter(function ($buyer) use ($pivotData) {
                            foreach ($pivotData as $row) {
                                if (isset($row[$buyer]) && $row[$buyer]) {
                                    // value exists and is truthy (not 0/null)
                                    return true;
                                }
                            }
                            return false;
                        })
                        ->sort()
                        ->values();
                @endphp

                @foreach ($buyers as $buyer)
                    <th>{{ $buyer }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($pivotData as $row)
                <tr>
                    <td>{{ $row['tanggal_fix'] }}</td>

                    {{-- Column: blc_full_earning --}}
                    <td class="text-end" style="{{ $row['blc_full_earning'] < 0 ? 'color: red;' : '' }}">
                        {{ $row['blc_full_earning'] }}
                    </td>

                    {{-- Column: day_without_output --}}
                    <td class="text-end" style="{{ $row['day_without_output'] < 0 ? 'color: red;' : '' }}">
                        {{ $row['day_without_output'] }}
                    </td>

                    {{-- Dynamic buyer columns --}}
                    @foreach ($buyers as $buyer)
                        @php
                            $value = $row[$buyer] ?? 0;
                        @endphp
                        <td class="text-end" style="{{ $value < 0 ? 'color: red;' : '' }}">
                            {{ $value }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>

    </table>
</body>

</html>
