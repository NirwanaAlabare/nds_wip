<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="11"> Report GR Set</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">No. Form</th>
                <th class="text-center align-middle">Panel</th>
                <th class="text-center align-middle">WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Barcode</th>
                <th class="text-center align-middle">ID Item</th>
                <th class="text-center align-middle">Item</th>
                <th class="text-center align-middle">Qty</th>
                <th class="text-center align-middle">Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->tanggal_fix }}</td>
                    <td>{{ $row->no_form }}</td>
                    <td>{{ $row->panel }}</td>
                    <td>{{ $row->act_costing_ws }}</td>
                    <td>{{ $row->style }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->barcode }}</td>
                    <td>{{ $row->id_item }}</td>
                    <td>{{ $row->itemdesc }}</td>
                    <td class="text-end">{{ $row->qty_pakai }}</td>
                    <td class="text-end">{{ $row->satuan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
