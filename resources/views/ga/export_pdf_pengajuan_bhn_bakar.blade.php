<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>

    <style>
        @page {
            /*margin: 2cm 2cm 1.5cm 1.5cm; You can specify the margin of the page*/
            /*size: 21cm 29.7cm; You can specify the print size as well*/
            margin: 0;
        }


        body {
            margin: 1px;
        }

        * {
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            border-collapse: collapse;
        }

        table tr th,
        table tr td {
            border: 1px solid;
        }

        table tr th.borderless,
        table tr td.borderless {
            border: none;
        }

        .border-left {
            border-top: none;
            border-left: 1px solid;
            border-bottom: none;
            border-right: none;
        }

        .border-right {
            border-top: none;
            border-right: 1px solid;
            border-bottom: none;
            border-left: none;
        }

        .border-between {
            border-top: none;
            border-right: 1px solid;
            border-bottom: none;
            border-left: 1px solid;
        }

        .border-right-0 {
            border-top: 1px solid;
            border-right: none;
            border-bottom: 1px solid;
            border-left: 1px solid;
        }

        .border-left-0 {
            border-top: 1px solid;
            border-right: 1px solid;
            border-bottom: 1px solid;
            border-left: none;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <table style="margin-left:1px;margin-right:0px;width: 100%;">
        <tr>
            <td style="vertical-align: middle; text-align: center; width: 100%;" colspan="2">
                <img height="50" src="{{ public_path('/assets/dist/img/nag-logo.png') }}" alt="">
            </td>
            <td style="vertical-align: middle; font-size: 15px; text-align: center; font-weight: 800;" colspan="4">
                FORMULIR PERMINTAAN BAHAN BAKAR KENDARAAN</td>
        </tr>
        <tr>
            <td style="font-size: 10px;" class="border-right-0" colspan="5">Mohon untuk dapat diberikan bahan bakar
                untuk kendaraan berikut
                :</td>
            <td colspan="1" class="border-left-0">&nbsp;</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">No. Form</td>
            <td style="width:1%">:</td>
            <td colspan="3" style="font-size: 14px;">{{ $no_trans }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Tanggal
                Pengisian</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ date('d-M-Y', strtotime($tgl_trans)) }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Nama</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $nm }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">NIP</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $nip }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Nomor
                Kendaraan</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $plat_no }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Jenis
                Kendaraan</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $jns_kendaraan }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Oddometer</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $oddometer }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Jenis Bahan
                Bakar</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $nm_bhn_bakar }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Jumlah Bahan
                Bakar (Liter)</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $jml }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Total
                Pembayaran</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">{{ $tot_biaya_fix }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;width:29%;" class="border-right-0" colspan="2">Realisasi
                Jumlah Bahan Bakar
                Jika jumlah bahan bakar kurang dari permintaan (Diisi Petugas Pom Bensin)</td>
            <td style="width:1%">:</td>
            <td colspan="3"style="font-size: 14px;">Rp. </td>
        </tr>
        <tr>
            <td colspan="6" class="borderless">&nbsp;</td>
        </tr>
    </table>
    <table style="margin-left:0px;margin-right:auto;width: 100%;">
        <tr>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Dibuat</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Diketahui</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Disetujui</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Disetujui</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Diterima</td>
        </tr>
        <tr>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
        </tr>
        <tr>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
        </tr>
        <tr>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
            <td style="width: 20%;" class="border-between"> &nbsp; </td>
        </tr>
        <tr>
            <td style="width: 20%;vertical-align: middle; text-align: center;"> {{ ucfirst($user) }}</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Driver</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Ka. Bagian</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Manager</td>
            <td style="width: 20%;vertical-align: middle; text-align: center;">Petugas SPBU</td>
        </tr>
    </table>

</body>

</html>
