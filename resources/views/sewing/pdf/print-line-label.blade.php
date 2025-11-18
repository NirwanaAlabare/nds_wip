<!DOCTYPE html>
<html>
<head>
    <title>Print Line Label</title>
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
            font-size: 13px;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < count($data); $i++)
        <div style="{{ $i == count($data)-1 ? '' : 'page-break-after: always;' }} position: relative; width: 35.35px;height: 110.90px;">
            <h3 style="
                display: inline-block;
                position: absolute;
                top: 123px;
                left: 13px;
                -webkit-transform: rotate(-90deg);
                transform: rotate(-90deg);
                -webkit-transform-origin: 0 0;
                transform-origin: 0 0;
                white-space: nowrap;
                font-size: {{ strlen($data[$i]) > 10 ? '16px' : '18px' }};
            ">
                {{ $data[$i] }}
            </h3>
        </div>
    @endfor
</body>
</html>
