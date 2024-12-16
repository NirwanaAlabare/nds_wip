<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 0.0px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 0.0px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 9px;
        }

        img {
            width: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 0.0px;
            width: 100%;
        }
    </style>
</head>
<body>
    @for ($i = 0;$i < count($data); $i++)
        <div style="{{ $i == count($data)-1 ? '' : 'page-break-after: always;' }}">
            <hr style="margin-left: 15px; margin-right: 15px;margin-bottom: 25px;border-collapse: collapse; color: transparent;">
            <hr style="margin-top: 10px;margin-bottom: 30px;border-style: dashed;border-collapse: collapse;">
            <h5 style="text-align: center;margin-top: 0px;margin-bottom: 0px; {{ (strlen($data[$i]['color']) > 12 ? "font-size: 4px" : 'font-size: 6px;') }}">{{ 'S/'.($data[$i]['style'] ? $data[$i]['style'] : '-') }}</h5>
            <h5 style="text-align: center;margin-top: 0px;margin-bottom: 0px; {{ (strlen($data[$i]['color']) > 12 ? "font-size: 4px" : 'font-size: 6px;') }}">{{ substr(($data[$i]['color'] ? $data[$i]['color'] : '-'), 0, 15) }}</h5>
            <h5 style="text-align: center;margin-top: 0px;margin-bottom: 0px; {{ (strlen($data[$i]['color']) > 12 ? "font-size: 4px" : 'font-size: 6px;') }}">{{ 'V/012345' }}</h5>
            <h5 style="text-align: center;margin-top: 0px;margin-bottom: 0px; {{ (strlen($data[$i]['color']) > 12 ? "font-size: 4px" : 'font-size: 6px;') }}">{{ date('Y', strtotime($data[$i]['year']))."_".$data[$i]['year_sequence'] }}</h5>
            <div style="transform: rotate(-90deg);">
                <div style="margin-bottom: 0px;">
                    <center>
                        <img style="margin-bottom: 0px;" src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($data[$i]['id_year_sequence'])) !!}">
                    </center>
                </div>
                <h5 style="font-size: 4px;text-align: center;margin-top: 0.5px;margin-bottom: 0px;">{{ $data[$i]['year_sequence_number']; }}</h5>
            </div>
            <h5 style="font-size: 6px;text-align: center;margin-top: 1px;margin-bottom: 1px;">{{ $data[$i]['size'] ? $data[$i]['size'] : '-' }}</h5>
        </div>
    @endfor
</body>
</html>
