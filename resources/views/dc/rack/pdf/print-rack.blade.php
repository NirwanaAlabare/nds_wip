<!DOCTYPE html>
<html>
<head>
    <title>Rack</title>
    <style>
        @page { margin: 15px; }

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
            font-size: 50px;
        }

        img {
            width: 450px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: auto;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 15px 30px;
            border: 1px solid;
            width: auto;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @foreach ($dataRack->rackDetails as $rackDetail)
        <table class="{{ $loop->index != 0 ? 'page-break' : '' }}">
            <thead>
                <tr>
                    <th style="text-align: center;">{{ $rackDetail->nama_detail_rak }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">
                        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($rackDetail->kode)) !!}">
                    </td>
                </tr>
                {{-- <tr>
                    <td style="text-align: center; font-size: 40px;">
                        {{ $rackDetail->kode }}
                    </td>
                </tr> --}}
            </tbody>
        </table>
    @endforeach
</body>
</html>
