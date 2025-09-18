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
            width: 100%;
            height: 3.9mm;
            font-size: 10px;
            text-align: center;
        }

        .header {
            font-weight: bold;
            width: 100%;
            height: 4.5mm;
            font-size: 10px;
            text-align: center;
        }

        .value {
            font-weight: bold;
            width: 100%;
            height: 10mm;
            font-size: 55px;
            text-align: center;
        }
    </style>

</head>

<body>
    @foreach ($data_header as $index => $dh)
        <div style="@if ($index !== count($data_header) - 1) page-break-after: always; @endif">
            <table>
                <tr>
                    <td class="header">GROUP</td>
                </tr>
                <tr>
                    <td class="value">{{ $dh->group ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="header">{{ $dh->barcode ?? 'null' }}</td>
                </tr>
            </table>
        </div>
    @endforeach
</body>

</html>
