<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th class="text-center align-middle" scope="col">PO Number</th>
                <th class="text-center align-middle" scope="col">Carton Number</th>
                <th class="text-center align-middle" scope="col">SKU</th>
                <th class="text-center align-middle" scope="col">Short Description</th>
                <th class="text-center align-middle" scope="col">Size</th>
                <th class="text-center align-middle" scope="col">Style</th>
                <th class="text-center align-middle" scope="col">Color</th>
                <th class="text-center align-middle" scope="col">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->po }}</td>
                    <td>{{ $row->no_carton }}</td>
                    <td>{{ $row->sku }}</td>
                    <td>{{ $row->short_desc }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->reff_no }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
