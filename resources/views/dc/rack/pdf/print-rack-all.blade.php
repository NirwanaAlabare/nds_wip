<!DOCTYPE html>
<html>
<head>
    <title>Rack</title>
    <style>
        @page { margin: 15px; size: 500pt 560pt; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 5px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 80px;
        }

        img {
            width: 450px;
            margin: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: auto;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 15px 10px;
            border: 1px solid;
            width: auto;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php $first = true; @endphp
    @foreach ($dataRacks as $dataRack)
        @foreach ($dataRack->rackDetails as $rackDetail)
            <table class="{{ $first ? '' : 'page-break' }}">
                @php $first = false; @endphp
                <tr>
                    <th style="text-align: center;">{{ $rackDetail->nama_detail_rak }}</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($rackDetail->nama_detail_rak)) !!}">
                    </td>
                </tr>
            </table>
        @endforeach
    @endforeach
</body>
</html>
