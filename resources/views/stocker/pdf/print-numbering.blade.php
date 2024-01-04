<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 0.5px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 0.5px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 3.3px;
            white-space: nowrap;
            overflow: hidden;
        }

        img {
            width: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: top;
            padding: 0.3px;
            width: 100%;
        }
    </style>
</head>
<body>
    @foreach ($dataNumbering as $numbering)
        <table style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
            <tr>
                <td>{{ $numbering['kode'] }}</td>
                <td rowspan="6" style="vertical-align: middle; text-align: center;">
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate("WIP-".$numbering['kode']."-".$numbering['no_cut_size']."-".$numbering['so_det_id'])) !!}">
                </td>
            </tr>
            <tr>
                <td>{{ $numbering['no_cut_size'] }}</td>
            </tr>
            <tr>
                <td>{{ $ws }}</td>
            </tr>
            <tr>
                <td>{{ $color }}</td>
            </tr>
            <tr>
                <td>{{ $numbering['size'] }}</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
