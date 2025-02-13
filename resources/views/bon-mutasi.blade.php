<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 15px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 25px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 15px;
        }

        img {
            width: 95px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1.5px 3px;
            border: 1px solid;
            width: auto;
        }

        table.border-none td, table.border-none th{
            text-align: left;
            vertical-align: middle;
            padding: 1.5px 3px;
            border: none;
            width: auto;
        }
    </style>
</head>
<body>
    <img src="" alt="">
    <h3 style="text-align: center; font-weight: 800; font-size: 18px; margin:0px;">BON MUTASI</h3>
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <table class="border-none">
                <tr>
                    <td>
                        LINE :
                    </td>
                    <td style="width: 250px">

                    </td>
                </tr>
            </table>
        </div>
        <div>
            <table class="border-none">
                <tr>
                    <td>
                        Departemen :
                    </td>
                    <td style="width: 250px">

                    </td>
                </tr>
                <tr>
                    <td>
                        No. Bon Mutasi :
                    </td>
                    <td style="width: 250px">

                    </td>
                </tr>
            </table>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">TGL</th>
                <th style="text-align: center;">STYLE</th>
                <th style="text-align: center;">COLOUR</th>
                <th style="text-align: center;">SIZE</th>
                <th style="text-align: center;">QTY</th>
                <th style="text-align: center;">BUNDLE</th>
                <th><span style="visibility: hidden;">BLANK</span></th>
                <th><span style="visibility: hidden;">BLANK</span></th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < 19; $i++)
                <tr>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                    <td><span style="visibility: hidden;">BLANK</span></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <div style="width: 100%; display:flex; margin-top: 10px;">
        <div style="width: 25%;">
            <p style="text-align: center; font-size: 18px; margin-bottom: 100px;">BON MUTASI</p>
        </div>
        <div style="width: 25%;">
            <p style="text-align: center; font-size: 18px; margin-bottom: 100px;">PENERIMA</p>
        </div>
        <div style="width: 25%;">
            <p style="text-align: center; font-size: 18px; margin-bottom: 100px;">PENGEMBALIAN</p>
        </div>
        <div style="width: 25%;">
            <p style="text-align: center; font-size: 18px; margin-bottom: 100px;">TERIMA KEMBALI</p>
        </div>
    </div>
</body>
</html>
