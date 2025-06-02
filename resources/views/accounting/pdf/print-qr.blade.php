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
    @foreach ($data as $d)
        @if ($d)
            <div style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($d['so_det_id']."_".$d['number'])) !!}">
                <h5 style="float: right">{{ $d['so_det_id']."_".$d['number'] }}</h5>
            </div>
        @endif
    @endforeach
</body>
</html>
