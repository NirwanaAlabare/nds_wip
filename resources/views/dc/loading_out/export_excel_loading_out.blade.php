<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="14"> Report Loading Out</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle">No. Form</th>
                <th class="text-center align-middle">Tgl. Form</th>
                <th class="text-center align-middle">PO</th>
                <th class="text-center align-middle">Item</th>
                <th class="text-center align-middle">Supplier</th>
                <th class="text-center align-middle">Jenis Dok</th>
                <th class="text-center align-middle">Jenis Pengeluaran</th>
                <th class="text-center align-middle">No. Karung</th>
                <th class="text-center align-middle">No. Stocker</th>
                <th class="text-center align-middle">WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->no_form }}</td>
                    <td>{{ $row->tgl_form_fix }}</td>
                    <td>{{ $row->pono }}</td>
                    <td>{{ $row->itemdesc }}</td>
                    <td>{{ $row->supplier }}</td>
                    <td>{{ $row->jns_dok }}</td>
                    <td>{{ $row->jns_pengeluaran }}</td>
                    <td>{{ $row->no_karung }}</td>
                    <td>{{ $row->id_qr_stocker }}</td>
                    <td>{{ $row->kpno }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td class="text-end">{{ $row->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
