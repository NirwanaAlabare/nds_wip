{{-- ===========================================================================
     PDF Invoice Local EXIM - bentuk KNITTING, gaya CLASSIC (tanpa CARING).

     Dibuka lewat menu "PDF Classic" (?gaya=lama). Isinya sama dengan PDF
     Knitting bergaya CARING (pdf-knitting.blade.php); bedanya cuma tampilan.
     Kop suratnya sama persis dengan PDF Local classic (pdf-lama.blade.php),
     termasuk margin negatif & line-height-nya yang memang perilaku mPDF.


     Khusus invoice knitting (NAK). Bedanya dengan dua bentuk ringkas yang lain:
     tidak memakai kop surat, dibuka blok CONSIGNOR / CONSIGNEE / BILL TO, lalu
     satu tabel berbingkai dan keterangan pengapalan & bank di bawahnya.
     Susunannya mengikuti berkas contoh yang dipakai tim knitting.

     Alamat CONSIGNOR sengaja ditulis tetap di sini - itu alamat pabrik yang
     dipakai di dokumen ekspor, bukan data yang berubah per invoice.
     =========================================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $data_invoice['no_invoice'] }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            vertical-align: top;
            padding: 1px 2px;
        }

        .pihak td {
            line-height: 1.45;
        }

        .judul-pihak {
            font-weight: bold;
        }

        .no-invoice {
            text-align: center;
            font-weight: bold;
            padding: 16px 0 4px;
        }

        /* Tabel isi: satu-satunya bagian yang berbingkai penuh. */
        .rinci th,
        .rinci td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .rinci th {
            text-align: center;
            font-weight: bold;
        }

        .kanan {
            text-align: right;
        }

        .bawah td {
            line-height: 1.55;
        }

        .tebal {
            font-weight: bold;
        }

        /* ---------- Kop surat: sama dengan PDF Local classic ---------- */
        .header {
            width: 100%;
            height: 20px;
            padding-top: 0;
            margin-bottom: 10px;
        }

        .title {
            font-size: 30px;
            font-weight: bold;
            text-align: center;
            margin-top: -90px;
        }

        .horizontal {
            height: 0;
            width: 100%;
            border: 3px solid #000000;
        }
    </style>
</head>

<body>

    {{-- ---------------- Kop surat ---------------- --}}
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <img src="{{ $logo }}" width="15%">
                </td>
                <td class="title">
                    PT.NIRWANA ALABARE GARMENT
                    <div style="font-size:12px;line-height:9">
                        Jl. Raya Rancaekek – Majalaya No. 289 Desa Solokan Jeruk Kecamatan Solokan Jeruk,
                        <br />Kabupaten Bandung 40382 <br />Telp. 022-85962081
                    </div>
                </td>
            </tr>
        </table>
        &nbsp;
        <div class="horizontal"></div>
    </div>

    {{-- ---------------- Pengirim & penerima ---------------- --}}
    <table class="pihak">
        <tr>
            <td style="width:52%">
                <span class="judul-pihak">CONSIGNOR</span><br>
                PT Nirwana Alabare Garment<br>
                Jl. Raya Rancaekek - Majalaya No. 289<br>
                Solokan Jeruk, Majalaya<br>
                Kab. Bandung, West Java, Indonesia<br>
                Zip code : 40376<br>
                Phone No. : +62 22 85962076 / +62 22 85962081
            </td>
            <td>
                <span class="judul-pihak">CONSIGNEE</span><br>
                {{ $consignee['nama'] }}
                @foreach ($consignee['baris'] as $baris)
                    <br>{{ $baris }}
                @endforeach

                @if ($bill_to['nama'] !== '')
                    <br><br>
                    <span class="judul-pihak">BILL TO:</span><br>
                    {{ $bill_to['nama'] }}
                    @foreach ($bill_to['baris'] as $baris)
                        <br>{{ $baris }}
                    @endforeach
                @endif
            </td>
        </tr>
    </table>

    {{-- ---------------- Nomor invoice ---------------- --}}
    <div class="no-invoice">Invoice {{ $data_invoice['no_invoice'] }}</div>
    {{-- Penanda versi - lihat pdf-knitting.blade.php. --}}
    <div class="versi-knit" style="text-align:center;font-size:11px;font-weight:bold;letter-spacing:1px;color:#555;margin-top:3px">{{ $versi_knit ?? 'SHIPMENT' }}</div>

    {{-- ---------------- Rincian ---------------- --}}
    <table class="rinci">
        <tr>
            <th style="width:12%">Invoice Date</th>
            <th style="width:25%">Product Item</th>
            <th style="width:16%">Style / Color</th>
            <th style="width:13%">PO</th>
            <th style="width:11%">Qty ({{ $kol_uom }})</th>
            <th style="width:11%">Price ({{ $kol_curr }})</th>
            <th style="width:12%">Total ({{ $kol_curr }})</th>
        </tr>
        @foreach ($baris_knit as $b)
            <tr>
                <td>{{ $tgl_knit }}</td>
                <td>{{ $b['produk'] }}</td>
                <td>{{ $b['warna'] }}</td>
                <td>{{ $b['po'] }}</td>
                <td class="kanan">{{ $b['qty'] }}</td>
                <td class="kanan">{{ $b['unit_price'] }}</td>
                <td class="kanan">{{ $b['total'] }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ---------------- Keterangan pengapalan & bank ---------------- --}}
    <table class="bawah" style="margin-top:14px">
        <tr>
            <td>Shipping on behalf of</td>
        </tr>
        <tr>
            <td class="tebal">{{ $kirim_atas }}</td>
        </tr>
        @if ($bayar['top'] !== '')
            <tr>
                <td>Payment Terms :</td>
            </tr>
            <tr>
                <td>{{ $bayar['top'] }}</td>
            </tr>
        @endif
        @if ($bayar['bank'] !== '')
            <tr>
                <td>Name of the bank : {{ $bayar['bank'] }}</td>
            </tr>
        @endif
        @if ($bayar['no_rek'] !== '')
            <tr>
                <td>Bank Account Number : {{ $bayar['no_rek'] }}</td>
            </tr>
        @endif
        @if ($bayar['swift'] !== '')
            <tr>
                <td>SWIFT Code : {{ $bayar['swift'] }}</td>
            </tr>
        @endif
        @if ($bayar['curr'] !== '')
            <tr>
                <td>Bank account currency. : {{ $bayar['curr'] }}</td>
            </tr>
        @endif
    </table>

</body>

</html>
