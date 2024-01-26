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
            margin: 0.3px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 4.5px;
            line-height: 4.5px;
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
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate("WIP-".$numbering['kode']."-".$numbering['no_cut_size']."-".$numbering['so_det_id']."-".$numbering['size'])) !!}">
                </td>
            </tr>
            <tr>
                <td>{{ $numbering['no_cut_size'] }}</td>
            </tr>
            <tr>
                <td>{{ $ws }}</td>
            </tr>
            <tr>
                <td>{{ strtoupper(substr($color, 0, 10)).(strlen($color) > 10 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td>{{ $numbering['size'] }}</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
