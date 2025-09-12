<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 30mm 20mm;
            margin: 0;
        }

        body {
            margin: 1;
            padding: 0;
            font-size: 5.5px;
            /* Smaller font */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid black;
        }

        td {
            border: 1px solid black;
            padding: 0.3mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label {
            font-weight: bold;
            width: 25%;
            height: 2.3mm;
            font-size: 5px;
        }

        .value {
            font-weight: bold;
            width: 25%;
            height: 2.3mm;
            font-size: 5px;
            text-align: center;
        }

        .rotated-wrapper {
            transform: rotate(90deg);
            transform-origin: left top;
            display: inline-block;
            white-space: nowrap;
        }

        .barcode-img {
            width: 10mm;
            /* Adjust as needed */
            height: auto;
            display: block;
        }

        .barcode-text {
            font-size: 5px;
            text-align: center;
            margin-top: 1mm;
        }
    </style>

</head>

<body>
    @foreach ($data_header as $index => $dh)
        <div style="@if ($index !== count($data_header) - 1) page-break-after: always; @endif">
            <table>
                <tr>
                    <td class="label">Lot :</td>
                    <td class="value">{{ $dh->no_lot ?? 'null' }}</td>
                    <td class="value" rowspan="6" style="text-align: center;">
                        <img src="data:image/png;base64,{{ $dh->barcode_base64 }}" alt="Barcode"
                            style="width: 200%; height: 20px; transform: rotate(90deg);">
                    </td>
                </tr>
                <tr>
                    <td class="label">No. Roll :</td>
                    <td class="value">{{ $dh->no_roll_buyer ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="label">Qty :</td>
                    <td class="value">{{ $dh->qty_aktual ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="label">Unit :</td>
                    <td class="value">{{ $dh->satuan ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="label">ID Item :</td>
                    <td class="value">{{ $dh->id_item ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="label">Barcode :</td>
                    <td class="value">{{ $dh->no_barcode ?? 'null' }}</td>
                </tr>
            </table>
        </div>
    @endforeach
</body>


</html>
