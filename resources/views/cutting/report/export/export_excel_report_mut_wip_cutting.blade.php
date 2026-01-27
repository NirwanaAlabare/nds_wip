<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="11"> Report Mutasi WIP Cutting</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle">Worksheet</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Panel</th>
                <th class="text-center align-middle">Saldo Awal</th>
                <th class="text-center align-middle">In</th>
                <th class="text-center align-middle">Replacement</th>
                <th class="text-center align-middle">Out</th>
                <th class="text-center align-middle">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->panel }}</td>
                    <td class="text-end">{{ $row->saldo_awal }}</td>
                    <td class="text-end">{{ $row->qty_cut }}</td>
                    <td class="text-end">{{ $row->qty_replace }}</td>
                    <td class="text-end">{{ $row->qty_dc }}</td>
                    <td class="text-end">{{ $row->saldo_akhir }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
