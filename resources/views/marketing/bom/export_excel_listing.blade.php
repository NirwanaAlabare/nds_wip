<table>
    <thead>
        <tr>
            <th colspan="15" style="text-align: center; font-weight: bold;">LAPORAN DATA BOM MARKETING DETAIL ITEM</th>
        </tr>
        <tr>
            <th style="font-weight: bold;">No</th>
            <th style="font-weight: bold;">Category</th>
            <th style="font-weight: bold;">Id Content</th>
            <th style="font-weight: bold;">Content</th>
            <th style="font-weight: bold;">Item Description</th>
            <th style="font-weight: bold;">Color</th>
            <th style="font-weight: bold;">Size</th>
            <th style="font-weight: bold;">Cons / Qty</th>
            <th style="font-weight: bold;">Unit</th>
            <th style="font-weight: bold;">Shell</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $row->category }}</td>
            <td>{{ $row->id_content }}</td>
            <td>{{ $row->content_name }}</td>
            <td>{{ $row->item_name }}</td>
            <td>{{ $row->color_name }}</td>
            <td>{{ $row->size_name }}</td>
            <td style="text-align: right;">{{ (float) $row->qty }}</td>
            <td>{{ $row->currency }}</td>
            <td>{{ $row->nama_panel }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
