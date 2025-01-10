<!DOCTYPE html>
<html>
<head>
    <title>PIPING</title>
    <style>
        @page { margin: 5px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 3px;
            font-family: 'Open Sans', sans-serif;
        }

        * {
            font-size: 11px;
        }

        img {
            width: 80px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: start;
            padding-top: 5px 3px 5px 3px;
            width: auto;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="border: 1px solid black; vertical-align: middle;" rowspan="9">
                <center>
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($pipingProcess->kode_piping)) !!}">
                </center>
            </td>
            <td style="border: 1px solid black;" colspan="6">
                <b>Stocker Piping</b>
            </td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">ID Piping </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->kode_piping }}</td>

            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Date </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ /*localDateFormat($pipingProcess->tanggal)*/ $pipingProcess->tanggal }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Buyer </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->masterPiping->buyer }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Worksheet </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->masterPiping->act_costing_ws }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Color </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->color }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Panjang Roll </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->panjang_roll_piping." ".$pipingProcess->panjang_roll_piping_unit }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">Lebar Roll </td>
            <td style="border-none; border-top: 1px solid black; border-bottom:1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom:1px solid black;">{{ $pipingProcess->lebar_roll_piping." ".$pipingProcess->lebar_roll_piping_unit }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">Qty Roll Awal </td>
            <td style="border-none; border-top: 1px solid black; border-bottom: 1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">{{ $pipingProcess->output_total_roll." ".$pipingProcess->output_total_roll_unit }}</td>
        </tr>
        <tr>
            <td style="border-none; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">Qty PCS </td>
            <td style="border-none; border-top: 1px solid black; border-bottom: 1px solid black;"> : </td>
            <td colspan="4" style="border-none; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">{{ $pipingProcess->estimasi_output_total." ".$pipingProcess->estimasi_output_total_unit }}</td>
        </tr>
    </table>
</body>
</html>
