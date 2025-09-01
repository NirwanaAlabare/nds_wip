<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 40mm 30mm;
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
            height: 5mm;
            font-size: 8px;
        }

        .value {
            font-weight: bold;
            width: 25%;
            height: 5mm;
            font-size: 8px;
            text-align: center;
        }
    </style>

</head>

<body>

    <table>
        @foreach ($data_header as $dh)
            <tr>
                <td class="label">ID Item</td>
                <td class="value">{{ $dh->id_item ?? 'null' }}</td>
                <td class="label">Barcode</td>
                <td class="value">{{ $dh->no_barcode ?? 'null' }}</td>
            </tr>
            <tr>
                <td colspan="4" style="height: 22mm;font-weight: bold;text-align: center;font-size: 24px;">
                    {{ $dh->result_sticker ?? 'null' }}</td>
            </tr>
        @endforeach
    </table>

</body>

</html>
