<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100"> Report Mutasi Fabric</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle">Worksheet</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">ID Item</th>
                <th class="text-center align-middle">Item Name</th>
                <th class="text-center align-middle">Saldo Awal</th>
                <th class="text-center align-middle">Penerimaan</th>
                <th class="text-center align-middle">Pemakaian Cutting</th>
                <th class="text-center align-middle">Ganti Reject Set</th>
                <th class="text-center align-middle">Ganti Reject Panel</th>
                <th class="text-center align-middle">Retur</th>
                <th class="text-center align-middle">Short Roll</th>
                <th class="text-center align-middle">Saldo Akhir</th>
                <th class="text-center align-middle">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td class="text-end">{{ $row->id_item }}</td>
                    <td class="text-end">{{ $row->itemdesc }}</td>
                    <td class="text-end">{{ $row->qty_sawal }}</td>
                    <td class="text-end">{{ $row->qty_out }}</td>
                    <td class="text-end">{{ $row->qty_pakai }}</td>
                    <td class="text-end">{{ $row->qty_reject_set }}</td>
                    <td class="text-end">{{ $row->qty_reject_panel }}</td>
                    <td class="text-end">{{ $row->qty_retur }}</td>
                    <td class="text-end">{{ $row->short_roll }}</td>
                    <td class="text-end">{{ $row->saldo_akhir }}</td>
                    <td class="text-end">{{ $row->satuan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
