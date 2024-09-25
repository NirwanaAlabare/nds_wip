<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 0.3px; }

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
            font-size: 5px;
            line-height: 5px;
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
            vertical-align: middle;
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
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($numbering['kode'])) !!}">
                </td>
            </tr>
            <tr>
                <td>{{ strtoupper(substr($numbering['no_cut_size'], 0, 8)).(strlen($numbering['no_cut_size']) > 8 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td>{{ $ws }}</td>
            </tr>
            <tr>
                @php
                    if (strlen($color) > 16) {
                        $colorArray = explode(" ", $color);
                        $newColorArray = [];

                        for ($i = (count($colorArray)-1); $i > 0; $i--) {
                            array_push($newColorArray, $colorArray[$i]);
                        }

                        $color = implode(" ", $newColorArray);
                    }
                @endphp
                <td>{{ strtoupper(substr($color, 0, 8)).(strlen($color) > 8 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="line-height: 6px; padding-top: 6px; {{ (strlen(str_replace(" ", "", $numbering['size'])) > 6 ? 'font-size: 7px;' : 'font-size: 11px;') }}">{{ strtoupper(substr(str_replace(" ", "", $numbering['size']), 0, 6)).(strlen(str_replace(" ", "", $numbering['size'])) > 6 ? '...' : '') }}</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
