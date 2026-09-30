{{-- ===========================================================================
     CADANGAN tampilan LAMA dari local/pdf-ringkas.blade.php (sebelum gaya CARING).
     Disimpan sebagai pembanding: buka PDF-nya dengan tambahan &gaya=lama di
     alamatnya. Setelah gaya baru disetujui, berkas ini boleh dihapus.
     =========================================================================== --}}
{{-- ===========================================================================
     PDF Invoice Local EXIM - bentuk RINGKAS.

     Bedanya dengan local/pdf.blade.php (bentuk rinci yang disalin dari
     reportinvoice3.php di AR): di sini barisnya dikelompokkan per produk,
     tidak dipecah per style/warna/size, potongan yang nol tidak ikut dicetak,
     dan yang tampil cuma Total - PPN - TOTAL. Susunannya mengikuti berkas
     contoh yang dipakai NAK.

     Kop suratnya sengaja disalin apa adanya dari local/pdf.blade.php supaya
     kedua cetakan memakai kop yang sama persis - termasuk margin negatif dan
     line-height-nya, yang memang perilaku mPDF.
     =========================================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $data_invoice['no_invoice'] }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 2px 3px;
            vertical-align: top;
        }

        /* ---------- Kop surat: sama dengan PDF rinci ---------- */
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

        /* ---------- Judul ---------- */
        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding-top: 6px;
        }

        /* ---------- Blok keterangan ---------- */
        .ket .label {
            width: 62px;
        }

        .ket .titik {
            width: 12px;
        }

        /* ---------- Tabel isi ---------- */
        .rinci th {
            font-weight: bold;
            text-align: center;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 4px 3px;
        }

        .rinci td {
            padding: 2px 3px;
        }

        .kanan {
            text-align: right;
        }

        .tengah {
            text-align: center;
        }

        /* Blok rekap: selebar garis tanda tangan, menempel ke tepi kanan. */
        .rekap td {
            padding: 2px 3px;
        }

        /* Label rekap duduk rapat di kiri angkanya. */
        .rekap-label {
            width: 17%;
            text-align: right;
            padding-right: 10px;
        }

        .garis-atas {
            border-top: 1px solid #000;
        }

        .tebal {
            font-weight: bold;
        }

        .mata-uang {
            width: 22px;
            padding-left: 10px;
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

    {{-- ---------------- Judul ---------------- --}}
    <div class="judul">
        {{ trim($data_invoice['type'] . ' INVOICE') }}<br>
        {{ $data_invoice['no_invoice'] }}
    </div>

    {{-- ---------------- Tanggal & tujuan ---------------- --}}
    <table class="ket" style="margin-top:10px">
        <tr>
            <td></td>
            <td class="kanan tebal" style="width:18%">Date</td>
            <td style="width:3%">:</td>
            <td class="kanan" style="width:24%">{{ $tgl_cetak }}</td>
        </tr>
    </table>

    <table class="ket" style="margin-top:6px">
        <tr>
            <td class="label">To</td>
            <td class="titik">:</td>
            <td>{{ $tujuan_npwp }}</td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="titik">:</td>
            <td>
                {{ $data_invoice['customer'] }}
                @foreach ($alamat_baris as $baris)
                    <br>{{ $baris }}
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ---------------- Rincian produk ---------------- --}}
    <table class="rinci" style="margin-top:14px">
        <tr>
            <th style="width:38%">PRODUCT</th>
            <th style="width:13%">QUANTITY</th>
            {{-- Rp cuma dipasang di kolom TOTAL; harga satuan cukup angkanya. --}}
            <th style="width:22%">UNIT PRICE</th>
            <th colspan="2" style="width:27%">TOTAL</th>
        </tr>

        @foreach ($baris_produk as $b)
            <tr>
                <td>{{ $b['nama'] }}</td>
                <td class="kanan">{{ $b['qty'] }}</td>
                <td class="kanan">{{ $b['unit_price'] }}</td>
                <td class="mata-uang">{{ $simbol }}</td>
                <td class="kanan">{{ $b['total'] }}</td>
            </tr>
        @endforeach

        {{-- Total qty: persis di bawah kolom QUANTITY, bergaris atas seperti
             di berkas contoh. --}}
        <tr>
            <td>&nbsp;</td>
            <td class="kanan garis-atas">{{ $total_qty }}</td>
            <td colspan="3"></td>
        </tr>

    </table>

    {{-- ---------------- Rekap ----------------
         Ditaruh di tabel sendiri, tidak menyambung tabel produk: dengan begitu
         garis pemisahnya sependek garis tanda tangan di bawah - sama-sama 38%
         lebar halaman dan rata kanan - tidak menjulur sampai ke tengah.
         Potongan hanya muncul kalau memang terpakai; invoice NAK biasanya
         langsung Subtotal lalu PPN. --}}
    <table class="rekap">
        @foreach ($baris_rekap as $r)
            <tr>
                <td style="width:62%">&nbsp;</td>
                <td class="rekap-label {{ $r['tebal'] ? 'tebal' : '' }} {{ $r['garis'] ? 'garis-atas' : '' }}">{{ $r['label'] }}</td>
                <td class="kanan {{ $r['garis'] ? 'garis-atas' : '' }}">{{ $r['nilai'] }}</td>
            </tr>
        @endforeach

        @if ($pakai_grand)
            {{-- Baris ini cuma perlu kalau ada PPN atau potongan; kalau tidak,
                 angkanya sama persis dengan Subtotal di atas. --}}
            <tr>
                <td>&nbsp;</td>
                <td class="rekap-label tebal garis-atas">TOTAL</td>
                <td class="kanan tebal garis-atas">{{ $grand_total_teks }}</td>
            </tr>
        @endif
    </table>

    {{-- ---------------- Tanda tangan ---------------- --}}
    <div style="margin-top:40px; margin-bottom:0.5cm; page-break-inside: avoid;">
        <table style="page-break-inside: avoid;" cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td style="width:62%">&nbsp;</td>
                <td class="tengah" style="font-size:11px">Approved By</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td style="height:70px">&nbsp;</td>
            </tr>
            {{-- Penandatangan sama dengan PDF rinci (local/pdf.blade.php). --}}
            <tr>
                <td>&nbsp;</td>
                <td class="tengah" style="font-size:11px;border-bottom:1px solid #000000">Yus Yulius</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td class="tengah" style="font-size:11px">Exim Manager</td>
            </tr>
        </table>
    </div>

</body>

</html>
