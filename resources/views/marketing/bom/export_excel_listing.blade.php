<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center;">LAPORAN DATA BOM MARKETING DETAIL ITEM</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Buyer</th>
            <th>Style</th>
            <th>Category</th>
            <th>Market</th>
            <th>Content</th>
            <th>Item Description</th>
            <th>Color</th>
            <th>Size</th>
            <th>Cons / Qty</th>
            <th>Currency</th>
            <th>Unit</th>
            <th>Shell</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $row->buyer }}</td>
            <td>{{ $row->style }}</td>
            <td>{{ $row->category }}</td>
            <td>{{ $row->market }}</td>
            <td>{{ $row->content_name }}</td>
            <td>{{ $row->item_name }}</td>
            <td>{{ $row->color_name }}</td>
            <td>{{ $row->size_name }}</td>
            <td style="text-align: right;">{{ (float) $row->qty }}</td>
            <td>{{ $row->currency }}</td>
            <td>{{ $row->unit_name }}</td>
            <td>{{ $row->shell }}</td>
            <td>{{ $row->price }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
