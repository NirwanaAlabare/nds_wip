<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: top;
            padding: 10px;
            border: 1px solid;
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
                    <img src="data:image/png;base64, {!! $qrCode[$loop->index] !!}">
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
                <td>{{ $numbering['id_stocker'] }}</td>
            </tr>
            <tr>
                <td>{{ $numbering['size'] }}</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
