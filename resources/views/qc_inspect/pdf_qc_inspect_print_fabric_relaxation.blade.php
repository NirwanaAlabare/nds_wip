<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 40mm 20mm;
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
            text-align: center;
            /* Horizontal center */
            vertical-align: middle;
        }

        .value {
            font-weight: bold;
            width: 25%;
            height: 2mm;
            font-size: 7px;
            text-align: center;
        }
    </style>

</head>

<body>
    @foreach ($data_header as $index => $dh)
        <div style="@if ($index !== count($data_header) - 1) page-break-after: always; @endif">
            <table>
                <tr>
                    <td class="value" colspan="4">{{ $dh->barcode ?? 'null' }}</td>
                </tr>
                <tr>
                    <td class="label" colspan="4">Relaxation</td>
                </tr>
                <tr>
                    <td class="value" colspan="2">Start</td>
                    <td class="value" colspan="2">Finish</td>
                </tr>
                <tr>
                    <td class="value">Date</td>
                    <td class="value">Time</td>
                    <td class="value">Date</td>
                    <td class="value">Time</td>
                </tr>
                <tr>
                    <td class="value">{{ $dh->finish_date ?? 'null' }}</td>
                    <td class="value">{{ $dh->finish_time ?? 'null' }}</td>
                    <td class="value">{{ $dh->finish_relax_date ?? 'null' }}</td>
                    <td class="value">{{ $dh->finish_relax_time ?? 'null' }}</td>
                </tr>
            </table>
        </div>
    @endforeach
</body>


</html>
